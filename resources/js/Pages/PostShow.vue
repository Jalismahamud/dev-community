<script setup>
import axios from 'axios';
import { marked } from 'marked';
import { ref } from 'vue';
import CommentThread from '../Components/CommentThread.vue';

const props = defineProps({
    post: Object,
    auth: Object,
});

const post = ref(props.post?.data ?? props.post);
const copied = ref(false);
const lightbox = ref(null);
const newCommentText = ref('');
const submittingComment = ref(false);

const renderedBody = (value) => marked.parse(value ?? '');
const mediaUrl = (path) => (!path ? '' : path.startsWith('http://') || path.startsWith('https://') ? path : `/storage/${path}`);

const userProfileUrl = (user) => (!user ? '#' : `/in/${user.username || user.id}`);
const postUrl = (p) => (!p ? '#' : `/posts/${p.slug || p.code || p.id}`);

function onAvatarError(e, name = 'User') {
    e.target.onerror = null;
    e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=161B22&color=F5A623&bold=true`;
}

function onImgError(e) {
    e.target.onerror = null;
    e.target.style.display = 'none';
}

async function likePost() {
    const response = await axios.post(`/posts/${post.value.id}/like`);
    post.value.likes_count = response.data.data.likes_count;
    post.value.is_liked = response.data.data.liked;
}

async function sharePost() {
    const response = await axios.post(`/posts/${post.value.id}/share`);
    post.value.shares_count = response.data.data.shares_count;
    post.value.is_shared = response.data.data.shared;
}

async function bookmarkPost() {
    const response = await axios.post(`/posts/${post.value.id}/bookmark`);
    post.value.is_bookmarked = response.data.data.bookmarked;
}

function copyPostLink() {
    const canonicalUrl = `${window.location.origin}${postUrl(post.value)}`;
    navigator.clipboard?.writeText(canonicalUrl);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}

async function likeComment(comment) {
    const response = await axios.post(`/comments/${comment.id}/like`);
    comment.likes_count = response.data.data.likes_count;
}

async function submitComment() {
    if (!newCommentText.value.trim() || submittingComment.value) return;
    const text = newCommentText.value.trim();
    newCommentText.value = '';
    submittingComment.value = true;
    try {
        const response = await axios.post(`/posts/${post.value.id}/comments`, { body: text });
        if (!post.value.comments) post.value.comments = [];
        post.value.comments.unshift(response.data.data);
        post.value.comments_count = (post.value.comments_count ?? 0) + 1;
    } catch (e) {
        console.error('Failed to post comment', e);
        newCommentText.value = text;
    } finally {
        submittingComment.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-[#0D1117] text-[#E6E6E6]">
        <header class="border-b border-[#232A34] bg-[#161B22]/90 backdrop-blur sticky top-0 z-40">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-5 py-3">
                <a href="/feed" class="flex items-center gap-2 font-display text-lg font-bold tracking-tight">
                    <i class="fa-solid fa-terminal text-[#F5A623]"></i>
                    <span><span class="text-[#F5A623]">dev</span>/community</span>
                </a>
                <nav class="flex items-center gap-4 text-xs text-[#8B949E]">
                    <a href="/feed" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Feed</span>
                    </a>
                    <a href="/notifications" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fa-solid fa-bell text-xs"></i>
                        <span>Alerts</span>
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-5 py-8 pb-20">
            <article class="surface border border-[#232A34] bg-[#161B22] p-6 sm:p-10 rounded-xl shadow-lg">
                <div class="mb-6 flex items-center justify-between border-b border-[#232A34] pb-4">
                    <div class="flex items-center gap-3">
                        <a :href="userProfileUrl(post.user)" class="h-10 w-10 rounded-full bg-[#0D1117] border border-[#232A34] flex items-center justify-center font-bold text-sm text-[#F5A623] overflow-hidden">
                            <img
                                v-if="post.user?.avatar_path"
                                :src="mediaUrl(post.user?.avatar_path)"
                                :alt="post.user?.name"
                                class="h-full w-full object-cover"
                                @error="(e) => onAvatarError(e, post.user?.name)"
                            />
                            <span v-else>{{ post.user?.name?.charAt(0) }}</span>
                        </a>
                        <div>
                            <a :href="userProfileUrl(post.user)" class="text-sm font-bold text-white hover:text-[#F5A623] transition flex items-center gap-1">
                                <span>{{ post.user?.name }}</span>
                                <i class="fa-solid fa-circle-check text-xs text-[#0FA3B1]"></i>
                            </a>
                            <div class="flex items-center gap-2 text-xs text-[#8B949E]">
                                <span>{{ post.created_at }}</span>
                                <span>•</span>
                                <span class="flex items-center gap-1 text-[#0FA3B1] font-mono"><i class="fa-regular fa-eye text-[11px]"></i> {{ post.views_count ?? 1 }} views</span>
                            </div>
                        </div>
                    </div>
                    <span class="font-mono text-xs text-[#8B949E]">#{{ post.code || post.id?.toString().padStart(4, '0') }}</span>
                </div>

                <h1 v-if="post.title" class="font-display mb-6 text-3xl font-extrabold text-white sm:text-4xl leading-tight">
                    {{ post.title }}
                </h1>

                <div class="prose-community text-base leading-8 text-[#C7CDD4]" v-html="renderedBody(post.body_markdown)"></div>

                <div v-if="post.tags?.length" class="mt-6 flex flex-wrap gap-2">
                    <span v-for="tag in post.tags" :key="tag.id" class="rounded-full border border-[#0FA3B144] bg-[#0FA3B110] px-3 py-1 font-mono text-xs text-[#53D2DE]">
                        #{{ tag.name }}
                    </span>
                </div>

                <div v-if="post.images?.length" :class="['mt-6 grid gap-3', post.images.length === 1 ? 'grid-cols-1' : 'grid-cols-2']">
                    <button
                        v-for="image in post.images"
                        :key="image.id"
                        class="overflow-hidden rounded-lg border border-[#232A34] bg-[#0D1117] transition hover:opacity-90 w-full"
                        @click="lightbox = image"
                    >
                        <img
                            :src="mediaUrl(image.path_webp)"
                            :alt="image.alt_text ?? 'Post image'"
                            :class="post.images.length === 1 ? 'max-h-[500px] w-full object-cover rounded-lg' : 'aspect-video w-full object-cover transition hover:scale-105'"
                            @error="(e) => onImgError(e)"
                        />
                    </button>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-between border-t border-[#232A34] pt-5 text-sm text-[#8B949E]">
                    <div class="flex items-center gap-6">
                        <button
                            class="flex items-center gap-2 transition"
                            :class="post.is_liked ? 'text-[#FF6B6B] font-bold' : 'hover:text-[#FF6B6B]'"
                            @click="likePost"
                        >
                            <i :class="post.is_liked ? 'fa-solid fa-heart text-[#FF6B6B]' : 'fa-regular fa-heart'"></i>
                            <span>{{ post.likes_count }}</span>
                        </button>

                        <button class="flex items-center gap-2 hover:text-[#F5A623] transition" @click="$refs.commentInputRef?.focus()">
                            <i class="fa-regular fa-comment"></i>
                            <span>{{ post.comments_count }} replies</span>
                        </button>

                        <button
                            class="flex items-center gap-2 transition"
                            :class="post.is_shared ? 'text-[#53D2DE] font-bold' : 'hover:text-[#53D2DE]'"
                            @click="sharePost"
                        >
                            <i class="fa-solid fa-share-nodes"></i>
                            <span>{{ post.shares_count ?? 0 }} shares</span>
                        </button>

                        <button
                            class="flex items-center gap-2 transition"
                            :class="post.is_bookmarked ? 'text-[#F5A623] font-bold' : 'hover:text-[#F5A623]'"
                            @click="bookmarkPost"
                        >
                            <i :class="post.is_bookmarked ? 'fa-solid fa-bookmark text-[#F5A623]' : 'fa-regular fa-bookmark'"></i>
                            <span>{{ post.is_bookmarked ? 'Saved' : 'Save' }}</span>
                        </button>
                    </div>

                    <button class="flex items-center gap-2 text-xs hover:text-white transition" @click="copyPostLink">
                        <i :class="copied ? 'fa-solid fa-check text-[#0FA3B1]' : 'fa-solid fa-link'"></i>
                        <span>{{ copied ? 'Link copied' : 'Copy link' }}</span>
                    </button>
                </div>

                <div class="mt-10 border-t border-[#232A34] pt-8">
                    <h3 class="font-display mb-4 text-xl font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-comments text-[#F5A623] text-base"></i>
                        <span>Discussion & Replies</span>
                    </h3>

                    <!-- Inline Comment Box -->
                    <div class="mb-6 flex items-center gap-2.5">
                        <input
                            ref="commentInputRef"
                            v-model="newCommentText"
                            type="text"
                            placeholder="Add your engineering insight or ask a question... (Enter to reply)"
                            class="flex-1 rounded-md border border-[#232A34] bg-[#0D1117] px-3.5 py-2 text-xs text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623] transition"
                            @keydown.enter.prevent="submitComment"
                        />
                        <button
                            @click="submitComment"
                            :disabled="!newCommentText.trim() || submittingComment"
                            class="rounded-md bg-[#F5A623] px-4 py-2 text-xs font-bold text-[#10141A] disabled:opacity-40 hover:shadow-[0_0_12px_#F5A62355] transition flex items-center gap-1.5 shrink-0"
                        >
                            <i v-if="submittingComment" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <i v-else class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Reply</span>
                        </button>
                    </div>
                    <div v-if="post.comments?.length" class="space-y-4">
                        <CommentThread
                            v-for="comment in post.comments.filter((item) => !item.parent_id)"
                            :key="comment.id"
                            :comment="comment"
                            @like="likeComment"
                        />
                    </div>
                    <div v-else class="surface border border-[#232A34] bg-[#0D1117] p-6 text-center text-xs text-[#8B949E] rounded-lg">
                        No replies yet. Click reply above to join the conversation.
                    </div>
                </div>
            </article>
        </main>

        <div
            v-if="lightbox"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#0D1117EE] p-6 backdrop-blur-sm"
            @click.self="lightbox = null"
        >
            <button class="absolute right-6 top-5 text-2xl text-[#E6E6E6] hover:text-white transition" @click="lightbox = null">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img :src="mediaUrl(lightbox.path_webp)" :alt="lightbox.alt_text ?? ''" class="max-h-[85vh] max-w-full rounded-md object-contain shadow-2xl" />
        </div>
    </div>
</template>
