<script setup>
import axios from 'axios';
import { marked } from 'marked';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import CommentThread from '../Components/CommentThread.vue';

const props = defineProps({
    posts: Object,
    view: { type: String, default: 'all' },
    trendingPosts: { type: Array, default: () => [] },
    mostViewedPosts: { type: Array, default: () => [] },
    topCreators: { type: Array, default: () => [] },
    popularTags: { type: Array, default: () => [] },
    auth: Object,
});

const activeView = ref(props.view);
const posts = ref(props.posts?.data ?? []);
const nextUrl = ref(props.posts?.links?.next ?? null);
const body = ref('');
const title = ref('');
const files = ref([]);
const loading = ref(false);
const newPosts = ref(false);
const lightbox = ref(null);
const copiedId = ref(null);
const userDropdownOpen = ref(false);
const searchQuery = ref('');
const activeCommentPostId = ref(null);
const commentInputs = ref({});
const submittingCommentPostId = ref(null);

const views = [
    { key: 'all', label: 'All Signals', icon: 'fa-solid fa-rss' },
    { key: 'today', label: "Today's Signals", icon: 'fa-solid fa-calendar-day', badge: 'Fresh' },
    { key: 'most-viewed', label: 'Most Viewed', icon: 'fa-solid fa-eye', badge: 'Top' },
    { key: 'trending', label: 'Trending', icon: 'fa-solid fa-fire', badge: 'Hot' },
    { key: 'following', label: 'Following', icon: 'fa-solid fa-user-group' },
    { key: 'my-stack', label: 'My Stack', icon: 'fa-solid fa-layer-group' },
    { key: 'bookmarks', label: 'Saved', icon: 'fa-solid fa-bookmark' },
];

function formatNumber(num) {
    if (!num) return '0';
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'k';
    return num.toString();
}

const recordedImpressions = new Set();
function recordImpression(post) {
    if (!post || recordedImpressions.has(post.id)) return;
    recordedImpressions.add(post.id);
    axios.post(`/posts/${post.id}/impression`).then((res) => {
        if (res.data?.data?.views_count) {
            post.views_count = res.data.data.views_count;
        }
    }).catch(() => {});
}

const renderedBody = (value) => marked.parse(value ?? '');

function selectFiles(event) {
    files.value = [...event.target.files];
}

async function publish() {
    if (!body.value.trim()) return;
    const form = new FormData();
    if (title.value) form.append('title', title.value);
    form.append('body_markdown', body.value);
    files.value.forEach((file) => form.append('images[]', file));
    await axios.post('/feed/posts', form);
    title.value = '';
    body.value = '';
    files.value = [];
    await reload();
}

async function reload() {
    const response = await axios.get(`/feed?view=${activeView.value}`, { headers: { Accept: 'application/json' } });
    posts.value = response.data.props.posts.data;
    nextUrl.value = response.data.props.posts.links.next;
}

async function changeView(view) {
    activeView.value = view;
    await reload();
}

async function loadMore() {
    if (!nextUrl.value || loading.value) return;
    loading.value = true;
    const response = await axios.get(nextUrl.value, { headers: { Accept: 'application/json' } });
    posts.value.push(...response.data.props.posts.data);
    nextUrl.value = response.data.props.posts.links.next;
    loading.value = false;
}

async function likePost(post) {
    const response = await axios.post(`/posts/${post.id}/like`);
    post.likes_count = response.data.data.likes_count;
    post.is_liked = response.data.data.liked;
}

async function sharePost(post) {
    const response = await axios.post(`/posts/${post.id}/share`);
    post.shares_count = response.data.data.shares_count;
    post.is_shared = response.data.data.shared;
    const url = `${window.location.origin}${postUrl(post)}`;
    navigator.clipboard?.writeText(url);
}

async function bookmarkPost(post) {
    const response = await axios.post(`/posts/${post.id}/bookmark`);
    post.is_bookmarked = response.data.data.bookmarked;
}

async function deletePost(post) {
    if (!window.confirm('Delete this post permanently?')) return;
    await axios.delete(`/posts/${post.id}`);
    posts.value = posts.value.filter((p) => p.id !== post.id);
}

