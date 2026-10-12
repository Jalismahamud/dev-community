<script setup>
import axios from 'axios';
import { marked } from 'marked';
import { ref } from 'vue';

const props = defineProps({
    profileUser: Object,
    posts: Object,
    isFollowing: Boolean,
    followersCount: Number,
    followingCount: Number,
    auth: Object,
});

const user = ref(props.profileUser?.data ?? props.profileUser);
const posts = ref(props.posts?.data ?? []);
const following = ref(props.isFollowing);
const followers = ref(props.followersCount);
const activeTab = ref('signals');

const renderedBio = (text) => marked.parse(text ?? '');
const mediaUrl = (path) => (!path ? '' : path.startsWith('http://') || path.startsWith('https://') ? path : `/storage/${path}`);
const postUrl = (p) => (!p ? '#' : `/posts/${p.slug || p.code || p.id}`);

function onAvatarError(e, name = 'User') {
    e.target.onerror = null;
    e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=161B22&color=F5A623&bold=true`;
}

async function toggleFollow() {
    const response = await axios.post(`/users/${user.value.id}/follow`);
    following.value = response.data.data.following;
    followers.value += following.value ? 1 : -1;
}

async function startChat() {
    const response = await axios.post(`/conversations/${user.value.id}`);
    window.location.href = `/chat?conversation=${response.data.data.id}`;
}

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const activityIntensity = [
    3, 1, 0, 2, 4, 1, 2, 0, 1, 3, 2, 4, 1, 2, 0, 3, 4, 2, 1, 0, 2, 3, 1, 4, 2, 3, 0, 1,
    2, 4, 1, 3, 0, 2, 1, 4, 3, 2, 1, 0, 3, 2, 4, 1, 0, 2, 3, 1, 4, 2, 0, 3, 1, 2, 4, 0,
    1, 2, 3, 4, 1, 0, 2, 3, 4, 1, 2, 0, 1, 3, 2, 4, 1, 2, 0, 3, 4, 2, 1, 0, 2, 3, 1, 4,
];
</script>

<template>
    <div class="min-h-screen bg-[#090C15] text-[#E2E8F0]">
        <header class="border-b border-[#1E253A] bg-[#0E1220]/90 backdrop-blur-md sticky top-0 z-40">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
                <a href="/feed" class="flex items-center gap-2.5 font-display text-lg font-bold tracking-tight text-white">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-tr from-cyan-400 via-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                        <i class="fa-solid fa-wifi text-sm rotate-45"></i>
                    </div>
                    <span class="text-white text-base tracking-wide">Dev<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">Connect</span></span>
                </a>
                <nav class="flex items-center gap-5 text-xs text-[#94A3B8]">
                    <a href="/feed" class="hover:text-white transition flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Feed</span>
                    </a>
                    <a href="/chat" class="hover:text-white transition flex items-center gap-1.5">
                        <i class="fa-solid fa-comments text-xs"></i>
                        <span>Chat</span>
                    </a>
                    <a href="/notifications" class="hover:text-white transition flex items-center gap-1.5">
                        <i class="fa-regular fa-bell text-xs"></i>
                        <span>Alerts</span>
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-10 pb-24 space-y-6">
            <section class="rounded-3xl border border-[#1E253A] bg-[#0E1220] p-6 sm:p-8 shadow-2xl relative overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-[#1E253A]">
                    <div class="flex items-center gap-5">
                        <div class="h-24 w-24 overflow-hidden rounded-full border-2 border-purple-500/50 bg-[#161B2E] flex items-center justify-center font-display text-3xl font-bold text-purple-300 shadow-xl shrink-0">
                            <img v-if="user.avatar_path" :src="mediaUrl(user.avatar_path)" :alt="user.name" class="h-full w-full object-cover" @error="(e) => onAvatarError(e, user.name)" />
                            <span v-else>{{ user.name?.charAt(0) }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="font-display text-2xl font-extrabold text-white">{{ user.name }}</h1>
                                <i class="fa-solid fa-circle-check text-sm text-cyan-400"></i>
                            </div>
                            <p class="text-xs text-[#64748B] mt-0.5">@{{ user.name?.toLowerCase().replace(/\s+/g, '') }}</p>
                            <p class="text-xs text-purple-300 font-medium mt-1">{{ user.current_status || 'Senior Full Stack Developer' }}</p>
                        </div>
                    </div>

                    <div v-if="props.auth?.user?.id !== user.id" class="flex items-center gap-2.5">
                        <button
                            class="rounded-xl px-5 py-2.5 text-xs font-semibold transition flex items-center gap-2 shadow-sm"
                            :class="following ? 'border border-[#1E253A] bg-[#141829] text-[#94A3B8] hover:border-rose-500/50 hover:text-rose-400' : 'bg-gradient-to-r from-cyan-400 to-blue-500 text-slate-950 font-bold shadow-cyan-500/20'"
                            @click="toggleFollow"
                        >
                            <i :class="following ? 'fa-solid fa-user-check' : 'fa-solid fa-user-plus'"></i>
                            <span>{{ following ? 'Following' : 'Follow' }}</span>
                        </button>
                        <button
                            class="rounded-xl border border-[#1E253A] bg-[#141829] px-4 py-2.5 text-xs font-semibold text-slate-200 hover:text-white hover:border-purple-500/50 transition flex items-center gap-2"
                            @click="startChat"
                        >
                            <i class="fa-regular fa-envelope text-xs text-cyan-400"></i>
                            <span>Message</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-6 border-b border-[#1E253A] text-center">
                    <div>
                        <span class="block text-xl font-extrabold text-white font-mono">{{ posts.length }}</span>
                        <span class="text-xs text-[#64748B] uppercase tracking-wider">Signal Posts</span>
                    </div>
                    <div>
                        <span class="block text-xl font-extrabold text-cyan-300 font-mono">{{ (posts.length * 45) + 320 }}</span>
                        <span class="text-xs text-[#64748B] uppercase tracking-wider">Profile Views</span>
                    </div>
                    <div>
                        <span class="block text-xl font-extrabold text-purple-300 font-mono">{{ followers }}</span>
                        <span class="text-xs text-[#64748B] uppercase tracking-wider">Followers</span>
                    </div>
                    <div>
                        <span class="block text-xl font-extrabold text-emerald-400 font-mono">{{ 94 + (posts.length * 2) }}</span>
                        <span class="text-xs text-[#64748B] uppercase tracking-wider">Reputation Score</span>
                    </div>
                </div>

                <div class="py-5 border-b border-[#1E253A] space-y-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Tech Stack</h3>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="tag in (user.tech_tags?.length ? user.tech_tags : [{ id: 1, name: 'PHP' }, { id: 2, name: 'Laravel 11' }, { id: 3, name: 'Vue 3' }, { id: 4, name: 'Tailwind' }, { id: 5, name: 'MySQL' }, { id: 6, name: 'Inertia.js' }, { id: 7, name: 'Redis' }])"
                            :key="tag.id"
                            class="rounded-xl border border-[#222B48] bg-[#141829] px-3.5 py-1.5 font-mono text-xs text-[#94A3B8]"
                        >
                            {{ tag.name }}
                        </span>
                    </div>
                </div>

                <div class="pt-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Activity Graph</h3>
                        <div class="flex items-center gap-1.5 text-[11px] text-[#64748B]">
                            <span>Less</span>
                            <span class="h-2.5 w-2.5 rounded-sm bg-[#161B2E]"></span>
                            <span class="h-2.5 w-2.5 rounded-sm bg-emerald-950 border border-emerald-800"></span>
                            <span class="h-2.5 w-2.5 rounded-sm bg-emerald-700"></span>
                            <span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span>
                            <span class="h-2.5 w-2.5 rounded-sm bg-emerald-300"></span>
                            <span>More</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-[#1E253A] bg-[#090C16] p-4 overflow-x-auto">
                        <div class="flex justify-between text-[10px] text-[#64748B] mb-2 px-1 font-mono">
                            <span v-for="m in months" :key="m">{{ m }}</span>
                        </div>
                        <div class="grid grid-flow-col grid-rows-4 gap-1.5 min-w-[500px]">
                            <div
                                v-for="(level, idx) in activityIntensity"
                                :key="idx"
                                :class="[
                                    'h-3.5 w-3.5 rounded-sm transition',
                                    level === 0 ? 'bg-[#141829]' :
                                    level === 1 ? 'bg-emerald-950 border border-emerald-900/40' :
                                    level === 2 ? 'bg-emerald-800' :
                                    level === 3 ? 'bg-emerald-600' : 'bg-emerald-400'
                                ]"
                            ></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="space-y-5">
                <div class="flex items-center gap-3 border-b border-[#1E253A] pb-3 text-xs font-semibold">
                    <button
                        class="px-4 py-2 rounded-xl transition flex items-center gap-2"
                        :class="activeTab === 'signals' ? 'bg-purple-600/15 border border-purple-500/70 text-white' : 'text-[#64748B] hover:text-white'"
                        @click="activeTab = 'signals'"
                    >
                        <i class="fa-solid fa-layer-group text-xs text-purple-400"></i>
                        <span>Published Signals</span>
                    </button>
                    <button
                        class="px-4 py-2 rounded-xl transition flex items-center gap-2"
                        :class="activeTab === 'bookmarks' ? 'bg-purple-600/15 border border-purple-500/70 text-white' : 'text-[#64748B] hover:text-white'"
                        @click="activeTab = 'bookmarks'"
                    >
                        <i class="fa-regular fa-bookmark text-xs text-cyan-400"></i>
                        <span>Bookmarks</span>
                    </button>
                    <button
                        class="px-4 py-2 rounded-xl transition flex items-center gap-2"
                        :class="activeTab === 'activity' ? 'bg-purple-600/15 border border-purple-500/70 text-white' : 'text-[#64748B] hover:text-white'"
                        @click="activeTab = 'activity'"
                    >
                        <i class="fa-solid fa-list-check text-xs text-emerald-400"></i>
                        <span>Activity Feed</span>
                    </button>
                </div>

                <div v-if="posts.length" class="space-y-4">
                    <article v-for="post in posts" :key="post.id" class="rounded-2xl border border-[#1E253A] bg-[#0E1220] p-6 hover:border-purple-500/40 transition shadow-sm">
                        <div class="mb-3 flex items-center justify-between text-xs text-[#64748B]">
                            <time>{{ post.created_at }}</time>
                            <a :href="postUrl(post)" class="hover:text-purple-300 transition flex items-center gap-1 text-cyan-400 font-mono">
                                <span>Read signal</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                        <h3 v-if="post.title" class="font-display text-lg font-bold text-white mb-2">
                            <a :href="postUrl(post)" class="hover:text-purple-300 transition">{{ post.title }}</a>
                        </h3>
                        <p class="text-sm line-clamp-3 text-[#94A3B8] leading-relaxed">{{ post.body_markdown }}</p>
                        <div class="mt-4 flex items-center gap-6 text-xs text-[#64748B]">
                            <span class="flex items-center gap-1.5"><i class="fa-regular fa-heart text-rose-400"></i> {{ post.likes_count }}</span>
                            <span class="flex items-center gap-1.5"><i class="fa-regular fa-comment text-purple-400"></i> {{ post.comments_count }}</span>
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-share-nodes text-cyan-400"></i> {{ post.shares_count ?? 0 }}</span>
                            <span class="flex items-center gap-1.5 font-mono ml-auto"><i class="fa-regular fa-eye text-cyan-400"></i> {{ post.views_count ?? 0 }} views</span>
                        </div>
                    </article>
                </div>
                <div v-else class="rounded-2xl border border-[#1E253A] bg-[#0E1220] p-8 text-center text-xs text-[#64748B]">
                    No signals published yet.
                </div>
            </section>
        </main>
    </div>
</template>
