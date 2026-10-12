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
    <div class="min-h-screen bg-[#090C15] text-[#E2E8F0] flex flex-col">
        <header class="border-b border-[#1E253A] bg-[#0E1220]/90 backdrop-blur-md sticky top-0 z-40">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3">
                <a href="/feed" class="flex items-center gap-2.5 font-display text-lg font-bold tracking-tight text-white">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-tr from-cyan-400 via-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                        <i class="fa-solid fa-wifi text-sm rotate-45"></i>
                    </div>
                    <span class="text-white text-base tracking-wide">Dev<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">Connect</span></span>
                </a>

                <nav class="flex items-center gap-3 text-xs text-[#94A3B8]">
                    <a href="/feed" class="flex items-center gap-1.5 hover:text-white transition px-2.5 py-1">
                        <i class="fa-solid fa-house text-sm"></i>
                        <span class="hidden sm:inline">Feed</span>
                    </a>
                    <a href="/chat" class="flex items-center gap-1.5 text-purple-400 font-bold px-2.5 py-1">
                        <i class="fa-solid fa-comments text-sm"></i>
                        <span class="hidden sm:inline">Messages</span>
                    </a>
                    <a href="/notifications" class="flex items-center gap-1.5 hover:text-white transition px-2.5 py-1">
                        <i class="fa-regular fa-bell text-sm"></i>
                        <span class="hidden sm:inline">Alerts</span>
                    </a>
                    <a href="/profile" class="flex items-center gap-1.5 hover:text-white transition px-2.5 py-1">
                        <i class="fa-solid fa-gear text-sm"></i>
                        <span class="hidden sm:inline">Settings</span>
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 flex">
            <div class="w-full grid grid-cols-1 md:grid-cols-[360px_1fr] rounded-3xl border border-[#1E253A] bg-[#0E1220] shadow-2xl overflow-hidden min-h-[78vh]">
                <aside class="border-r border-[#1E253A] flex flex-col bg-[#0A0D17]">
                    <div class="p-5 border-b border-[#1E253A] space-y-3.5">
                        <div class="flex items-center justify-between">
                            <h1 class="font-display text-lg font-extrabold text-white tracking-wide">
                                Chat Hub
                            </h1>
                            <div class="flex items-center gap-2">
                                <a
                                    href="/feed"
                                    class="rounded-xl border border-white/10 bg-[#141829] px-3 py-1.5 text-xs font-semibold text-slate-200 hover:text-white hover:border-purple-500/50 transition flex items-center gap-1.5"
                                >
                                    <i class="fa-solid fa-plus text-[10px] text-cyan-400"></i>
                                    <span>New Chat</span>
                                </a>
                            </div>
                        </div>

                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-xs text-[#64748B]"></i>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search conversations..."
                                class="w-full rounded-xl border border-[#1E253A] bg-[#121627] py-2 pl-9 pr-3 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 transition"
                            />
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-2 space-y-1">
                        <template v-if="filteredConversations.length">
                            <button
                                v-for="conv in filteredConversations"
                                :key="conv.id"
                                type="button"
                                class="w-full p-3.5 text-left transition rounded-2xl flex items-start gap-3.5"
                                :class="activeId === conv.id ? 'bg-[#1C2038] border border-purple-500/50 shadow-md shadow-purple-500/10' : 'hover:bg-[#121627] text-slate-300 border border-transparent'"
                                @click="selectConversation(conv)"
                            >
                                <div class="relative shrink-0">
                                    <div class="h-11 w-11 rounded-full border border-purple-500/30 bg-[#161B2E] flex items-center justify-center font-bold text-xs text-purple-300 overflow-hidden shadow-sm">
                                        <img
                                            v-if="getPartner(conv)?.avatar_path"
                                            :src="mediaUrl(getPartner(conv)?.avatar_path)"
                                            :alt="getPartner(conv)?.name"
                                            class="h-full w-full object-cover"
                                            @error="(e) => onAvatarError(e, getPartner(conv)?.name)"
                                        />
                                        <span v-else>{{ getPartner(conv)?.name?.charAt(0) ?? 'U' }}</span>
                                    </div>
                                    <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-emerald-400 ring-2 ring-[#0A0D17]"></span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <h2 class="text-xs font-bold text-white truncate">
                                            {{ getPartner(conv)?.name ?? 'Developer' }}
                                            <span class="text-[10px] text-emerald-400 font-normal ml-1">(Available)</span>
                                        </h2>
                                        <span class="text-[10px] text-[#64748B] font-mono shrink-0">
                                            {{ conv.messages?.[0]?.created_at ?? '10:02 AM' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#94A3B8] truncate mt-1">
                                        {{ conv.messages?.[0]?.body || (conv.messages?.[0]?.attachment_name ? 'Attachment: ' + conv.messages[0].attachment_name : 'Click to open conversation') }}
                                    </p>
                                </div>
                            </button>
                        </template>

                        <div v-else class="p-8 text-center text-xs text-[#64748B]">
                            <i class="fa-solid fa-comments text-2xl text-[#1E253A] mb-2 block"></i>
                            No channels match your search.
                        </div>
                    </div>
                </aside>

                <section class="flex flex-col bg-[#0E1220] min-h-[520px]">
                    <template v-if="activeConversation">
                        <div class="px-6 py-4 border-b border-[#1E253A] flex items-center justify-between bg-[#0E1220]/95 backdrop-blur shrink-0">
                            <div class="flex items-center gap-3.5">
                                <a
                                    v-if="currentPartner"
                                    :href="userProfileUrl(currentPartner)"
                                    class="h-10 w-10 rounded-full border border-purple-500/40 bg-[#161B2E] flex items-center justify-center font-bold text-xs text-purple-300 overflow-hidden shrink-0 hover:opacity-90 transition relative"
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
                                            class="text-sm font-bold text-white hover:text-purple-300 transition flex items-center gap-1.5"
                                        >
                                            <span>{{ currentPartner.name }}</span>
                                        </a>
                                        <span class="flex items-center gap-1 text-[11px] text-emerald-400 font-medium">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                            <span>Online</span>
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-[#64748B] font-mono">{{ currentPartner?.current_status ?? 'Senior Software Engineer' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-sm text-[#94A3B8]">
                                <button class="h-9 w-9 rounded-xl border border-[#1E253A] bg-[#141829] hover:text-white hover:border-purple-500/40 transition flex items-center justify-center">
                                    <i class="fa-solid fa-phone text-xs"></i>
                                </button>
                                <button class="h-9 w-9 rounded-xl border border-[#1E253A] bg-[#141829] hover:text-white hover:border-purple-500/40 transition flex items-center justify-center">
                                    <i class="fa-solid fa-video text-xs"></i>
                                </button>
                                <button class="h-9 w-9 rounded-xl border border-[#1E253A] bg-[#141829] hover:text-white hover:border-purple-500/40 transition flex items-center justify-center">
                                    <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-5">
                            <div v-if="loadingMessages" class="flex items-center justify-center py-10 text-xs text-[#94A3B8] gap-2">
                                <i class="fa-solid fa-circle-notch fa-spin text-sm text-purple-400"></i>
                                <span>Loading conversation history...</span>
                            </div>

                            <template v-else-if="messages.length">
                                <div
                                    v-for="msg in messages"
                                    :key="msg.id"
                                    class="flex gap-3 max-w-[80%]"
                                    :class="msg.sender_id === props.auth?.user?.id || msg.sender?.id === props.auth?.user?.id ? 'ml-auto flex-row-reverse' : ''"
                                >
                                    <div
                                        class="h-8 w-8 rounded-full bg-[#161B2E] border border-purple-500/30 flex items-center justify-center font-bold text-[10px] text-purple-300 overflow-hidden shrink-0 mt-1"
                                    >
                                        <img
                                            v-if="msg.sender?.avatar_path || (msg.sender_id === props.auth?.user?.id ? props.auth?.user?.avatar_path : currentPartner?.avatar_path)"
                                            :src="mediaUrl(msg.sender?.avatar_path || (msg.sender_id === props.auth?.user?.id ? props.auth?.user?.avatar_path : currentPartner?.avatar_path))"
                                            :alt="msg.sender?.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else>{{ msg.sender?.name?.charAt(0) ?? 'U' }}</span>
                                    </div>

                                    <div>
                                        <div
                                            class="flex items-center gap-2 mb-1 text-[11px]"
                                            :class="msg.sender_id === props.auth?.user?.id || msg.sender?.id === props.auth?.user?.id ? 'justify-end' : ''"
                                        >
                                            <span class="font-bold text-white text-xs">{{ msg.sender?.name || (msg.sender_id === props.auth?.user?.id ? props.auth?.user?.name : currentPartner?.name) }}</span>
                                            <span class="text-[#64748B] font-mono text-[10px]">{{ msg.created_at || '10:05 AM' }}</span>
                                        </div>

                                        <div
                                            class="rounded-2xl p-4 text-xs shadow-md leading-relaxed"
                                            :class="
                                                msg.sender_id === props.auth?.user?.id || msg.sender?.id === props.auth?.user?.id
                                                    ? 'bg-[#23204E] border border-purple-500/40 text-white rounded-tr-none'
                                                    : 'bg-[#141829] border border-[#1E253A] text-[#CBD5E1] rounded-tl-none'
                                            "
                                        >
                                            <div v-if="msg.attachment_url && msg.attachment_type === 'image'" class="mb-2.5">
                                                <button type="button" @click="chatLightbox = msg.attachment_url" class="overflow-hidden rounded-xl border border-white/10 block hover:opacity-90 transition">
                                                    <img :src="msg.attachment_url" :alt="msg.attachment_name ?? 'Attachment'" class="max-h-60 max-w-sm object-cover rounded-xl" @error="onImgError" />
                                                </button>
                                                <div class="mt-1 text-[11px] text-[#94A3B8] font-mono truncate">
                                                    {{ msg.attachment_name ?? 'design_mockup.png' }}
                                                </div>
                                            </div>

                                            <div v-else-if="msg.attachment_url" class="mb-2.5">
                                                <a
                                                    :href="msg.attachment_url"
                                                    :download="msg.attachment_name ?? 'file'"
                                                    target="_blank"
                                                    class="flex items-center gap-3 p-3 rounded-xl bg-[#090C16] hover:bg-black/60 transition border border-white/10"
                                                >
                                                    <div
                                                        class="h-10 w-10 rounded-lg flex items-center justify-center font-bold text-xs uppercase shrink-0"
                                                        :class="msg.attachment_name?.endsWith('.zip') || msg.attachment_type?.includes('zip') ? 'bg-cyan-500/20 text-cyan-300' : 'bg-rose-500/20 text-rose-300'"
                                                    >
                                                        <span>{{ msg.attachment_name?.split('.').pop() || 'DOC' }}</span>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="font-bold text-xs text-white truncate">{{ msg.attachment_name ?? 'attachment_document' }}</p>
                                                        <p class="text-[10px] text-[#64748B]">{{ formatFileSize(msg.attachment_size) || '2.4 MB' }}</p>
                                                    </div>
                                                    <i class="fa-solid fa-download text-xs text-[#94A3B8]"></i>
                                                </a>
                                            </div>

                                            <p v-if="msg.body" class="whitespace-pre-wrap">{{ msg.body }}</p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div v-else class="flex flex-col items-center justify-center py-20 text-center text-xs text-[#94A3B8]">
                                <i class="fa-solid fa-comments text-3xl text-purple-400 mb-3"></i>
                                <p class="text-white font-semibold text-sm mb-1">Start of conversation</p>
                                <p>Say hello to {{ currentPartner?.name }} to kick off the discussion!</p>
                            </div>
                        </div>

                        <form @submit.prevent="send" class="p-4 border-t border-[#1E253A] bg-[#0A0D17] shrink-0">
                            <div v-if="selectedFile" class="mb-3 flex items-center justify-between rounded-xl border border-purple-500/30 bg-[#141829] p-2.5 text-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img v-if="filePreviewUrl" :src="filePreviewUrl" alt="Preview" class="h-10 w-10 rounded-lg object-cover border border-white/10 shrink-0" />
                                    <div v-else class="h-10 w-10 rounded-lg bg-[#1E253A] flex items-center justify-center text-cyan-400 shrink-0">
                                        <i class="fa-solid fa-file text-base"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-white truncate max-w-xs">{{ selectedFile.name }}</p>
                                        <p class="text-[10px] text-[#64748B]">{{ formatFileSize(selectedFile.size) }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="clearSelectedFile" class="text-[#64748B] hover:text-[#F43F5E] transition px-2 py-1">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="h-10 w-10 rounded-xl border border-[#1E253A] bg-[#141829] hover:bg-[#1C223B] text-[#94A3B8] hover:text-white transition flex items-center justify-center shrink-0"
                                    title="More options"
                                >
                                    <i class="fa-solid fa-plus text-xs"></i>
                                </button>

                                <label
                                    class="h-10 w-10 rounded-xl border border-[#1E253A] bg-[#141829] hover:bg-[#1C223B] text-[#94A3B8] hover:text-cyan-400 transition flex items-center justify-center cursor-pointer shrink-0"
                                    title="Send Image"
                                >
                                    <i class="fa-regular fa-image text-sm"></i>
                                    <input ref="imageInputRef" type="file" accept="image/*" class="hidden" @change="handleFileSelect" />
                                </label>

                                <label
                                    class="h-10 w-10 rounded-xl border border-[#1E253A] bg-[#141829] hover:bg-[#1C223B] text-[#94A3B8] hover:text-purple-400 transition flex items-center justify-center cursor-pointer shrink-0"
                                    title="Attach File"
                                >
                                    <i class="fa-solid fa-paperclip text-sm"></i>
                                    <input ref="fileInputRef" type="file" class="hidden" @change="handleFileSelect" />
                                </label>

                                <input
                                    v-model="body"
                                    type="text"
                                    :placeholder="selectedFile ? 'Add an optional message...' : (currentPartner ? `Type a message... (${currentPartner.name})` : 'Type a message...')"
                                    class="flex-1 rounded-xl border border-[#1E253A] bg-[#121627] px-4 py-2.5 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 transition"
                                    @keydown.enter.prevent="send"
                                />

                                <button
                                    type="button"
                                    class="h-10 w-10 rounded-xl border border-[#1E253A] bg-[#141829] hover:bg-[#1C223B] text-[#94A3B8] hover:text-cyan-400 transition hidden sm:flex items-center justify-center shrink-0 font-mono text-xs"
                                    title="Code format"
                                    @click="body += '\n```\n\n```'"
                                >
                                    &lt;/&gt;
                                </button>

                                <button
                                    type="submit"
                                    :disabled="(!body.trim() && !selectedFile) || sending"
                                    class="rounded-xl bg-gradient-to-r from-cyan-400 via-teal-400 to-emerald-400 px-5 py-2.5 text-xs font-bold text-slate-950 hover:shadow-lg hover:shadow-cyan-500/30 transition flex items-center gap-1.5 disabled:opacity-40 shrink-0"
                                >
                                    <i v-if="sending" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                    <span v-else>Send</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </button>
                            </div>
                        </form>
                    </template>

                    <div v-else class="flex-1 flex flex-col items-center justify-center p-8 text-center text-[#94A3B8]">
                        <div class="h-16 w-16 rounded-2xl bg-[#141829] border border-[#1E253A] flex items-center justify-center mb-4">
                            <i class="fa-solid fa-comments text-2xl text-purple-400"></i>
                        </div>
                        <h2 class="text-base font-bold text-white mb-1">No conversation selected</h2>
                        <p class="text-xs max-w-sm mb-6 text-[#64748B]">Select a conversation from the sidebar to start chatting.</p>
                        <a
                            href="/feed"
                            class="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-5 py-2 text-xs font-semibold text-white transition"
                        >
                            Explore Signals Feed
                        </a>
                    </div>
                </section>
            </div>
        </main>

        <div
            v-if="chatLightbox"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-6 backdrop-blur-md"
            @click.self="chatLightbox = null"
        >
            <button class="absolute right-6 top-5 text-2xl text-slate-300 hover:text-white transition" @click="chatLightbox = null">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img :src="chatLightbox" alt="Enlarged attachment" class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl border border-white/10" />
        </div>
    </div>
</template>
