<script setup>
import axios from 'axios';
import { ref, computed } from 'vue';

const props = defineProps({
    notifications: Object,
});

const list = ref(props.notifications?.data ?? []);
const searchQuery = ref('');
const activeFilter = ref('all');

async function markAsRead(item) {
    if (item.read_at) return;
    await axios.post(`/notifications/${item.id}/read`);
    item.read_at = new Date().toISOString();
}

async function markAllAsRead() {
    for (const item of list.value) {
        if (!item.read_at) {
            markAsRead(item);
        }
    }
}

const unreadCount = computed(() => list.value.filter((i) => !i.read_at).length);

const filteredList = computed(() => {
    let result = list.value;
    if (activeFilter.value === 'unread') {
        result = result.filter((i) => !i.read_at);
    }
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter((i) => (i.data?.message || '').toLowerCase().includes(q));
    }
    return result;
});
</script>

<template>
    <div class="min-h-screen bg-[#090C15] text-[#E2E8F0]">
        <header class="border-b border-[#1E253A] bg-[#0E1220]/90 backdrop-blur-md sticky top-0 z-40">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-3">
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
                    <a href="/profile" class="hover:text-white transition flex items-center gap-1.5">
                        <i class="fa-solid fa-gear text-xs"></i>
                        <span>Settings</span>
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-6 py-10 pb-24">
            <div class="rounded-3xl border border-[#1E253A] bg-[#0E1220] p-6 sm:p-8 shadow-2xl space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1E253A] pb-6">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-2xl bg-purple-500/20 border border-purple-500/40 flex items-center justify-center text-purple-400 text-lg shadow-lg shadow-purple-500/10">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <h1 class="font-display text-lg sm:text-xl font-extrabold tracking-wide text-white uppercase">
                                Notification Center
                            </h1>
                            <p class="text-xs text-[#64748B]">Real-time technical alerts & mentions</p>
                        </div>
                    </div>

                    <button
                        @click="markAllAsRead"
                        class="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-5 py-2 text-xs font-semibold text-white shadow-md shadow-purple-500/20 hover:scale-105 transition flex items-center gap-2 self-start sm:self-auto"
                    >
                        <i class="fa-solid fa-check-double text-xs"></i>
                        <span>Mark All as Read</span>
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-xs text-[#64748B]"></i>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search notifications..."
                            class="w-full rounded-xl border border-[#1E253A] bg-[#121627] py-2 pl-9 pr-3 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 transition"
                        />
                    </div>

                    <div class="flex items-center gap-2 text-xs font-semibold">
                        <button
                            class="px-3 py-1.5 rounded-xl transition"
                            :class="activeFilter === 'all' ? 'bg-[#1C2038] text-white border border-purple-500/60' : 'text-[#64748B] hover:text-white'"
                            @click="activeFilter = 'all'"
                        >
                            All ({{ list.length }})
                        </button>
                        <button
                            class="px-3 py-1.5 rounded-xl transition"
                            :class="activeFilter === 'unread' ? 'bg-[#1C2038] text-white border border-purple-500/60' : 'text-[#64748B] hover:text-white'"
                            @click="activeFilter = 'unread'"
                        >
                            Unread ({{ unreadCount }})
                        </button>
                    </div>
                </div>

                <div v-if="filteredList.length" class="space-y-3.5 pt-2">
                    <div
                        v-for="item in filteredList"
                        :key="item.id"
                        class="rounded-2xl border p-4 sm:p-5 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                        :class="
                            !item.read_at
                                ? 'border-purple-500/50 bg-[#141829] shadow-md shadow-purple-500/5'
                                : 'border-[#1E253A] bg-[#0A0D17]/80 opacity-75'
                        "
                    >
                        <div class="flex items-start gap-4">
                            <div class="h-10 w-10 rounded-full bg-[#181D33] border border-purple-500/30 flex items-center justify-center font-bold text-xs text-purple-300 overflow-hidden shrink-0 mt-0.5">
                                <i class="fa-solid fa-code text-cyan-400"></i>
                            </div>

                            <div class="space-y-1">
                                <p class="text-xs sm:text-sm text-white font-medium leading-relaxed">
                                    {{ item.data?.message ?? 'New technical reaction on your published signal' }}
                                </p>
                                <time class="font-mono text-[10px] text-[#64748B] block">{{ item.created_at }}</time>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                            <a
                                href="/feed"
                                class="rounded-xl border border-[#1E253A] bg-[#121627] px-3 py-1.5 text-xs text-slate-300 hover:text-white hover:border-purple-500/40 transition"
                            >
                                View
                            </a>
                            <button
                                v-if="!item.read_at"
                                class="rounded-xl bg-purple-500/20 border border-purple-500/40 px-3 py-1.5 text-xs font-semibold text-purple-300 hover:bg-purple-500/30 transition flex items-center gap-1.5"
                                @click="markAsRead(item)"
                            >
                                <i class="fa-solid fa-check text-[10px]"></i>
                                <span>Mark Read</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-2xl border border-[#1E253A] bg-[#0A0D17] p-12 text-center text-xs text-[#64748B] space-y-2">
                    <i class="fa-solid fa-bell-slash text-2xl text-[#1E253A] block"></i>
                    <p class="text-slate-400 font-medium">All caught up! No notifications to display.</p>
                </div>
            </div>
        </main>
    </div>
</template>
