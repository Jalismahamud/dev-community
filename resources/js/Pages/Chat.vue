<script setup>
import { nextTick, onMounted, onUnmounted, ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    conversations: { type: Array, default: () => [] },
    activeConversationId: { type: [Number, String], default: null },
    initialMessages: { type: Array, default: () => [] },
    auth: Object,
});

const conversations = ref(props.conversations ?? []);
const activeId = ref(props.activeConversationId ?? conversations.value[0]?.id ?? null);
const messages = ref(props.initialMessages?.data ?? props.initialMessages ?? []);
const body = ref('');
const loadingMessages = ref(false);
const sending = ref(false);
const searchQuery = ref('');
const messagesContainer = ref(null);
const selectedFile = ref(null);
const filePreviewUrl = ref(null);
const chatLightbox = ref(null);
const fileInputRef = ref(null);
const imageInputRef = ref(null);

function handleFileSelect(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    selectedFile.value = file;
    if (file.type.startsWith('image/')) {
        filePreviewUrl.value = URL.createObjectURL(file);
    } else {
        filePreviewUrl.value = null;
    }
}

function clearSelectedFile() {
    selectedFile.value = null;
    if (filePreviewUrl.value) {
        URL.revokeObjectURL(filePreviewUrl.value);
        filePreviewUrl.value = null;
    }
    if (fileInputRef.value) fileInputRef.value.value = '';
    if (imageInputRef.value) imageInputRef.value.value = '';
}

function formatFileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

const mediaUrl = (path) => (!path ? '' : path.startsWith('http://') || path.startsWith('https://') ? path : `/storage/${path}`);
const userProfileUrl = (u) => (!u ? '#' : `/in/${u.username || u.id}`);

