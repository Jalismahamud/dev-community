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
    <div class="min-h-screen bg-[#090C15] text-[#E2E8F0]">
        <header class="border-b border-[#1E253A] bg-[#0E1220]/90 backdrop-blur-md sticky top-0 z-40">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
                <div class="flex items-center gap-6">
                    <a href="/feed" class="flex items-center gap-2.5 font-display text-lg font-bold tracking-tight text-white">
                        <div class="h-8 w-8 rounded-lg bg-gradient-to-tr from-cyan-400 via-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                            <i class="fa-solid fa-wifi text-sm rotate-45"></i>
                        </div>
                        <span class="text-white text-base tracking-wide">Dev<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">Connect</span></span>
                    </a>

                    <nav class="hidden md:flex items-center gap-6 text-xs font-semibold text-[#94A3B8]">
                        <a href="/feed" class="text-white hover:text-purple-300 transition">Articles</a>
                        <a href="/feed?view=trending" class="hover:text-white transition">Forum</a>
                        <a href="/feed?view=most-viewed" class="hover:text-white transition">Code</a>
                        <a href="/terms" class="hover:text-white transition">Docs</a>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        href="/feed"
                        class="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-purple-500/20 hover:scale-105 transition flex items-center gap-1.5"
                    >
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>New Post</span>
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-10 pb-24">
            <article class="rounded-3xl border border-[#1E253A] bg-[#0E1220] p-6 sm:p-10 shadow-2xl space-y-8">
                <div class="space-y-4">
                    <h1 v-if="post.title" class="font-display text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                        {{ post.title }}
                    </h1>

                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-2">
                        <div class="flex items-center gap-4">
                            <a :href="userProfileUrl(post.user)" class="h-12 w-12 rounded-full bg-[#161B2E] border-2 border-purple-500/40 flex items-center justify-center font-bold text-sm text-purple-300 overflow-hidden shrink-0 shadow-md">
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
                                <div class="flex flex-wrap items-center gap-2">
                                    <a :href="userProfileUrl(post.user)" class="text-sm font-bold text-white hover:text-purple-300 transition">
                                        {{ post.user?.name }}
                                    </a>
                                    <span class="text-xs text-[#64748B]">@{{ post.user?.name?.toLowerCase().replace(/\s+/g, '') }}</span>
                                    <span class="rounded-full bg-purple-500/10 border border-purple-500/30 text-purple-300 text-[10px] font-mono font-semibold px-2 py-0.5">
                                        {{ post.user?.current_status || 'Senior Full Stack Dev' }}
                                    </span>
                                    <span class="rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-[10px] font-mono font-semibold px-2 py-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-award text-[9px]"></i>
                                        <span>Community Expert</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-[#64748B] mt-1 font-mono">
                                    <span>Published: {{ post.created_at }}</span>
                                    <span>·</span>
                                    <span>5 min read</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 rounded-2xl border border-[#1E253A] bg-[#121627] px-4 py-2.5 shrink-0">
                            <div class="text-center px-2">
                                <span class="block text-xs font-bold text-purple-300 font-mono">{{ ((post.views_count ?? 1) * 3) + 120 }}</span>
                                <span class="text-[10px] text-[#64748B] uppercase">Impressions</span>
                            </div>
                            <div class="h-6 w-px bg-[#1E253A]"></div>
                            <div class="text-center px-2">
                                <span class="block text-xs font-bold text-cyan-300 font-mono">{{ post.views_count ?? 1 }}</span>
                                <span class="text-[10px] text-[#64748B] uppercase">Views</span>
                            </div>
                            <div class="h-6 w-px bg-[#1E253A]"></div>
                            <div class="text-center px-2">
                                <span class="block text-xs font-bold text-rose-400 font-mono">{{ post.likes_count }}</span>
                                <span class="text-[10px] text-[#64748B] uppercase">Likes</span>
                            </div>
                            <div class="h-6 w-px bg-[#1E253A]"></div>
                            <div class="text-center px-2">
                                <span class="block text-xs font-bold text-emerald-400 font-mono">{{ post.comments_count }}</span>
                                <span class="text-[10px] text-[#64748B] uppercase">Replies</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 border-y border-[#1E253A] py-3 text-xs">
                    <button
                        class="flex items-center gap-2 rounded-xl px-4 py-2 font-medium transition"
                        :class="post.is_liked ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-[#121627] text-slate-300 border border-[#1E253A] hover:bg-[#181D33]'"
                        @click="likePost"
                    >
                        <i :class="post.is_liked ? 'fa-solid fa-heart text-rose-400' : 'fa-regular fa-heart'"></i>
                        <span>Like</span>
                    </button>

                    <button
                        class="flex items-center gap-2 rounded-xl bg-[#121627] px-4 py-2 font-medium text-slate-300 border border-[#1E253A] hover:bg-[#181D33] hover:text-amber-300 transition"
                        @click="likePost"
                    >
                        <i class="fa-regular fa-lightbulb text-amber-400"></i>
                        <span>Insightful</span>
                    </button>

                    <button
                        class="flex items-center gap-2 rounded-xl bg-[#121627] px-4 py-2 font-medium text-slate-300 border border-[#1E253A] hover:bg-[#181D33] hover:text-cyan-300 transition"
                        @click="likePost"
                    >
                        <i class="fa-regular fa-circle-question text-cyan-400"></i>
                        <span>Help</span>
                    </button>

                    <button
                        class="flex items-center gap-2 rounded-xl px-4 py-2 font-medium transition"
                        :class="post.is_bookmarked ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40' : 'bg-[#121627] text-slate-300 border border-[#1E253A] hover:bg-[#181D33]'"
                        @click="bookmarkPost"
                    >
                        <i :class="post.is_bookmarked ? 'fa-solid fa-bookmark text-purple-400' : 'fa-regular fa-bookmark'"></i>
                        <span>Bookmark</span>
                    </button>

                    <button
                        class="flex items-center gap-2 rounded-xl bg-[#121627] px-4 py-2 font-medium text-slate-300 border border-[#1E253A] hover:bg-[#181D33] transition ml-auto"
                        @click="copyPostLink"
                    >
                        <i :class="copied ? 'fa-solid fa-check text-cyan-400' : 'fa-solid fa-share-nodes'"></i>
                        <span>{{ copied ? 'Copied' : 'Share' }}</span>
                    </button>
                </div>

                <div class="prose-community text-base leading-8 text-[#CBD5E1]" v-html="renderedBody(post.body_markdown)"></div>

                <div v-if="post.tags?.length" class="flex flex-wrap gap-2 pt-2">
                    <span v-for="tag in post.tags" :key="tag.id" class="rounded-full border border-cyan-500/30 bg-cyan-500/10 px-3.5 py-1 font-mono text-xs text-cyan-300">
                        #{{ tag.name }}
                    </span>
                </div>

                <div v-if="post.images?.length" :class="['grid gap-3 pt-4', post.images.length === 1 ? 'grid-cols-1' : 'grid-cols-2']">
                    <button
                        v-for="image in post.images"
                        :key="image.id"
                        class="overflow-hidden rounded-2xl border border-[#1E253A] bg-[#090C16] transition hover:opacity-90 w-full"
                        @click="lightbox = image"
                    >
                        <img
                            :src="mediaUrl(image.path_webp)"
                            :alt="image.alt_text ?? 'Post image'"
                            :class="post.images.length === 1 ? 'max-h-[500px] w-full object-cover rounded-2xl' : 'aspect-video w-full object-cover transition hover:scale-105'"
                            @error="(e) => onImgError(e)"
                        />
                    </button>
                </div>

                <div class="mt-12 border-t border-[#1E253A] pt-8 space-y-6">
                    <h3 class="font-display text-xl font-bold text-white flex items-center justify-between">
                        <span>Discussion ({{ post.comments_count }} Comments)</span>
                    </h3>

                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-full bg-[#181D33] border border-purple-500/30 flex items-center justify-center font-bold text-xs text-purple-300 overflow-hidden shrink-0">
                            <img v-if="props.auth?.user?.avatar_path" :src="mediaUrl(props.auth?.user?.avatar_path)" class="h-full w-full object-cover" @error="(e) => onAvatarError(e, props.auth?.user?.name)" />
                            <span v-else>{{ props.auth?.user?.name?.charAt(0) ?? 'U' }}</span>
                        </div>
                        <input
                            ref="commentInputRef"
                            v-model="newCommentText"
                            type="text"
                            placeholder="Write a reply... (Enter to post)"
                            class="flex-1 rounded-xl border border-[#1E253A] bg-[#121627] px-4 py-2.5 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 transition"
                            @keydown.enter.prevent="submitComment"
                        />
                        <button
                            @click="submitComment"
                            :disabled="!newCommentText.trim() || submittingComment"
                            class="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-5 py-2.5 text-xs font-semibold text-white disabled:opacity-40 hover:shadow-lg hover:shadow-purple-500/20 transition flex items-center gap-1.5 shrink-0"
                        >
                            <i v-if="submittingComment" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <span v-else>Reply</span>
                        </button>
                    </div>

                    <div v-if="post.comments?.length" class="space-y-4 pt-2">
                        <CommentThread
                            v-for="comment in post.comments.filter((item) => !item.parent_id)"
                            :key="comment.id"
                            :comment="comment"
                            @like="likeComment"
                        />
                    </div>
                    <div v-else class="rounded-2xl border border-[#1E253A] bg-[#121627] p-8 text-center text-xs text-[#94A3B8]">
                        No replies yet. Be the first to share your engineering insight!
                    </div>
                </div>
            </article>
        </main>

        <div
            v-if="lightbox"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-6 backdrop-blur-md"
            @click.self="lightbox = null"
        >
            <button class="absolute right-6 top-5 text-2xl text-slate-300 hover:text-white transition" @click="lightbox = null">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img :src="mediaUrl(lightbox.path_webp)" :alt="lightbox.alt_text ?? ''" class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl border border-white/10" />
        </div>
    </div>
</template>
