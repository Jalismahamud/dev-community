<script setup>
import axios from 'axios';
import { marked } from 'marked';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import CommentThread from '../Components/CommentThread.vue';

const props = defineProps({ posts: Object, view: { type: String, default: 'all' } });
const activeView = ref(props.view);
const posts = ref(props.posts?.data ?? []);
const nextUrl = ref(props.posts?.links?.next ?? null);
const body = ref('');
const title = ref('');
const files = ref([]);
const loading = ref(false);
const newPosts = ref(false);
const lightbox = ref(null);

const views = [
    { key: 'all', label: 'All signal' },
    { key: 'following', label: 'Following' },
    { key: 'my-stack', label: 'My stack' },
    { key: 'trending', label: 'Trending 24h' },
];

const renderedBody = (value) => marked.parse(value ?? '');

function selectFiles(event) { files.value = [...event.target.files]; }

async function publish() {
    if (!body.value.trim()) return;
    const form = new FormData();
    form.append('title', title.value);
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

async function changeView(view) { activeView.value = view; await reload(); }
async function loadMore() { if (!nextUrl.value || loading.value) return; loading.value = true; const response = await axios.get(nextUrl.value, { headers: { Accept: 'application/json' } }); posts.value.push(...response.data.props.posts.data); nextUrl.value = response.data.props.posts.links.next; loading.value = false; }
async function likePost(post) { const response = await axios.post(`/posts/${post.id}/like`); post.likes_count = response.data.data.likes_count; }
async function likeComment(comment) { const response = await axios.post(`/comments/${comment.id}/like`); comment.likes_count = response.data.data.likes_count; }
async function addComment(post) { const value = window.prompt('Write a reply'); if (!value) return; await axios.post(`/posts/${post.id}/comments`, { body: value }); await reload(); }

function onFeedUpdate() { newPosts.value = true; }
function onPostLiked(event) { const post = posts.value.find((item) => item.id === event.post_id); if (post) post.likes_count = event.likes_count; }

onMounted(() => { window.Echo?.channel('feed').listen('.PostCreated', onFeedUpdate); });
onUnmounted(() => { window.Echo?.leave('feed'); });

const canLoadMore = computed(() => Boolean(nextUrl.value));
</script>

<template>
    <div class="min-h-screen bg-[#10141A] text-[#E6E6E6]">
        <header class="mx-auto flex max-w-6xl items-center justify-between px-5 py-6">
            <a href="/feed" class="font-display text-xl font-bold tracking-tight"><span class="text-[#F5A623]">dev</span>/community</a>
            <nav class="flex items-center gap-4 text-sm text-[#8B949E]"><a href="/notifications">notifications</a><form method="post" action="/logout"><button>logout</button></form></nav>
        </header>
        <main class="mx-auto grid max-w-6xl gap-6 px-5 pb-20 lg:grid-cols-[1fr_300px]">
            <section>
                <div class="mb-6 flex items-end justify-between"><div><p class="font-mono text-xs uppercase tracking-[.2em] text-[#0FA3B1]">public feed / live</p><h1 class="font-display mt-2 text-4xl font-bold">Build in public.</h1></div><span class="font-mono text-xs text-[#8B949E]">{{ posts.length }} loaded</span></div>
                <button v-if="newPosts" class="mb-4 w-full rounded-md border border-[#F5A623] bg-[#F5A6231a] px-4 py-3 text-left text-sm text-[#F5A623]" @click="newPosts = false; reload()">New posts ↑ · refresh signal</button>
                <div class="mb-6 flex flex-wrap gap-2 border-b border-[#232A34] pb-3"><button v-for="item in views" :key="item.key" class="rounded-md px-3 py-2 text-sm" :class="activeView === item.key ? 'bg-[#F5A623] text-[#10141A]' : 'text-[#8B949E] hover:text-[#E6E6E6]'" @click="changeView(item.key)">{{ item.label }}</button></div>
                <article class="surface mb-6 p-5"><div class="mb-3 font-mono text-xs text-[#8B949E]">// publish a thought</div><input v-model="title" class="amber-focus mb-3 w-full border-b border-[#232A34] bg-transparent py-2 font-display text-lg outline-none" placeholder="Optional title" /><textarea v-model="body" class="amber-focus min-h-32 w-full resize-y rounded-md border border-[#232A34] bg-[#10141A] p-3 text-sm outline-none" placeholder="Share code, a lesson, or a sharp question..."></textarea><div class="mt-3 flex items-center justify-between"><label class="cursor-pointer text-sm text-[#8B949E] hover:text-[#0FA3B1]"><input type="file" class="hidden" multiple accept="image/*" @change="selectFiles" />+ attach images <span v-if="files.length">({{ files.length }})</span></label><button class="rounded-md bg-[#F5A623] px-4 py-2 text-sm font-semibold text-[#10141A] hover:shadow-[0_0_18px_#F5A62355]" @click="publish">Publish →</button></div></article>
                <div class="space-y-4"><article v-for="post in posts" :key="post.id" class="surface p-5"><div class="mb-4 flex items-start justify-between"><div><span class="font-mono text-sm text-[#F5A623]">@{{ post.user?.name }}</span><time class="ml-3 text-xs text-[#8B949E]">{{ post.created_at }}</time></div><span class="font-mono text-xs text-[#8B949E]">#{{ post.id.toString().padStart(4, '0') }}</span></div><h2 v-if="post.title" class="font-display mb-2 text-xl font-semibold">{{ post.title }}</h2><div class="prose-community text-sm leading-7 text-[#C7CDD4]" v-html="renderedBody(post.body_markdown)"></div><div v-if="post.tags?.length" class="mt-4 flex flex-wrap gap-2"><span v-for="tag in post.tags" :key="tag.id" class="tag-chip px-2 py-1 text-xs">#{{ tag.name }}</span></div><div v-if="post.images?.length" class="mt-4 grid grid-cols-2 gap-2"><button v-for="image in post.images" :key="image.id" class="overflow-hidden rounded-md border border-[#232A34]" @click="lightbox = image"><img :src="`/storage/${image.path_webp}`" :alt="image.alt_text ?? ''" class="aspect-video w-full object-cover transition hover:scale-105" /></button></div><div v-if="post.comments?.length" class="mt-4 space-y-3 border-t border-[#232A34] pt-4"><CommentThread v-for="comment in post.comments.filter((item) => !item.parent_id)" :key="comment.id" :comment="comment" @like="likeComment" /></div><div class="mt-5 flex items-center gap-5 border-t border-[#232A34] pt-3 text-sm text-[#8B949E]"><button class="hover:text-[#FF6B6B]" @click="likePost(post)">♡ {{ post.likes_count }}</button><button class="hover:text-[#F5A623]" @click="addComment(post)">↳ {{ post.comments_count }}</button></div></article></div><button v-if="canLoadMore" class="surface mt-6 w-full px-4 py-3 text-sm text-[#0FA3B1]" @click="loadMore">{{ loading ? 'Loading...' : 'Load older signal ↓' }}</button>
            </section>
            <aside class="hidden lg:block"><div class="surface sticky top-6 p-5"><p class="font-mono text-xs uppercase tracking-[.2em] text-[#0FA3B1]">field notes</p><h2 class="font-display mt-3 text-2xl font-semibold">Keep it useful.</h2><p class="mt-3 text-sm leading-6 text-[#8B949E]">Share the sharp edge, the failed deploy, the tiny discovery. The community gets better when the trail stays visible.</p><div class="mt-6 border-t border-[#232A34] pt-4 font-mono text-xs text-[#8B949E]">reverb: <span class="text-[#53D2DE]">listening</span><br />feed channel: <span class="text-[#F5A623]">public</span></div></div></aside>
        </main>
        <div v-if="lightbox" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0D1117EE] p-6" @click.self="lightbox = null"><button class="absolute right-6 top-5 text-2xl text-[#E6E6E6]" @click="lightbox = null">×</button><img :src="`/storage/${lightbox.path_webp}`" :alt="lightbox.alt_text ?? ''" class="max-h-[85vh] max-w-full rounded-md object-contain" /></div>
    </div>
</template>