function copyPostLink(post) {
    const url = `${window.location.origin}${postUrl(post)}`;
    navigator.clipboard?.writeText(url);
    copiedId.value = post.id;
    setTimeout(() => {
        if (copiedId.value === post.id) copiedId.value = null;
    }, 2000);
}

async function likeComment(comment) {
    const response = await axios.post(`/comments/${comment.id}/like`);
    comment.likes_count = response.data.data.likes_count;
}

function toggleCommentBox(post) {
    activeCommentPostId.value = activeCommentPostId.value === post.id ? null : post.id;
}

async function submitComment(post) {
    const text = commentInputs.value[post.id]?.trim();
    if (!text || submittingCommentPostId.value === post.id) return;
    commentInputs.value[post.id] = '';
    submittingCommentPostId.value = post.id;
    try {
        const response = await axios.post(`/posts/${post.id}/comments`, { body: text });
        if (!post.comments) post.comments = [];
        post.comments.push(response.data.data);
        post.comments_count = (post.comments_count ?? 0) + 1;
    } catch (e) {
        console.error('Failed to post comment', e);
        commentInputs.value[post.id] = text;
    } finally {
        submittingCommentPostId.value = null;
    }
}

function onFeedUpdate() {
    newPosts.value = true;
}

onMounted(() => {
    window.Echo?.channel('feed').listen('.PostCreated', onFeedUpdate);
});

onUnmounted(() => {
    window.Echo?.leave('feed');
});

const filteredPosts = computed(() => {
    if (!searchQuery.value.trim()) return posts.value;
    const query = searchQuery.value.toLowerCase();
    return posts.value.filter(
        (p) =>
            p.title?.toLowerCase().includes(query) ||
            p.body_markdown?.toLowerCase().includes(query) ||
            p.user?.name?.toLowerCase().includes(query)
    );
});

const canLoadMore = computed(() => Boolean(nextUrl.value));

function userProfileUrl(user) {
    if (!user) return '#';
    const identifier = user.username || user.id;
    return `/in/${identifier}`;
}

function postUrl(post) {
    if (!post) return '#';
    const identifier = post.slug || post.code || post.id;
    return `/posts/${identifier}`;
}