function onAvatarError(e, name = 'User') {
    e.target.onerror = null;
    e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=161B22&color=F5A623&bold=true`;
}

function onImgError(e) {
    e.target.onerror = null;
    e.target.style.display = 'none';
}

const activeConversation = computed(() => {
    return conversations.value.find((c) => c.id === activeId.value) ?? null;
});

const currentPartner = computed(() => {
    if (!activeConversation.value) return null;
    const myId = props.auth?.user?.id;
    return activeConversation.value.participants?.find((p) => p.id !== myId) ?? activeConversation.value.participants?.[0] ?? null;
});

const filteredConversations = computed(() => {
    if (!searchQuery.value.trim()) return conversations.value;
    const query = searchQuery.value.toLowerCase();
    const myId = props.auth?.user?.id;

    return conversations.value.filter((conv) => {
        const partner = conv.participants?.find((p) => p.id !== myId) ?? conv.participants?.[0];
        return (
            partner?.name?.toLowerCase().includes(query) ||
            partner?.current_status?.toLowerCase().includes(query) ||
            conv.messages?.[0]?.body?.toLowerCase().includes(query)
        );
    });
});

function getPartner(conv) {
    const myId = props.auth?.user?.id;
    return conv.participants?.find((p) => p.id !== myId) ?? conv.participants?.[0] ?? null;
}

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
}

async function selectConversation(conv) {
    if (activeId.value === conv.id) return;
    
    // Leave previous Echo channel
    if (activeId.value) {
        window.Echo?.leave(`private-conversation.${activeId.value}`);
    }

    activeId.value = conv.id;
    loadingMessages.value = true;
    clearSelectedFile();

    try {
        const response = await axios.get(`/conversations/${conv.id}/messages`);
        messages.value = response.data.data;
        scrollToBottom();
        listenToActiveChannel();
        // Mark as read
        axios.post(`/conversations/${conv.id}/read`).catch(() => {});
    } catch (e) {
        console.error('Failed to load messages', e);
    } finally {
        loadingMessages.value = false;
    }
}

async function send() {
    if (!activeId.value || (!body.value.trim() && !selectedFile.value) || sending.value) return;

    const text = body.value.trim();
    const file = selectedFile.value;

    body.value = '';
    clearSelectedFile();
    sending.value = true;

    try {
        const formData = new FormData();
        if (text) formData.append('body', text);
        if (file) formData.append('file', file);

        const response = await axios.post(`/conversations/${activeId.value}/messages`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        const newMessage = response.data.data;
        messages.value.push(newMessage);
        
        // Update last message preview in conversations list
        const conv = conversations.value.find((c) => c.id === activeId.value);
        if (conv) {
            conv.messages = [newMessage];
        }

        scrollToBottom();
    } catch (e) {
        console.error('Failed to send message', e);
        body.value = text;
        selectedFile.value = file;
    } finally {
        sending.value = false;
    }
}

function listenToActiveChannel() {
    if (!activeId.value || !window.Echo) return;

    window.Echo.private(`conversation.${activeId.value}`).listen('.MessageSent', (event) => {
        if (event.message?.conversation_id === activeId.value) {
            // Avoid duplicate if sent by current user
            if (!messages.value.some((m) => m.id === event.message.id)) {
                messages.value.push(event.message);
                scrollToBottom();
            }
        }
    });
}

onMounted(() => {
    scrollToBottom();
    listenToActiveChannel();
});

onUnmounted(() => {
    if (activeId.value) {
        window.Echo?.leave(`private-conversation.${activeId.value}`);
    }
});
</script>

<template>
    <div class="min-h-screen bg-[#0D1117] text-[#E6E6E6] flex flex-col">
        <!-- Top Navigation -->
        <header class="border-b border-[#232A34] bg-[#161B22]/95 backdrop-blur sticky top-0 z-40 shrink-0">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2.5">
                <a href="/feed" class="flex items-center gap-2 font-display text-lg font-bold tracking-tight text-white">
                    <i class="fa-solid fa-terminal text-[#F5A623]"></i>
                    <span><span class="text-[#F5A623]">dev</span>/community</span>
                </a>

                <nav class="flex items-center gap-3 text-xs text-[#8B949E]">
                    <a href="/feed" class="flex items-center gap-1.5 hover:text-white transition px-2 py-1">
                        <i class="fa-solid fa-house text-sm"></i>
                        <span class="hidden sm:inline">Feed</span>
                    </a>
                    <a href="/chat" class="flex items-center gap-1.5 text-[#F5A623] font-bold px-2 py-1">
                        <i class="fa-solid fa-comments text-sm"></i>
                        <span class="hidden sm:inline">Messages</span>
                    </a>
                    <a href="/notifications" class="flex items-center gap-1.5 hover:text-white transition px-2 py-1">
                        <i class="fa-solid fa-bell text-sm"></i>
                        <span class="hidden sm:inline">Alerts</span>
                    </a>
                    <a href="/profile" class="flex items-center gap-1.5 hover:text-white transition px-2 py-1">
                        <i class="fa-solid fa-gear text-sm"></i>
                        <span class="hidden sm:inline">Settings</span>
                    </a>
                </nav>
            </div>
        </header>

        <!-- Main Chat App Area -->
        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 flex">
            <div class="w-full grid grid-cols-1 md:grid-cols-[340px_1fr] rounded-xl border border-[#232A34] bg-[#161B22] shadow-2xl overflow-hidden min-h-[75vh]">
                <!-- Left Sidebar: Conversations List -->
                <aside class="border-r border-[#232A34] flex flex-col bg-[#10141A]">
                    <!-- Search Header -->
                    <div class="p-4 border-b border-[#232A34] space-y-3">
                        <div class="flex items-center justify-between">
                            <h1 class="font-display text-base font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-message text-[#F5A623]"></i>
                                <span>Direct Channels</span>
                            </h1>
                            <span class="rounded-full bg-[#0FA3B120] text-[#53D2DE] font-mono text-[11px] px-2 py-0.5 border border-[#0FA3B130]">
                                {{ conversations.length }} active
                            </span>
                        </div>

                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-[11px] text-[#8B949E]"></i>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Filter conversations..."
                                class="w-full rounded-md border border-[#232A34] bg-[#161B22] py-2 pl-8 pr-3 text-xs text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623] transition"
                            />
                        </div>
                    </div>

                    <!-- Conversations Scroll Area -->
                    <div class="flex-1 overflow-y-auto divide-y divide-[#232A34]/50">
                        <template v-if="filteredConversations.length">
                            <button
                                v-for="conv in filteredConversations"
                                :key="conv.id"
                                type="button"
                                class="w-full p-3.5 text-left transition flex items-start gap-3 hover:bg-[#161B22]"
                                :class="activeId === conv.id ? 'bg-[#161B22] border-l-4 border-l-[#F5A623]' : ''"
                                @click="selectConversation(conv)"
                            >
                                <!-- Avatar with Status -->
                                <div class="relative shrink-0">
                                    <div class="h-10 w-10 rounded-full border border-[#232A34] bg-[#0D1117] flex items-center justify-center font-bold text-xs text-[#F5A623] overflow-hidden">
                                        <img
                                            v-if="getPartner(conv)?.avatar_path"
                                            :src="mediaUrl(getPartner(conv)?.avatar_path)"
                                            :alt="getPartner(conv)?.name"
                                            class="h-full w-full object-cover"
                                            @error="(e) => onAvatarError(e, getPartner(conv)?.name)"
                                        />
                                        <span v-else>{{ getPartner(conv)?.name?.charAt(0) ?? 'U' }}</span>
                                    </div>
                                    <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-[#10B981] ring-2 ring-[#10141A]"></span>
                                </div>

                                <!-- Meta Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <h2 class="text-xs font-bold text-white truncate">{{ getPartner(conv)?.name ?? 'Developer' }}</h2>
                                        <span class="text-[10px] text-[#8B949E] font-mono shrink-0">
                                            {{ conv.messages?.[0]?.created_at ?? '' }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-[#0FA3B1] truncate font-mono">
                                        {{ getPartner(conv)?.current_status ?? 'Software Engineer' }}
                                    </p>
                                    <p class="text-xs text-[#8B949E] truncate mt-1">
                                        {{ conv.messages?.[0]?.body ?? 'No messages yet.' }}
                                    </p>
                                </div>
                            </button>
                        </template>

                        <div v-else class="p-8 text-center text-xs text-[#8B949E]">
                            <i class="fa-solid fa-comments text-2xl text-[#232A34] mb-2 block"></i>
                            No channels match your search.
                        </div>
                    </div>
                </aside>

                <!-- Right Pane: Active Chat Window -->
                <section class="flex flex-col bg-[#161B22] min-h-[500px]">
                    <template v-if="activeConversation">
                        <!-- Chat Channel Header -->
                        <div class="p-4 border-b border-[#232A34] flex items-center justify-between bg-[#161B22]/90 backdrop-blur shrink-0">
                            <div class="flex items-center gap-3">
                                <a
                                    v-if="currentPartner"
                                    :href="userProfileUrl(currentPartner)"
                                    class="h-10 w-10 rounded-full border border-[#F5A623] bg-[#0D1117] flex items-center justify-center font-bold text-xs text-[#F5A623] overflow-hidden shrink-0 hover:opacity-80 transition"
                                >
                                    <img
                                        v-if="currentPartner.avatar_path"
                                        :src="mediaUrl(currentPartner.avatar_path)"
                                        :alt="currentPartner.name"
                                        class="h-full w-full object-cover"
                                        @error="(e) => onAvatarError(e, currentPartner.name)"
                                    />
                                    <span v-else>{{ currentPartner.name?.charAt(0) ?? 'U' }}</span>
                                </a>

                                <div>
                                    <div class="flex items-center gap-2">
                                        <a
                                            v-if="currentPartner"
                                            :href="userProfileUrl(currentPartner)"
                                            class="text-sm font-bold text-white hover:text-[#F5A623] transition flex items-center gap-1.5"
                                        >
                                            <span>{{ currentPartner.name }}</span>
                                            <i class="fa-solid fa-circle-check text-xs text-[#0FA3B1]"></i>
                                        </a>
                                        <span class="rounded bg-[#10B98120] text-[#10B981] text-[10px] px-1.5 py-0.2 font-mono">online</span>
                                    </div>
                                    <p class="text-[11px] text-[#8B949E] font-mono">// {{ currentPartner?.current_status ?? 'Engineering Peer' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-xs text-[#8B949E]">
                                <a
                                    v-if="currentPartner"
                                    :href="userProfileUrl(currentPartner)"
                                    class="rounded-md border border-[#232A34] bg-[#0D1117] px-3 py-1.5 hover:text-white transition flex items-center gap-1.5"
                                >
                                    <i class="fa-solid fa-user text-xs"></i>
                                    <span>Profile</span>
                                </a>
                                <div class="hidden sm:flex items-center gap-1.5 text-[11px] text-[#0FA3B1] font-mono border border-[#0FA3B130] bg-[#0FA3B110] px-2.5 py-1 rounded">
                                    <i class="fa-solid fa-lock text-[10px]"></i>
                                    <span>Encrypted Private Feed</span>
                                </div>
                            </div>
                        </div>

                        <!-- Messages Stream -->
                        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-5 space-y-4">
                            <div v-if="loadingMessages" class="flex items-center justify-center py-10 text-xs text-[#8B949E] gap-2">
                                <i class="fa-solid fa-circle-notch fa-spin text-sm text-[#F5A623]"></i>
                                <span>Decrypting message history...</span>
                            </div>

                            <template v-else-if="messages.length">
                                <div
                                    v-for="msg in messages"
                                    :key="msg.id"
                                    class="flex gap-2.5 max-w-[85%]"
                                    :class="msg.sender_id === props.auth?.user?.id || msg.sender?.id === props.auth?.user?.id ? 'ml-auto flex-row-reverse' : ''"
                                >
                                    <!-- Message Avatar (Only for partner) -->
                                    <div
                                        v-if="msg.sender_id !== props.auth?.user?.id && msg.sender?.id !== props.auth?.user?.id"
                                        class="h-7 w-7 rounded-full bg-[#0D1117] border border-[#232A34] flex items-center justify-center font-bold text-[10px] text-[#F5A623] overflow-hidden shrink-0 mt-1"
                                    >
                                        <img
                                            v-if="msg.sender?.avatar_path || currentPartner?.avatar_path"
                                            :src="mediaUrl(msg.sender?.avatar_path || currentPartner?.avatar_path)"
                                            :alt="msg.sender?.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else>{{ msg.sender?.name?.charAt(0) ?? 'U' }}</span>
                                    </div>

                                    <!-- Message Bubble -->
                                    <div>
                                        <div
                                            class="rounded-xl px-4 py-2.5 text-xs shadow-sm leading-relaxed"
                                            :class="
                                                msg.sender_id === props.auth?.user?.id || msg.sender?.id === props.auth?.user?.id
                                                    ? 'bg-gradient-to-r from-[#0FA3B1] to-[#0A818C] text-white rounded-tr-none'
                                                    : 'bg-[#10141A] border border-[#232A34] text-[#E6E6E6] rounded-tl-none'
                                            "
                                        >
                                            <!-- Image Attachment -->
                                            <div v-if="msg.attachment_url && msg.attachment_type === 'image'" class="mb-2">
                                                <button type="button" @click="chatLightbox = msg.attachment_url" class="overflow-hidden rounded-lg border border-black/20 block hover:opacity-90 transition">
                                                    <img :src="msg.attachment_url" :alt="msg.attachment_name ?? 'Attachment'" class="max-h-60 max-w-xs object-cover rounded-lg" @error="onImgError" />
                                                </button>
                                            </div>

                                            <!-- Generic File Attachment -->
                                            <div v-else-if="msg.attachment_url" class="mb-2">
                                                <a
                                                    :href="msg.attachment_url"
                                                    :download="msg.attachment_name ?? 'file'"
                                                    target="_blank"
                                                    class="flex items-center gap-3 p-2.5 rounded-lg bg-black/25 hover:bg-black/40 transition border border-white/10"
                                                >
                                                    <div class="h-9 w-9 rounded bg-[#F5A623]/20 text-[#F5A623] flex items-center justify-center shrink-0">
                                                        <i class="fa-solid fa-file-lines text-base"></i>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="font-bold text-xs truncate">{{ msg.attachment_name ?? 'Download File' }}</p>
                                                        <p class="text-[10px] opacity-75">{{ formatFileSize(msg.attachment_size) }} · Click to download</p>
                                                    </div>
                                                    <i class="fa-solid fa-download text-xs opacity-75"></i>
                                                </a>
                                            </div>

                                            <p v-if="msg.body" class="whitespace-pre-wrap">{{ msg.body }}</p>
                                        </div>

                                        <div
                                            class="mt-1 flex items-center gap-1.5 text-[10px] font-mono text-[#8B949E]"
                                            :class="msg.sender_id === props.auth?.user?.id || msg.sender?.id === props.auth?.user?.id ? 'justify-end' : ''"
                                        >
                                            <span>{{ msg.created_at }}</span>
                                            <i
                                                v-if="msg.sender_id === props.auth?.user?.id || msg.sender?.id === props.auth?.user?.id"
                                                class="fa-solid fa-check text-[9px] text-[#0FA3B1]"
                                            ></i>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div v-else class="flex flex-col items-center justify-center py-16 text-center text-xs text-[#8B949E]">
                                <i class="fa-solid fa-hand-wave text-3xl text-[#F5A623] mb-3"></i>
                                <p class="text-white font-semibold mb-1">Start of conversation</p>
                                <p>Say hello to {{ currentPartner?.name }} to kick off the discussion!</p>
                            </div>
                        </div>

                        <!-- Message Composer Form -->
                        <form @submit.prevent="send" class="p-3 border-t border-[#232A34] bg-[#10141A] shrink-0">
                            <!-- Selected File Preview Pill -->
                            <div v-if="selectedFile" class="mb-2 flex items-center justify-between rounded-lg border border-[#232A34] bg-[#161B22] p-2 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <img v-if="filePreviewUrl" :src="filePreviewUrl" alt="Preview" class="h-10 w-10 rounded object-cover border border-[#232A34] shrink-0" />
                                    <div v-else class="h-10 w-10 rounded bg-[#232A34] flex items-center justify-center text-[#F5A623] shrink-0">
                                        <i class="fa-solid fa-file text-base"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white truncate max-w-xs">{{ selectedFile.name }}</p>
                                        <p class="text-[10px] text-[#8B949E]">{{ formatFileSize(selectedFile.size) }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="clearSelectedFile" class="text-[#8B949E] hover:text-[#FF6B6B] transition px-2 py-1" title="Remove attachment">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <!-- Attachment Buttons -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <label class="h-9 w-9 rounded-lg border border-[#232A34] bg-[#161B22] hover:bg-[#232A34] text-[#8B949E] hover:text-[#53D2DE] transition flex items-center justify-center cursor-pointer" title="Send Image">
                                        <i class="fa-solid fa-image text-xs"></i>
                                        <input ref="imageInputRef" type="file" accept="image/*" class="hidden" @change="handleFileSelect" />
                                    </label>
                                    <label class="h-9 w-9 rounded-lg border border-[#232A34] bg-[#161B22] hover:bg-[#232A34] text-[#8B949E] hover:text-[#F5A623] transition flex items-center justify-center cursor-pointer" title="Attach Document or File">
                                        <i class="fa-solid fa-paperclip text-xs"></i>
                                        <input ref="fileInputRef" type="file" class="hidden" @change="handleFileSelect" />
                                    </label>
                                </div>

                                <input
                                    v-model="body"
                                    type="text"
                                    :placeholder="selectedFile ? 'Add an optional message...' : (currentPartner ? `Type message to @${currentPartner.name}...` : 'Type a message...')"
                                    class="flex-1 rounded-lg border border-[#232A34] bg-[#161B22] px-4 py-2.5 text-xs text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623] transition"
                                    @keydown.enter.prevent="send"
                                />
                                <button
                                    type="submit"
                                    :disabled="(!body.trim() && !selectedFile) || sending"
                                    class="rounded-lg bg-[#F5A623] px-5 py-2.5 text-xs font-bold text-[#10141A] hover:shadow-[0_0_15px_#F5A62355] transition flex items-center gap-1.5 disabled:opacity-40"
                                >
                                    <i v-if="sending" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                    <i v-else class="fa-solid fa-paper-plane text-xs"></i>
                                    <span class="hidden sm:inline">Send</span>
                                </button>
                            </div>
                        </form>
                    </template>

                    <!-- Empty State (No Active Conversation) -->
                    <div v-else class="flex-1 flex flex-col items-center justify-center p-8 text-center text-[#8B949E]">
                        <div class="h-16 w-16 rounded-full bg-[#10141A] border border-[#232A34] flex items-center justify-center mb-4">
                            <i class="fa-solid fa-comments text-2xl text-[#F5A623]"></i>
                        </div>
                        <h2 class="text-base font-bold text-white mb-1">No conversation selected</h2>
                        <p class="text-xs max-w-sm mb-6">Select an existing direct message from the sidebar or click "Message" on any developer's public profile.</p>
                        <a
                            href="/feed"
                            class="rounded-md bg-[#0FA3B1] px-5 py-2 text-xs font-bold text-white hover:bg-[#0FA3B1]/90 transition"
                        >
                            Explore Developer Feed
                        </a>
                    </div>
                </section>
            </div>
        </main>

        <!-- Chat Image Lightbox Modal -->
        <div
            v-if="chatLightbox"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#0D1117EE] p-6 backdrop-blur-sm"
            @click.self="chatLightbox = null"
        >
            <button class="absolute right-6 top-5 text-2xl text-[#E6E6E6] hover:text-white transition" @click="chatLightbox = null">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img :src="chatLightbox" alt="Enlarged attachment" class="max-h-[85vh] max-w-full rounded-md object-contain shadow-2xl" />
        </div>
    </div>
</template>
