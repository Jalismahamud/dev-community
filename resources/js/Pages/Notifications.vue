<script setup>
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
    notifications: Object,
});

const list = ref(props.notifications?.data ?? []);

async function markAsRead(item) {
    if (item.read_at) return;
    await axios.post(`/notifications/${item.id}/read`);
    item.read_at = new Date().toISOString();
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
                    <a href="/chat" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fa-solid fa-comments text-xs"></i>
                        <span>Chat</span>
                    </a>
                    <a href="/profile" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fa-solid fa-gear text-xs"></i>
                        <span>Settings</span>
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-5 py-8 pb-20">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-[#0FA3B1]">
                        <i class="fa-solid fa-satellite-dish"></i>
                        <span>Activity Feed</span>
                    </div>
                    <h1 class="font-display mt-2 text-2xl sm:text-3xl font-bold text-white">Notifications</h1>
                </div>
            </div>

            <div v-if="list.length" class="space-y-3">
                <div
                    v-for="item in list"
                    :key="item.id"
                    class="surface border border-[#232A34] bg-[#161B22] flex items-center justify-between p-4 rounded-lg transition"
                    :class="!item.read_at ? 'border-l-4 border-l-[#F5A623]' : 'opacity-70'"
                >
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 text-[#F5A623]">
                            <i class="fa-solid fa-bell text-sm"></i>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs sm:text-sm text-white">
                                {{ item.data?.message ?? 'New activity on your content' }}
                            </p>
                            <time class="font-mono text-[11px] text-[#8B949E] block">{{ item.created_at }}</time>
                        </div>
                    </div>

                    <button v-if="!item.read_at" class="text-xs font-semibold text-[#F5A623] hover:underline shrink-0 ml-4 flex items-center gap-1" @click="markAsRead(item)">
                        <i class="fa-solid fa-check text-[10px]"></i>
                        <span>Mark read</span>
                    </button>
                    <span v-else class="text-[11px] text-[#8B949E] shrink-0 ml-4 flex items-center gap-1">
                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                        <span>Read</span>
                    </span>
                </div>
            </div>

            <div v-else class="surface border border-[#232A34] bg-[#161B22] p-12 text-center text-xs text-[#8B949E] rounded-xl space-y-2">
                <i class="fa-solid fa-bell-slash text-2xl text-[#232A34]"></i>
                <p>All caught up! No new notifications.</p>
            </div>
        </main>
    </div>
</template>