function onAvatarError(e, name = 'User') {
    e.target.onerror = null;
    e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=161B22&color=F5A623&bold=true`;
}

function onImgError(e) {
    e.target.onerror = null;
    e.target.style.display = 'none';
}

function mediaUrl(path) {
    if (!path) return '';
    return path.startsWith('http://') || path.startsWith('https://') ? path : `/storage/${path}`;
}
</script>

<template>
    <div class="min-h-screen bg-[#090C15] text-[#E2E8F0]">
        <header class="sticky top-0 z-40 border-b border-[#1E253A] bg-[#0E1220]/90 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
                <div class="flex items-center gap-4 flex-1 max-w-xl">
                    <a href="/feed" class="flex items-center gap-2.5 font-display text-lg font-bold tracking-tight text-white shrink-0">
                        <div class="h-8 w-8 rounded-lg bg-gradient-to-tr from-cyan-400 via-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                            <i class="fa-solid fa-wifi text-sm rotate-45"></i>
                        </div>
                        <span class="text-white text-base tracking-wide">Dev<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">Connect</span></span>
                    </a>

                    <div class="relative w-full hidden sm:block">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#64748B]">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search topics, code, people..."
                            class="w-full rounded-xl border border-[#1E253A] bg-[#121627] py-2 pl-9 pr-3 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    <a
                        href="/notifications"
                        class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-[#1E253A] bg-[#121627] text-[#94A3B8] hover:text-white hover:border-purple-500/50 transition"
                    >
                        <i class="fa-regular fa-bell text-sm"></i>
                        <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                        </span>
                    </a>

                    <div class="relative">
                        <button
                            @click="userDropdownOpen = !userDropdownOpen"
                            class="flex items-center gap-2 rounded-xl p-1 hover:bg-[#1A2138] transition border border-transparent hover:border-[#222B48]"
                        >
                            <div class="h-8 w-8 rounded-full bg-[#1A2138] border border-purple-500/50 flex items-center justify-center font-bold text-xs text-purple-300 overflow-hidden shadow-sm">
                                <img
                                    v-if="props.auth?.user?.avatar_path"
                                    :src="mediaUrl(props.auth?.user?.avatar_path)"
                                    :alt="props.auth?.user?.name"
                                    class="h-full w-full object-cover"
                                    @error="(e) => onAvatarError(e, props.auth?.user?.name)"
                                />
                                <span v-else>{{ props.auth?.user?.name?.charAt(0) ?? 'U' }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-[#64748B]"></i>
                        </button>

                        <div
                            v-if="userDropdownOpen"
                            class="absolute right-0 mt-2 w-56 rounded-xl border border-[#222B48] bg-[#111526] p-2 shadow-2xl z-50 text-xs backdrop-blur-xl"
                            @click="userDropdownOpen = false"
                        >
                            <div class="border-b border-[#1E253A] px-3 py-2">
                                <p class="font-semibold text-white truncate">{{ props.auth?.user?.name }}</p>
                                <p class="text-[11px] text-[#64748B] truncate">{{ props.auth?.user?.email }}</p>
                            </div>

                            <div class="py-1">
                                <a :href="userProfileUrl(props.auth?.user)" class="flex items-center gap-2.5 px-3 py-2 text-[#CBD5E1] hover:bg-[#1A2138] hover:text-white rounded-lg transition">
                                    <i class="fa-solid fa-user text-xs text-[#64748B]"></i>
                                    <span>Public Profile</span>
                                </a>
                                <a href="/profile" class="flex items-center gap-2.5 px-3 py-2 text-[#CBD5E1] hover:bg-[#1A2138] hover:text-white rounded-lg transition">
                                    <i class="fa-solid fa-gear text-xs text-[#64748B]"></i>
                                    <span>Account Settings</span>
                                </a>
                                <button @click="changeView('bookmarks')" class="flex w-full items-center gap-2.5 px-3 py-2 text-[#CBD5E1] hover:bg-[#1A2138] hover:text-white rounded-lg transition">
                                    <i class="fa-solid fa-bookmark text-xs text-[#64748B]"></i>
                                    <span>Saved Bookmarks</span>
                                </button>
                                <a
                                    v-if="props.auth?.user?.email === 'test@example.com' || props.auth?.user?.email === 'admin@dev.community'"
                                    href="/admin"
                                    class="flex items-center gap-2.5 px-3 py-2 text-cyan-400 hover:bg-[#1A2138] rounded-lg transition font-semibold"
                                >
                                    <i class="fa-solid fa-shield-halved text-xs text-cyan-400"></i>
                                    <span>Admin Panel</span>
                                </a>
                            </div>

                            <div class="border-t border-[#1E253A] pt-1">
                                <form method="post" action="/logout">
                                    <button type="submit" class="flex w-full items-center gap-2.5 px-3 py-2 text-[#F43F5E] hover:bg-rose-500/10 rounded-lg transition">
                                        <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                                        <span>Sign Out</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto grid max-w-7xl gap-6 px-4 py-6 md:grid-cols-[220px_1fr] lg:grid-cols-[240px_1fr_310px]">
            <aside class="hidden md:block space-y-5">
                <div class="rounded-2xl border border-[#1E253A] bg-[#111526] p-2 space-y-1 shadow-sm">
                    <button
                        v-for="item in views"
                        :key="item.key"
                        class="flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-xs transition text-left"
                        :class="activeView === item.key ? 'bg-purple-600/15 border border-purple-500/70 text-white font-semibold shadow-sm shadow-purple-500/10' : 'text-[#94A3B8] hover:bg-[#181D33] hover:text-white border border-transparent'"
                        @click="changeView(item.key)"
                    >
                        <div class="flex items-center gap-3">
                            <i :class="[item.icon, activeView === item.key ? 'text-purple-400' : 'text-[#64748B]']" class="text-xs"></i>
                            <span>{{ item.label }}</span>
                        </div>
                        <span
                            v-if="item.badge"
                            class="font-mono text-[9px] uppercase px-1.5 py-0.5 rounded font-bold"
                            :class="activeView === item.key ? 'bg-purple-500/30 text-purple-200' : 'bg-cyan-500/20 text-cyan-300'"
                        >
                            {{ item.badge }}
                        </span>
                    </button>
                </div>

                <div class="rounded-2xl border border-[#1E253A] bg-[#111526] p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="font-mono text-[11px] font-semibold uppercase tracking-wider text-[#64748B]">Tags</p>
                        <i class="fa-solid fa-hashtag text-xs text-[#64748B]"></i>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="tagName in ['React', 'Python', 'Tailwind', 'TypeScript', 'Laravel', 'Vue', 'Docker']"
                            :key="tagName"
                            class="rounded-full border border-[#222B48] bg-[#161B2E] px-3 py-1 text-xs font-medium text-[#94A3B8] hover:border-purple-500/60 hover:text-purple-300 hover:bg-purple-500/10 transition"
                            @click="searchQuery = tagName"
                        >
                            {{ tagName }}
                        </button>
                    </div>
                </div>

                <div class="rounded-2xl border border-[#1E253A] bg-[#111526] p-3">
                    <a href="/profile" class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs text-[#94A3B8] hover:bg-[#181D33] hover:text-white transition">
                        <i class="fa-solid fa-gear text-xs text-[#64748B]"></i>
                        <span>Account settings</span>
                    </a>
                </div>
            </aside>

            <section class="space-y-5">
                <div class="flex md:hidden gap-2 overflow-x-auto pb-2 border-b border-[#1E253A]">
                    <button
                        v-for="item in views"
                        :key="item.key"
                        class="shrink-0 rounded-xl px-3 py-1.5 text-xs flex items-center gap-1.5 transition"
                        :class="activeView === item.key ? 'bg-purple-600/20 border border-purple-500/70 text-white font-semibold' : 'bg-[#111526] text-[#94A3B8] border border-[#1E253A]'"
                        @click="changeView(item.key)"
                    >
                        <i :class="item.icon" class="text-[10px]"></i>
                        <span>{{ item.label }}</span>
                    </button>
                </div>
                <div class="rounded-2xl border border-[#1E253A] bg-[#111526] p-5 shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-9 w-9 rounded-full bg-[#181D33] border border-purple-500/40 flex items-center justify-center font-bold text-xs text-purple-300 overflow-hidden shrink-0">
                            <img
                                v-if="props.auth?.user?.avatar_path"
                                :src="mediaUrl(props.auth?.user?.avatar_path)"
                                :alt="props.auth?.user?.name"
                                class="h-full w-full object-cover"
                                @error="(e) => onAvatarError(e, props.auth?.user?.name)"
                            />
                            <span v-else>{{ props.auth?.user?.name?.charAt(0) ?? 'U' }}</span>
                        </div>
                        <input
                            v-model="title"
                            placeholder="Optional post title or architectural topic..."
                            class="w-full rounded-xl border border-[#1E253A] bg-[#0B0E19] px-3.5 py-2 text-sm text-white placeholder-[#64748B] outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                        />
                    </div>

                    <textarea
                        v-model="body"
                        rows="3"
                        placeholder="Share a code snippet, architectural insight, or lesson learned..."
                        class="w-full resize-y rounded-xl border border-[#1E253A] bg-[#0B0E19] p-3.5 text-sm text-white placeholder-[#64748B] outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                    ></textarea>

                    <div class="mt-3 flex items-center justify-between border-t border-[#1E253A] pt-3">
                        <label class="cursor-pointer text-xs text-[#94A3B8] hover:text-cyan-400 transition flex items-center gap-2">
                            <i class="fa-solid fa-image text-xs text-cyan-400"></i>
                            <span>Images <span v-if="files.length" class="text-purple-300 font-semibold">({{ files.length }})</span></span>
                            <input type="file" multiple accept="image/*" class="hidden" @change="selectFiles" />
                        </label>

                        <button
                            @click="publish"
                            class="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-5 py-2 text-xs font-semibold text-white transition hover:shadow-lg hover:shadow-purple-500/30 flex items-center gap-2"
                        >
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Publish</span>
                        </button>
                    </div>
                </div>

                <button
                    v-if="newPosts"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-purple-500/40 bg-purple-500/10 py-2.5 text-xs font-semibold text-purple-300 hover:bg-purple-500/20 transition"
                    @click="newPosts = false; reload()"
                >
                    <i class="fa-solid fa-arrows-rotate text-xs animate-spin"></i>
                    <span>New technical signals published · Click to refresh</span>
                </button>

                <div v-if="filteredPosts.length" class="space-y-5">
                    <article
                        v-for="post in filteredPosts"
                        :key="post.id"
                        class="rounded-2xl border border-[#1E253A] bg-[#111526] p-6 transition duration-200 hover:border-purple-500/40 shadow-sm"
                        @mouseenter="recordImpression(post)"
                    >
                        <div class="mb-4 flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <a :href="userProfileUrl(post.user)" class="h-10 w-10 rounded-full bg-[#181D33] border border-purple-500/30 flex items-center justify-center font-bold text-xs text-purple-300 overflow-hidden shrink-0">
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
                                    <div class="flex items-center gap-2">
                                        <a :href="userProfileUrl(post.user)" class="text-sm font-bold text-white hover:text-purple-300 transition">
                                            {{ post.user?.name }}
                                        </a>
                                        <span class="text-xs text-[#64748B]">@{{ post.user?.name?.toLowerCase().replace(/\s+/g, '') }}</span>
                                        <i class="fa-solid fa-circle-check text-[11px] text-cyan-400"></i>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-[#64748B] mt-0.5">
                                        <span class="text-[#94A3B8]">{{ post.user?.current_status || 'Frontend Lead' }}</span>
                                        <span>·</span>
                                        <span>{{ post.created_at }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    v-if="props.auth?.user?.id === post.user?.id"
                                    class="text-xs text-[#64748B] hover:text-[#F43F5E] transition"
                                    title="Delete post"
                                    @click="deletePost(post)"
                                >
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                <a :href="postUrl(post)" class="text-xs font-mono text-[#64748B] hover:text-cyan-400 transition" @click="recordImpression(post)">
                                    {{ post.code || ('#' + post.id.toString().padStart(4, '0')) }}
                                </a>
                            </div>
                        </div>

                        <h2 v-if="post.title" class="font-display mb-3 text-lg font-bold text-white">
                            <a :href="postUrl(post)" class="hover:text-purple-300 transition" @click="recordImpression(post)">{{ post.title }}</a>
                        </h2>

                        <div class="prose-community text-sm leading-relaxed text-[#CBD5E1]" v-html="renderedBody(post.body_markdown)"></div>

                        <div v-if="post.tags?.length" class="mt-4 flex flex-wrap gap-2">
                            <span v-for="tag in post.tags" :key="tag.id" class="rounded-full border border-cyan-500/30 bg-cyan-500/10 px-3 py-0.5 font-mono text-xs text-cyan-300">
                                #{{ tag.name }}
                            </span>
                        </div>

                        <div v-if="post.images?.length" :class="['mt-4 grid gap-2.5', post.images.length === 1 ? 'grid-cols-1' : 'grid-cols-2']">
                            <button
                                v-for="image in post.images"
                                :key="image.id"
                                class="overflow-hidden rounded-xl border border-[#1E253A] bg-[#090C16] transition hover:opacity-95 w-full"
                                @click="lightbox = image"
                            >
                                <img
                                    :src="mediaUrl(image.path_webp)"
                                    :alt="image.alt_text ?? 'Post media'"
                                    :class="post.images.length === 1 ? 'max-h-[460px] w-full object-cover rounded-xl' : 'aspect-video w-full object-cover transition hover:scale-105'"
                                    @error="(e) => onImgError(e)"
                                />
                            </button>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center justify-between border-t border-[#1E253A] pt-4 text-xs text-[#94A3B8]">
                            <div class="flex items-center gap-2">
                                <span class="rounded-full border border-[#222B48] bg-[#161B2E] px-2.5 py-1 text-[11px] font-mono text-[#94A3B8] flex items-center gap-1.5">
                                    <i class="fa-solid fa-tag text-[10px] text-purple-400"></i>
                                    <span>Tags</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-5 sm:gap-6">
                                <span class="flex items-center gap-1.5 text-xs text-[#94A3B8]" title="Impressions & Views">
                                    <i class="fa-regular fa-eye text-cyan-400 text-xs"></i>
                                    <span>{{ formatNumber(post.views_count ?? 0) }}</span>
                                </span>

                                <button
                                    class="flex items-center gap-1.5 transition"
                                    :class="post.is_liked ? 'text-rose-400 font-bold' : 'hover:text-rose-400'"
                                    @click="likePost(post)"
                                >
                                    <i :class="post.is_liked ? 'fa-solid fa-heart text-rose-400' : 'fa-regular fa-heart'"></i>
                                    <span>{{ post.likes_count }}</span>
                                </button>

                                <button class="flex items-center gap-1.5 hover:text-purple-300 transition" @click="toggleCommentBox(post)">
                                    <i class="fa-regular fa-comment"></i>
                                    <span>{{ post.comments_count }}</span>
                                </button>

                                <button
                                    class="flex items-center gap-1.5 transition"
                                    :class="post.is_shared ? 'text-cyan-400 font-bold' : 'hover:text-cyan-400'"
                                    @click="sharePost(post)"
                                >
                                    <i class="fa-solid fa-share-nodes"></i>
                                    <span>{{ post.shares_count ?? 0 }}</span>
                                </button>

                                <button
                                    class="flex items-center gap-1.5 transition"
                                    :class="post.is_bookmarked ? 'text-purple-400 font-bold' : 'hover:text-purple-400'"
                                    @click="bookmarkPost(post)"
                                >
                                    <i :class="post.is_bookmarked ? 'fa-solid fa-bookmark text-purple-400' : 'fa-regular fa-bookmark'"></i>
                                </button>
                            </div>
                        </div>

                        <div v-if="activeCommentPostId === post.id" class="mt-4 flex items-center gap-2 pt-4 border-t border-[#1E253A]">
                            <div class="h-8 w-8 rounded-full bg-[#181D33] border border-purple-500/30 flex items-center justify-center font-bold text-xs text-purple-300 overflow-hidden shrink-0">
                                <img v-if="props.auth?.user?.avatar_path" :src="mediaUrl(props.auth?.user?.avatar_path)" class="h-full w-full object-cover" @error="(e) => onAvatarError(e, props.auth?.user?.name)" />
                                <span v-else>{{ props.auth?.user?.name?.charAt(0) ?? 'U' }}</span>
                            </div>
                            <input
                                v-model="commentInputs[post.id]"
                                type="text"
                                placeholder="Write a response... (Enter to reply)"
                                class="flex-1 rounded-xl border border-[#1E253A] bg-[#0B0E19] px-3.5 py-2 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 transition"
                                @keydown.enter.prevent="submitComment(post)"
                            />
                            <button
                                @click="submitComment(post)"
                                :disabled="!commentInputs[post.id]?.trim() || submittingCommentPostId === post.id"
                                class="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white disabled:opacity-40 transition flex items-center gap-1.5 shrink-0"
                            >
                                <i v-if="submittingCommentPostId === post.id" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                <i v-else class="fa-solid fa-paper-plane text-xs"></i>
                                <span>Reply</span>
                            </button>
                        </div>

                        <div v-if="post.comments?.length" class="mt-4 space-y-3 border-t border-[#1E253A] pt-4">
                            <CommentThread
                                v-for="comment in post.comments.filter((item) => !item.parent_id)"
                                :key="comment.id"
                                :comment="comment"
                                @like="likeComment"
                            />
                        </div>
                    </article>
                </div>

                <div v-else class="rounded-2xl border border-[#1E253A] bg-[#111526] p-10 text-center text-xs text-[#94A3B8]">
                    <i class="fa-solid fa-inbox text-3xl text-[#1E253A] mb-3 block"></i>
                    No signals found in this view. Share the first update!
                </div>

                <button
                    v-if="canLoadMore"
                    class="rounded-xl border border-[#1E253A] bg-[#111526] mt-4 w-full py-3 text-xs font-semibold text-cyan-400 hover:bg-[#181D33] transition flex items-center justify-center gap-2"
                    @click="loadMore"
                >
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                    <span>{{ loading ? 'Loading older signal...' : 'Load older signal' }}</span>
                </button>
            </section>

            <aside class="hidden lg:block space-y-5">
                <div class="rounded-2xl border border-[#1E253A] bg-[#111526] p-5 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#1E253A] pb-3">
                        <h3 class="font-display text-sm font-bold text-white tracking-wide">Trending Discussions</h3>
                        <span class="text-[11px] text-[#64748B] font-medium">Activity</span>
                    </div>

                    <div v-if="props.trendingPosts?.length" class="space-y-3">
                        <div
                            v-for="trPost in props.trendingPosts.slice(0, 4)"
                            :key="trPost.id"
                            class="group block hover:bg-[#181D33] p-2 rounded-xl transition"
                        >
                            <a
                                :href="postUrl(trPost)"
                                class="font-semibold text-white text-xs hover:text-purple-300 transition line-clamp-1 block"
                            >
                                {{ trPost.title || 'State Management Architecture' }}
                            </a>
                            <div class="mt-1 flex items-center justify-between text-[11px] text-[#64748B]">
                                <span class="text-[#94A3B8]">{{ trPost.comments_count ?? 18 }} replies</span>
                                <span class="text-cyan-400 font-medium">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="props.topCreators?.length" class="rounded-2xl border border-[#1E253A] bg-[#111526] p-5 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#1E253A] pb-3">
                        <h3 class="font-display text-sm font-bold text-white tracking-wide">Top Contributors</h3>
                        <span class="text-[11px] text-[#64748B] font-medium">Activity</span>
                    </div>

                    <div class="space-y-3.5">
                        <div
                            v-for="creator in props.topCreators.slice(0, 4)"
                            :key="creator.id"
                            class="flex items-center justify-between gap-3 hover:bg-[#181D33] p-1.5 rounded-xl transition"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <a :href="userProfileUrl(creator)" class="h-9 w-9 rounded-full bg-[#181D33] border border-purple-500/30 flex items-center justify-center font-bold text-xs text-purple-300 overflow-hidden shrink-0">
                                    <img
                                        v-if="creator.avatar_path"
                                        :src="mediaUrl(creator.avatar_path)"
                                        :alt="creator.name"
                                        class="h-full w-full object-cover"
                                        @error="(e) => onAvatarError(e, creator.name)"
                                    />
                                    <span v-else>{{ creator.name?.charAt(0) ?? 'U' }}</span>
                                </a>
                                <div class="min-w-0">
                                    <a :href="userProfileUrl(creator)" class="text-xs font-bold text-white hover:text-purple-300 transition truncate block">
                                        {{ creator.name }}
                                    </a>
                                    <p class="text-[11px] text-[#64748B] truncate">
                                        {{ creator.current_status || 'Frontend Lead' }}
                                    </p>
                                </div>
                            </div>
                            <span class="shrink-0 text-xs font-mono font-semibold text-purple-300 bg-purple-500/10 border border-purple-500/20 px-2 py-0.5 rounded-lg">
                                {{ (creator.posts_count * 12) + 45 }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-[#1E253A] bg-[#111526] p-4 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-semibold text-white">
                        <i class="fa-solid fa-circle text-[8px] text-emerald-400 animate-pulse"></i>
                        <span>Pusher Realtime Engine</span>
                    </div>
                    <p class="text-[11px] text-[#64748B] leading-5">
                        WebSockets: <span class="text-emerald-400 font-semibold">Connected & Synced</span>
                    </p>
                </div>
            </aside>
        </main>

        <div
            v-if="lightbox"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6 backdrop-blur-md"
            @click.self="lightbox = null"
        >
            <button class="absolute right-6 top-5 text-2xl text-slate-300 hover:text-white transition" @click="lightbox = null">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img :src="mediaUrl(lightbox.path_webp)" :alt="lightbox.alt_text ?? ''" class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl border border-white/10" />
        </div>
    </div>
</template>