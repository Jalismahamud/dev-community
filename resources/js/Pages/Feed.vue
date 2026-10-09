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
    <div class="min-h-screen bg-[#0D1117] text-[#E6E6E6]">
        <!-- TOP NAVIGATION BAR (LinkedIn / Facebook Style) -->
        <header class="sticky top-0 z-40 border-b border-[#232A34] bg-[#161B22]/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2.5">
                <!-- Left: Brand & Search -->
                <div class="flex items-center gap-4 flex-1 max-w-md">
                    <a href="/feed" class="flex items-center gap-2 font-display text-lg font-bold tracking-tight text-white shrink-0">
                        <i class="fa-solid fa-terminal text-[#F5A623]"></i>
                        <span><span class="text-[#F5A623]">dev</span>/community</span>
                    </a>

                    <div class="relative w-full hidden sm:block">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#8B949E]">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search posts, tags, builders..."
                            class="w-full rounded-md border border-[#232A34] bg-[#0D1117] py-1.5 pl-8 pr-3 text-xs text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623]"
                        />
                    </div>
                </div>

                <!-- Right: Navigation Items & User Profile Dropdown -->
                <div class="flex items-center gap-1 sm:gap-4">
                    <nav class="flex items-center gap-1 text-xs">
                        <a href="/feed" class="flex flex-col items-center px-3 py-1 text-[#F5A623] hover:text-white transition">
                            <i class="fa-solid fa-house text-base"></i>
                            <span class="hidden md:inline text-[10px] mt-0.5 font-medium">Home</span>
                        </a>

                        <a href="/chat" class="flex flex-col items-center px-3 py-1 text-[#8B949E] hover:text-white transition">
                            <i class="fa-solid fa-comments text-base"></i>
                            <span class="hidden md:inline text-[10px] mt-0.5 font-medium">Messaging</span>
                        </a>

                        <a href="/notifications" class="flex flex-col items-center px-3 py-1 text-[#8B949E] hover:text-white transition relative">
                            <i class="fa-solid fa-bell text-base"></i>
                            <span class="hidden md:inline text-[10px] mt-0.5 font-medium">Alerts</span>
                        </a>
                    </nav>

                    <div class="h-6 w-[1px] bg-[#232A34] mx-1"></div>

                    <!-- User Avatar & Dropdown Menu -->
                    <div class="relative">
                        <button
                            @click="userDropdownOpen = !userDropdownOpen"
                            class="flex items-center gap-2 rounded-full p-1 hover:bg-[#232A34] transition"
                        >
                            <div class="h-8 w-8 rounded-full bg-[#232A34] border border-[#F5A623] flex items-center justify-center font-bold text-xs text-[#F5A623] overflow-hidden">
                                <img
                                    v-if="props.auth?.user?.avatar_path"
                                    :src="mediaUrl(props.auth?.user?.avatar_path)"
                                    :alt="props.auth?.user?.name"
                                    class="h-full w-full object-cover"
                                    @error="(e) => onAvatarError(e, props.auth?.user?.name)"
                                />
                                <span v-else>{{ props.auth?.user?.name?.charAt(0) ?? 'U' }}</span>
                            </div>
                            <i class="fa-solid fa-caret-down text-[10px] text-[#8B949E]"></i>
                        </button>

                        <div
                            v-if="userDropdownOpen"
                            class="absolute right-0 mt-2 w-56 rounded-md border border-[#232A34] bg-[#161B22] p-2 shadow-2xl z-50 text-xs"
                            @click="userDropdownOpen = false"
                        >
                            <div class="border-b border-[#232A34] px-3 py-2">
                                <p class="font-semibold text-white truncate">{{ props.auth?.user?.name }}</p>
                                <p class="text-[11px] text-[#8B949E] truncate">{{ props.auth?.user?.email }}</p>
                            </div>

                            <div class="py-1">
                                <a :href="userProfileUrl(props.auth?.user)" class="flex items-center gap-2.5 px-3 py-2 text-[#C7CDD4] hover:bg-[#232A34] hover:text-white rounded transition">
                                    <i class="fa-solid fa-user text-xs text-[#8B949E]"></i>
                                    <span>Public Profile</span>
                                </a>
                                <a href="/profile" class="flex items-center gap-2.5 px-3 py-2 text-[#C7CDD4] hover:bg-[#232A34] hover:text-white rounded transition">
                                    <i class="fa-solid fa-gear text-xs text-[#8B949E]"></i>
                                    <span>Account Settings</span>
                                </a>
                                <button @click="changeView('bookmarks')" class="flex w-full items-center gap-2.5 px-3 py-2 text-[#C7CDD4] hover:bg-[#232A34] hover:text-white rounded transition">
                                    <i class="fa-solid fa-bookmark text-xs text-[#8B949E]"></i>
                                    <span>Saved Bookmarks</span>
                                </button>
                                <a
                                    v-if="props.auth?.user?.email === 'test@example.com' || props.auth?.user?.email === 'admin@dev.community'"
                                    href="/admin"
                                    class="flex items-center gap-2.5 px-3 py-2 text-[#F5A623] hover:bg-[#232A34] rounded transition font-semibold"
                                >
                                    <i class="fa-solid fa-shield-halved text-xs text-[#F5A623]"></i>
                                    <span>Admin Panel</span>
                                </a>
                            </div>

                            <div class="border-t border-[#232A34] pt-1">
                                <form method="post" action="/logout">
                                    <button type="submit" class="flex w-full items-center gap-2.5 px-3 py-2 text-[#FF6B6B] hover:bg-[#FF6B6B15] rounded transition">
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

        <!-- 3-COLUMN COMMUNITY LAYOUT (LinkedIn / Facebook Style) -->
        <main class="mx-auto grid max-w-7xl gap-6 px-4 py-6 md:grid-cols-[240px_1fr] lg:grid-cols-[260px_1fr_300px]">
            <!-- LEFT SIDEBAR -->
            <aside class="hidden md:block space-y-4">
                <!-- User Profile Mini-Card -->
                <div class="surface border border-[#232A34] bg-[#161B22] rounded-lg overflow-hidden">
                    <div class="h-16 bg-gradient-to-r from-[#0FA3B133] to-[#F5A62333] border-b border-[#232A34]"></div>
                    <div class="px-4 pb-4 -mt-8 text-center">
                        <div class="mx-auto h-16 w-16 rounded-full border-2 border-[#161B22] bg-[#0D1117] flex items-center justify-center font-bold text-lg text-[#F5A623] overflow-hidden shadow-lg">
                            <img
                                v-if="props.auth?.user?.avatar_path"
                                :src="mediaUrl(props.auth?.user?.avatar_path)"
                                :alt="props.auth?.user?.name"
                                class="h-full w-full object-cover"
                                @error="(e) => onAvatarError(e, props.auth?.user?.name)"
                            />
                            <span v-else>{{ props.auth?.user?.name?.charAt(0) ?? 'U' }}</span>
                        </div>
                        <h2 class="font-display mt-2 text-sm font-bold text-white truncate">{{ props.auth?.user?.name }}</h2>
                        <p class="text-[11px] text-[#8B949E] truncate">{{ props.auth?.user?.current_status ?? 'Software Engineer' }}</p>

                        <div class="mt-4 border-t border-[#232A34] pt-3 flex justify-around text-center text-xs">
                            <a :href="userProfileUrl(props.auth?.user)" class="text-[#8B949E] hover:text-[#53D2DE] transition">
                                <span class="block font-bold text-white">{{ props.posts?.data?.length ?? 0 }}</span>
                                <span class="text-[10px]">Signals</span>
                            </a>
                            <a :href="userProfileUrl(props.auth?.user)" class="text-[#8B949E] hover:text-[#53D2DE] transition">
                                <span class="block font-bold text-white">Active</span>
                                <span class="text-[10px]">Status</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Feed Navigation Filter Menu -->
                <div class="surface border border-[#232A34] bg-[#161B22] p-3 rounded-lg space-y-1">
                    <p class="px-3 py-1 font-mono text-[10px] uppercase tracking-wider text-[#0FA3B1]">Signal Feeds</p>
                    <button
                        v-for="item in views"
                        :key="item.key"
                        class="flex w-full items-center justify-between rounded-md px-3 py-2 text-xs transition text-left"
                        :class="activeView === item.key ? 'bg-[#F5A623] text-[#10141A] font-bold shadow-sm' : 'text-[#8B949E] hover:bg-[#232A34] hover:text-white'"
                        @click="changeView(item.key)"
                    >
                        <div class="flex items-center gap-2.5">
                            <i :class="item.icon" class="text-xs"></i>
                            <span>{{ item.label }}</span>
                        </div>
                        <span
                            v-if="item.badge"
                            class="font-mono text-[9px] uppercase px-1.5 py-0.5 rounded font-bold"
                            :class="activeView === item.key ? 'bg-black/25 text-[#10141A]' : 'bg-[#0FA3B1]/20 text-[#53D2DE]'"
                        >
                            {{ item.badge }}
                        </span>
                    </button>
                </div>

                <!-- Popular Tech Stacks -->
                <div v-if="props.popularTags?.length" class="surface border border-[#232A34] bg-[#161B22] p-4 rounded-lg space-y-2.5">
                    <div class="flex items-center justify-between">
                        <p class="font-mono text-[10px] uppercase tracking-wider text-[#0FA3B1]">Active Stacks</p>
                        <i class="fa-solid fa-tags text-xs text-[#8B949E]"></i>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="tag in props.popularTags"
                            :key="tag.id"
                            class="rounded border border-[#232A34] bg-[#0D1117] px-2 py-1 text-[11px] font-mono text-[#8B949E] hover:border-[#0FA3B1] hover:text-[#53D2DE] transition flex items-center gap-1"
                            @click="searchQuery = tag.name"
                        >
                            <span>#{{ tag.name }}</span>
                            <span v-if="tag.posts_count" class="text-[9px] text-[#484F58]">({{ tag.posts_count }})</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="surface border border-[#232A34] bg-[#161B22] p-4 rounded-lg space-y-2 text-xs">
                    <p class="font-mono text-[10px] uppercase tracking-wider text-[#0FA3B1]">Quick Access</p>
                    <a href="/profile" class="flex items-center gap-2 text-[#8B949E] hover:text-white transition py-1">
                        <i class="fa-solid fa-sliders text-xs text-[#F5A623]"></i>
                        <span>Profile & Tech Stack</span>
                    </a>
                    <a href="/chat" class="flex items-center gap-2 text-[#8B949E] hover:text-white transition py-1">
                        <i class="fa-solid fa-comments text-xs text-[#53D2DE]"></i>
                        <span>Direct Messages</span>
                    </a>
                    <a href="/notifications" class="flex items-center gap-2 text-[#8B949E] hover:text-white transition py-1">
                        <i class="fa-solid fa-bell text-xs text-[#0FA3B1]"></i>
                        <span>Notifications</span>
                    </a>
                </div>
            </aside>

            <!-- CENTER MAIN FEED -->
            <section class="space-y-4">
                <!-- Mobile Feed Filter Pill Bar -->
                <div class="flex md:hidden gap-2 overflow-x-auto pb-1 border-b border-[#232A34]">
                    <button
                        v-for="item in views"
                        :key="item.key"
                        class="shrink-0 rounded-full px-3 py-1.5 text-xs flex items-center gap-1.5 transition"
                        :class="activeView === item.key ? 'bg-[#F5A623] text-[#10141A] font-bold' : 'surface text-[#8B949E]'"
                        @click="changeView(item.key)"
                    >
                        <i :class="item.icon" class="text-[10px]"></i>
                        <span>{{ item.label }}</span>
                    </button>
                </div>

                <!-- Post Creation Box (LinkedIn Composer Style) -->
                <div class="surface border border-[#232A34] bg-[#161B22] p-4 rounded-lg shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-9 w-9 rounded-full bg-[#0D1117] border border-[#232A34] flex items-center justify-center font-bold text-xs text-[#F5A623] overflow-hidden shrink-0">
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
                            class="w-full rounded-md border border-[#232A34] bg-[#0D1117] px-3 py-2 text-sm text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623]"
                        />
                    </div>

                    <textarea
                        v-model="body"
                        rows="3"
                        placeholder="Share a code snippet, architectural insight, or lesson learned..."
                        class="w-full resize-y rounded-md border border-[#232A34] bg-[#0D1117] p-3 text-sm text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623]"
                    ></textarea>

                    <div class="mt-3 flex items-center justify-between border-t border-[#232A34] pt-3">
                        <label class="cursor-pointer text-xs text-[#8B949E] hover:text-[#53D2DE] transition flex items-center gap-1.5">
                            <i class="fa-solid fa-image text-xs text-[#0FA3B1]"></i>
                            <span>Images <span v-if="files.length" class="text-[#F5A623]">({{ files.length }})</span></span>
                            <input type="file" multiple accept="image/*" class="hidden" @change="selectFiles" />
                        </label>

                        <button
                            @click="publish"
                            class="rounded-md bg-[#F5A623] px-4 py-1.5 text-xs font-bold text-[#10141A] transition hover:shadow-[0_0_15px_#F5A62355] flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Publish</span>
                        </button>
                    </div>
                </div>

                <!-- Real-time Update Alert -->
                <button
                    v-if="newPosts"
                    class="flex w-full items-center justify-center gap-2 rounded-md border border-[#F5A623] bg-[#F5A6231a] py-2 text-xs font-semibold text-[#F5A623] hover:bg-[#F5A6232a] transition"
                    @click="newPosts = false; reload()"
                >
                    <i class="fa-solid fa-arrows-rotate text-xs animate-spin"></i>
                    <span>New technical signals published · Click to refresh</span>
                </button>

                <!-- Posts List -->
                <div v-if="filteredPosts.length" class="space-y-4">
                    <article
                        v-for="post in filteredPosts"
                        :key="post.id"
                        class="surface border border-[#232A34] bg-[#161B22] p-5 rounded-lg transition hover:border-[#2f3846]"
                        @mouseenter="recordImpression(post)"
                    >
                        <!-- Author Header -->
                        <div class="mb-3 flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <a :href="userProfileUrl(post.user)" class="h-9 w-9 rounded-full bg-[#0D1117] border border-[#232A34] flex items-center justify-center font-bold text-xs text-[#F5A623] overflow-hidden shrink-0">
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
                                    <a :href="userProfileUrl(post.user)" class="text-xs font-bold text-white hover:text-[#F5A623] transition flex items-center gap-1">
                                        <span>{{ post.user?.name }}</span>
                                        <i class="fa-solid fa-circle-check text-[10px] text-[#0FA3B1]"></i>
                                    </a>
                                    <div class="flex items-center gap-2 text-[10px] text-[#8B949E]">
                                        <span>{{ post.created_at }}</span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1 text-[#0FA3B1]"><i class="fa-regular fa-eye text-[9px]"></i> {{ formatNumber(post.views_count ?? 0) }} views</span>
                                        <span>•</span>
                                        <i class="fa-solid fa-earth-americas text-[9px]"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    v-if="props.auth?.user?.id === post.user?.id"
                                    class="text-xs text-[#8B949E] hover:text-[#FF6B6B] transition"
                                    title="Delete post"
                                    @click="deletePost(post)"
                                >
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                <a :href="postUrl(post)" class="text-xs font-mono text-[#8B949E] hover:text-[#53D2DE] transition" @click="recordImpression(post)">
                                    {{ post.code || ('#' + post.id.toString().padStart(4, '0')) }}
                                </a>
                            </div>
                        </div>

                        <!-- Post Content -->
                        <h2 v-if="post.title" class="font-display mb-2 text-lg font-bold text-white">
                            <a :href="postUrl(post)" class="hover:text-[#F5A623] transition" @click="recordImpression(post)">{{ post.title }}</a>
                        </h2>

                        <div class="prose-community text-sm leading-7 text-[#C7CDD4]" v-html="renderedBody(post.body_markdown)"></div>

                        <!-- Tags -->
                        <div v-if="post.tags?.length" class="mt-3 flex flex-wrap gap-1.5">
                            <span v-for="tag in post.tags" :key="tag.id" class="rounded-full border border-[#0FA3B144] bg-[#0FA3B110] px-2.5 py-0.5 font-mono text-[11px] text-[#53D2DE]">
                                #{{ tag.name }}
                            </span>
                        </div>

                        <!-- WebP Compressed Images Grid -->
                        <div v-if="post.images?.length" :class="['mt-4 grid gap-2', post.images.length === 1 ? 'grid-cols-1' : 'grid-cols-2']">
                            <button
                                v-for="image in post.images"
                                :key="image.id"
                                class="overflow-hidden rounded-md border border-[#232A34] bg-[#0D1117] transition hover:opacity-90 w-full"
                                @click="lightbox = image"
                            >
                                <img
                                    :src="mediaUrl(image.path_webp)"
                                    :alt="image.alt_text ?? 'Post media'"
                                    :class="post.images.length === 1 ? 'max-h-[460px] w-full object-cover rounded-md' : 'aspect-video w-full object-cover transition hover:scale-105'"
                                    @error="(e) => onImgError(e)"
                                />
                            </button>
                        </div>

                        <!-- Interaction Bar (LinkedIn/Facebook Style) -->
                        <div class="mt-4 flex flex-wrap items-center justify-between border-t border-[#232A34] pt-3 text-xs text-[#8B949E]">
                            <div class="flex items-center gap-4 sm:gap-6">
                                <button
                                    class="flex items-center gap-1.5 transition"
                                    :class="post.is_liked ? 'text-[#FF6B6B] font-bold' : 'hover:text-[#FF6B6B]'"
                                    @click="likePost(post)"
                                >
                                    <i :class="post.is_liked ? 'fa-solid fa-heart text-[#FF6B6B]' : 'fa-regular fa-heart'"></i>
                                    <span>{{ post.likes_count }}</span>
                                </button>

                                <button class="flex items-center gap-1.5 hover:text-[#F5A623] transition" @click="toggleCommentBox(post)">
                                    <i class="fa-regular fa-comment"></i>
                                    <span>{{ post.comments_count }}</span>
                                </button>

                                <button
                                    class="flex items-center gap-1.5 transition"
                                    :class="post.is_shared ? 'text-[#53D2DE] font-bold' : 'hover:text-[#53D2DE]'"
                                    @click="sharePost(post)"
                                >
                                    <i class="fa-solid fa-share-nodes"></i>
                                    <span>{{ post.shares_count ?? 0 }}</span>
                                </button>

                                <button
                                    class="flex items-center gap-1.5 transition"
                                    :class="post.is_bookmarked ? 'text-[#F5A623] font-bold' : 'hover:text-[#F5A623]'"
                                    @click="bookmarkPost(post)"
                                >
                                    <i :class="post.is_bookmarked ? 'fa-solid fa-bookmark text-[#F5A623]' : 'fa-regular fa-bookmark'"></i>
                                    <span class="hidden sm:inline">{{ post.is_bookmarked ? 'Saved' : 'Save' }}</span>
                                </button>
                            </div>

                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1.5 text-[11px] font-mono text-[#8B949E]" title="Impressions & Views">
                                    <i class="fa-regular fa-eye text-xs text-[#0FA3B1]"></i>
                                    <span>{{ formatNumber(post.views_count ?? 0) }}</span>
                                </span>

                                <button class="flex items-center gap-1.5 hover:text-white transition" @click="copyPostLink(post)">
                                    <i :class="copiedId === post.id ? 'fa-solid fa-check text-[#0FA3B1]' : 'fa-solid fa-link'"></i>
                                    <span>{{ copiedId === post.id ? 'Copied' : 'Share' }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Inline Reply Box -->
                        <div v-if="activeCommentPostId === post.id" class="mt-3 flex items-center gap-2 pt-3 border-t border-[#232A34]">
                            <div class="h-7 w-7 rounded-full bg-[#0D1117] border border-[#232A34] flex items-center justify-center font-bold text-[10px] text-[#F5A623] overflow-hidden shrink-0">
                                <img v-if="props.auth?.user?.avatar_path" :src="mediaUrl(props.auth?.user?.avatar_path)" class="h-full w-full object-cover" @error="(e) => onAvatarError(e, props.auth?.user?.name)" />
                                <span v-else>{{ props.auth?.user?.name?.charAt(0) ?? 'U' }}</span>
                            </div>
                            <input
                                v-model="commentInputs[post.id]"
                                type="text"
                                placeholder="Add your response... (Enter to reply)"
                                class="flex-1 rounded-md border border-[#232A34] bg-[#0D1117] px-3 py-1.5 text-xs text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623] transition"
                                @keydown.enter.prevent="submitComment(post)"
                            />
                            <button
                                @click="submitComment(post)"
                                :disabled="!commentInputs[post.id]?.trim() || submittingCommentPostId === post.id"
                                class="rounded-md bg-[#F5A623] px-3.5 py-1.5 text-xs font-bold text-[#10141A] disabled:opacity-40 hover:shadow-[0_0_10px_#F5A62355] transition flex items-center gap-1 shrink-0"
                            >
                                <i v-if="submittingCommentPostId === post.id" class="fa-solid fa-circle-notch fa-spin text-[10px]"></i>
                                <i v-else class="fa-solid fa-paper-plane text-[10px]"></i>
                                <span>Reply</span>
                            </button>
                        </div>

                        <!-- Threaded Comments -->
                        <div v-if="post.comments?.length" class="mt-4 space-y-3 border-t border-[#232A34] pt-4">
                            <CommentThread
                                v-for="comment in post.comments.filter((item) => !item.parent_id)"
                                :key="comment.id"
                                :comment="comment"
                                @like="likeComment"
                            />
                        </div>
                    </article>
                </div>

                <div v-else class="surface border border-[#232A34] bg-[#161B22] p-8 text-center text-xs text-[#8B949E] rounded-lg">
                    <i class="fa-solid fa-inbox text-2xl text-[#232A34] mb-2 block"></i>
                    No signals found in this view. Share the first update!
                </div>

                <!-- Load More Button -->
                <button
                    v-if="canLoadMore"
                    class="surface border border-[#232A34] bg-[#161B22] mt-4 w-full rounded-md py-3 text-xs font-semibold text-[#0FA3B1] hover:bg-[#232A34] transition flex items-center justify-center gap-2"
                    @click="loadMore"
                >
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                    <span>{{ loading ? 'Loading older signal...' : 'Load older signal' }}</span>
                </button>
            </section>

            <!-- RIGHT SIDEBAR (Widgets / Trending, Most Viewed & Creators) -->
            <aside class="hidden lg:block space-y-4">
                <!-- Most Viewed Signals Widget -->
                <div class="surface border border-[#232A34] bg-[#161B22] p-4 rounded-lg space-y-3">
                    <div class="flex items-center justify-between border-b border-[#232A34] pb-2">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-[#0FA3B1] flex items-center gap-1.5">
                            <i class="fa-solid fa-eye text-[#0FA3B1]"></i>
                            <span>Most Viewed Signals</span>
                        </span>
                        <span class="rounded bg-[#0FA3B115] text-[#53D2DE] text-[9px] font-mono px-1.5 py-0.5">Tracked</span>
                    </div>

                    <div v-if="props.mostViewedPosts?.length" class="space-y-3">
                        <div
                            v-for="(mvPost, idx) in props.mostViewedPosts"
                            :key="mvPost.id"
                            class="group block hover:bg-[#232A34]/40 p-2 rounded-lg transition"
                        >
                            <div class="flex items-start gap-2.5">
                                <span class="font-mono text-xs font-bold text-[#F5A623] shrink-0 mt-0.5">
                                    0{{ idx + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <a
                                        :href="postUrl(mvPost)"
                                        class="block font-semibold text-white text-xs hover:text-[#F5A623] transition line-clamp-2 leading-snug"
                                    >
                                        {{ mvPost.title || 'Engineering Architecture Signal' }}
                                    </a>
                                    <div class="mt-1 flex items-center gap-2 text-[10px] text-[#8B949E]">
                                        <span class="truncate">@{{ mvPost.user?.name ?? 'Developer' }}</span>
                                        <span>•</span>
                                        <span class="text-[#0FA3B1] font-mono flex items-center gap-1">
                                            <i class="fa-regular fa-eye text-[9px]"></i>
                                            {{ formatNumber(mvPost.views_count) }} views
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-[11px] text-[#8B949E] py-2 text-center">Loading popular signals...</p>
                </div>

                <!-- Trending / Hot Signals Widget -->
                <div class="surface border border-[#232A34] bg-[#161B22] p-4 rounded-lg space-y-3">
                    <div class="flex items-center justify-between border-b border-[#232A34] pb-2">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-[#F5A623] flex items-center gap-1.5">
                            <i class="fa-solid fa-fire text-[#F5A623]"></i>
                            <span>Trending Discussions</span>
                        </span>
                        <span class="rounded bg-[#F5A62315] text-[#F5A623] text-[9px] font-mono px-1.5 py-0.5">Viral</span>
                    </div>

                    <div v-if="props.trendingPosts?.length" class="space-y-2.5">
                        <div
                            v-for="trPost in props.trendingPosts"
                            :key="trPost.id"
                            class="hover:bg-[#232A34]/40 p-2 rounded-lg transition"
                        >
                            <a
                                :href="postUrl(trPost)"
                                class="font-semibold text-white text-xs hover:text-[#F5A623] transition line-clamp-1 block"
                            >
                                {{ trPost.title || 'Technical Discussion' }}
                            </a>
                            <div class="mt-1 flex items-center justify-between text-[10px] text-[#8B949E]">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-regular fa-heart text-[#FF6B6B]"></i> {{ trPost.likes_count ?? 0 }}
                                    <i class="fa-regular fa-comment text-[#F5A623] ml-1"></i> {{ trPost.comments_count ?? 0 }}
                                </span>
                                <span class="font-mono text-[#53D2DE]">{{ formatNumber(trPost.views_count) }} impressions</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Tech Contributors Widget -->
                <div v-if="props.topCreators?.length" class="surface border border-[#232A34] bg-[#161B22] p-4 rounded-lg space-y-3">
                    <div class="flex items-center justify-between border-b border-[#232A34] pb-2">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-[#0FA3B1] flex items-center gap-1.5">
                            <i class="fa-solid fa-code text-[#0FA3B1]"></i>
                            <span>Top Contributors</span>
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="creator in props.topCreators"
                            :key="creator.id"
                            class="flex items-center justify-between gap-2.5 hover:bg-[#232A34]/30 p-1.5 rounded transition"
                        >
                            <div class="flex items-center gap-2.5 min-w-0">
                                <a :href="userProfileUrl(creator)" class="h-8 w-8 rounded-full bg-[#0D1117] border border-[#232A34] flex items-center justify-center font-bold text-xs text-[#F5A623] overflow-hidden shrink-0">
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
                                    <a :href="userProfileUrl(creator)" class="text-xs font-bold text-white hover:text-[#F5A623] transition truncate block">
                                        {{ creator.name }}
                                    </a>
                                    <p class="text-[10px] text-[#8B949E] truncate font-mono">
                                        {{ creator.posts_count }} signals shared
                                    </p>
                                </div>
                            </div>
                            <a
                                :href="userProfileUrl(creator)"
                                class="shrink-0 rounded border border-[#232A34] px-2 py-1 text-[10px] font-bold text-[#53D2DE] hover:border-[#0FA3B1] hover:bg-[#0FA3B110] transition"
                            >
                                View
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Real-time Status Card -->
                <div class="surface border border-[#232A34] bg-[#161B22] p-4 rounded-lg space-y-2">
                    <div class="flex items-center gap-2 text-xs font-semibold text-white">
                        <i class="fa-solid fa-satellite-dish text-[#0FA3B1] animate-pulse"></i>
                        <span>Reverb Realtime Engine</span>
                    </div>
                    <p class="text-[11px] text-[#8B949E] leading-5">
                        Active stream: <code class="text-[#F5A623]">feed</code><br />
                        WebSockets: <span class="text-[#10B981] font-semibold">Connected & Synced</span>
                    </p>
                </div>

                <!-- Platform Footer Links -->
                <div class="px-2 text-[11px] text-[#8B949E] space-y-2">
                    <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <a href="/privacy-policy" class="hover:text-white transition">Privacy Policy</a>
                        <span>•</span>
                        <a href="/terms" class="hover:text-white transition">Terms of Service</a>
                        <span>•</span>
                        <a href="/profile" class="hover:text-white transition">Profile Settings</a>
                    </div>
                    <p class="text-[10px] font-mono text-[#484F58]">dev/community © 2026. All rights reserved.</p>
                </div>
            </aside>
        </main>

        <!-- Media Lightbox Modal -->
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