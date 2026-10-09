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
</script>

<template>
    <div class="min-h-screen bg-[#0D1117] text-[#E6E6E6]">
        <header class="border-b border-[#232A34] bg-[#161B22]/90 backdrop-blur sticky top-0 z-40">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-3">
                <a href="/feed" class="flex items-center gap-2 font-display text-lg font-bold tracking-tight">
                    <i class="fa-solid fa-terminal text-[#F5A623]"></i>
                    <span><span class="text-[#F5A623]">dev</span>/community</span>
                </a>
                <nav class="flex items-center gap-4 text-xs text-[#8B949E]">
                    <a href="/feed" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Feed</span>
                    </a>
                    <a href="/chat" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fa-solid fa-comments text-xs"></i>
                        <span>Chat</span>
                    </a>
                    <a href="/notifications" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fa-solid fa-bell text-xs"></i>
                        <span>Alerts</span>
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-5 py-8 pb-20 space-y-6">
            <section class="surface border border-[#232A34] bg-[#161B22] p-6 sm:p-8 rounded-xl shadow-lg">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="h-20 w-20 overflow-hidden rounded-full border-2 border-[#F5A623] bg-[#0D1117] flex items-center justify-center font-display text-2xl font-bold text-[#F5A623]">
                            <img v-if="user.avatar_path" :src="mediaUrl(user.avatar_path)" :alt="user.name" class="h-full w-full object-cover" @error="(e) => onAvatarError(e, user.name)" />
                            <span v-else>{{ user.name?.charAt(0) }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="font-display text-2xl font-bold text-white">{{ user.name }}</h1>
                                <i class="fa-solid fa-circle-check text-sm text-[#0FA3B1]"></i>
                            </div>
                            <p v-if="user.current_status" class="text-xs text-[#0FA3B1] font-mono mt-1">// {{ user.current_status }}</p>
                            <div class="mt-2 flex items-center gap-4 text-xs text-[#8B949E]">
                                <span><strong class="text-white">{{ followers }}</strong> followers</span>
                                <span>•</span>
                                <span><strong class="text-white">{{ props.followingCount }}</strong> following</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="props.auth?.user?.id !== user.id" class="flex items-center gap-2">
                        <button
                            class="rounded-md border border-[#0FA3B1] px-4 py-2 text-xs font-bold text-[#53D2DE] hover:bg-[#0FA3B120] transition flex items-center gap-1.5"
                            @click="startChat"
                        >
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Message</span>
                        </button>
                        <button
                            class="rounded-md px-4 py-2 text-xs font-bold transition flex items-center gap-1.5"
                            :class="following ? 'border border-[#232A34] text-[#8B949E] hover:border-[#FF6B6B] hover:text-[#FF6B6B]' : 'bg-[#F5A623] text-[#10141A] hover:shadow-[0_0_15px_#F5A62355]'"
                            @click="toggleFollow"
                        >
                            <i :class="following ? 'fa-solid fa-user-check' : 'fa-solid fa-user-plus'"></i>
                            <span>{{ following ? 'Following' : 'Follow +' }}</span>
                        </button>
                    </div>
                </div>

                <div v-if="user.bio" class="mt-6 border-t border-[#232A34] pt-4 text-sm leading-6 text-[#C7CDD4]" v-html="renderedBio(user.bio)"></div>

                <div class="mt-6 flex flex-wrap items-center gap-4 text-xs font-mono text-[#8B949E]">
                    <a v-if="user.github_url" :href="user.github_url" target="_blank" rel="noopener noreferrer" class="hover:text-[#53D2DE] flex items-center gap-1.5 transition">
                        <i class="fa-brands fa-github text-sm"></i>
                        <span>GitHub</span>
                    </a>
                    <a v-if="user.linkedin_url" :href="user.linkedin_url" target="_blank" rel="noopener noreferrer" class="hover:text-[#53D2DE] flex items-center gap-1.5 transition">
                        <i class="fa-brands fa-linkedin text-sm"></i>
                        <span>LinkedIn</span>
                    </a>
                </div>

                <div v-if="user.tech_tags?.length" class="mt-6 flex flex-wrap gap-2">
                    <span v-for="tag in user.tech_tags" :key="tag.id" class="rounded-full border border-[#0FA3B144] bg-[#0FA3B110] px-3 py-1 font-mono text-xs text-[#53D2DE]">
                        #{{ tag.name }}
                    </span>
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="font-display text-xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-newspaper text-[#F5A623] text-base"></i>
                    <span>Engineering Signals by {{ user.name }}</span>
                </h2>

                <div v-if="posts.length" class="space-y-4">
                    <article v-for="post in posts" :key="post.id" class="surface border border-[#232A34] bg-[#161B22] p-5 rounded-xl">
                        <div class="mb-3 flex items-center justify-between text-xs text-[#8B949E]">
                            <time>{{ post.created_at }}</time>
                            <a :href="postUrl(post)" class="hover:text-[#F5A623] transition flex items-center gap-1">
                                <span>View post</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                        <h3 v-if="post.title" class="font-display text-lg font-bold text-white mb-2">
                            <a :href="postUrl(post)" class="hover:text-[#F5A623] transition">{{ post.title }}</a>
                        </h3>
                        <p class="text-sm line-clamp-3 text-[#8B949E]">{{ post.body_markdown }}</p>
                        <div class="mt-4 flex items-center gap-5 text-xs text-[#8B949E]">
                            <span class="flex items-center gap-1.5"><i class="fa-regular fa-heart"></i> {{ post.likes_count }}</span>
                            <span class="flex items-center gap-1.5"><i class="fa-regular fa-comment"></i> {{ post.comments_count }}</span>
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-share-nodes"></i> {{ post.shares_count ?? 0 }}</span>
                        </div>
                    </article>
                </div>
                <div v-else class="surface border border-[#232A34] bg-[#161B22] p-8 text-center text-xs text-[#8B949E] rounded-xl">
                    No signals shared yet.
                </div>
            </section>
        </main>
    </div>
</template>
