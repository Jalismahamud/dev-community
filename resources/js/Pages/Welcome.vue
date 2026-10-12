<script setup>
defineProps({
    appName: {
        type: String,
        default: 'DevConnect',
    },
});

const heroCards = [
    {
        icon: 'fa-solid fa-bolt',
        title: 'Real-Time WebSocket Sync',
        desc: 'Instant message delivery, live code updates, and activity feeds across the platform.',
        accent: 'from-cyan-500/20 to-blue-500/10',
        border: 'border-cyan-500/40 hover:border-cyan-400',
        iconColor: 'text-cyan-400',
    },
    {
        icon: 'fa-solid fa-code',
        title: 'Markdown Code Engine',
        desc: 'Beautifully rendered code snippets with syntax highlighting, line numbers, and fast formatting.',
        accent: 'from-purple-500/20 to-indigo-500/10',
        border: 'border-purple-500/40 hover:border-purple-400',
        iconColor: 'text-purple-400',
    },
    {
        icon: 'fa-solid fa-comments',
        title: 'Direct Peer Messaging & Sharing',
        desc: 'Secure DMs, inline image previews, lightbox views, and full file attachments between peers.',
        accent: 'from-teal-500/20 to-emerald-500/10',
        border: 'border-teal-500/40 hover:border-teal-400',
        iconColor: 'text-teal-400',
    },
    {
        icon: 'fa-solid fa-chart-line',
        title: 'Smart Metric & Impression Analytics',
        desc: 'Track code engagement, feed performance, view counts, and connections with detailed metrics.',
        accent: 'from-indigo-500/20 to-violet-500/10',
        border: 'border-indigo-500/40 hover:border-indigo-400',
        iconColor: 'text-indigo-400',
    },
];

const techPills = ['Laravel 12', 'Vue 3', 'Inertia.js', 'WebSockets', 'Pusher', 'TailwindCSS', 'WebP Storage', 'MySQL', 'Docker'];
</script>

<template>
    <div class="min-h-screen bg-[#07090F] text-[#E2E8F0] flex flex-col justify-between selection:bg-purple-500 selection:text-white relative overflow-hidden">
        <div class="absolute top-0 left-1/4 h-[500px] w-[500px] bg-purple-600/10 blur-[130px] rounded-full pointer-events-none"></div>
        <div class="absolute top-20 right-1/4 h-[400px] w-[400px] bg-cyan-500/10 blur-[130px] rounded-full pointer-events-none"></div>

        <header class="border-b border-[#1A2035] bg-[#0C101D]/80 backdrop-blur-md sticky top-0 z-50">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <a href="/" class="flex items-center gap-3 font-display text-xl font-extrabold tracking-tight">
                    <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-cyan-400 via-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                        <i class="fa-solid fa-wifi text-base rotate-45"></i>
                    </div>
                    <span class="text-white text-lg tracking-wide">Dev<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">Connect</span></span>
                </a>

                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-[#94A3B8]">
                    <a href="/feed" class="hover:text-white transition">Feed</a>
                    <a href="/feed?view=trending" class="hover:text-white transition">Discover</a>
                    <a href="/feed?view=most-viewed" class="hover:text-white transition">Most Viewed</a>
                    <a href="/feed?view=today" class="hover:text-white transition">Today</a>
                </nav>

                <div class="flex items-center gap-3">
                    <a href="/login" class="px-4 py-2 text-xs font-semibold text-[#94A3B8] hover:text-white transition">
                        Sign In
                    </a>
                    <a href="/register" class="rounded-full bg-gradient-to-r from-cyan-400 to-blue-600 px-5 py-2 text-xs font-bold text-slate-950 transition shadow-lg shadow-cyan-500/20 hover:shadow-cyan-400/40 hover:scale-105">
                        Join Community
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-16 sm:py-24 space-y-20 relative z-10">
            <section class="text-center space-y-6 max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 rounded-full border border-purple-500/30 bg-purple-500/10 px-4 py-1.5 text-xs font-mono text-purple-300">
                    <span class="h-2 w-2 rounded-full bg-cyan-400 animate-pulse"></span>
                    <span>NEXT-GEN REAL-TIME DEVELOPER ECOSYSTEM</span>
                </div>

                <h1 class="font-display text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white tracking-tight leading-[1.1]">
                    Where Developers Connect,<br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 via-indigo-300 to-purple-400">
                        Share Signals & Collaborate
                    </span>
                </h1>

                <p class="text-base sm:text-lg text-[#94A3B8] max-w-2xl mx-auto leading-relaxed">
                    Access real-time code signals, insights, nested technical discussions, and direct peer-to-peer messaging with rich media on the ultimate developer platform.
                </p>

                <div class="pt-4 flex flex-wrap items-center justify-center gap-4">
                    <a href="/register" class="rounded-full bg-gradient-to-r from-cyan-400 via-teal-400 to-blue-500 px-7 py-3 text-sm font-bold text-slate-950 shadow-xl shadow-cyan-500/25 hover:shadow-cyan-400/50 hover:scale-105 transition">
                        <span>Join Community</span>
                        <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                    </a>
                    <a href="/feed" class="rounded-full border border-[#2A344E] bg-[#121626]/80 px-7 py-3 text-sm font-semibold text-[#E2E8F0] hover:border-purple-500/60 hover:text-white transition backdrop-blur">
                        <i class="fa-solid fa-compass mr-2 text-cyan-400"></i>
                        <span>Explore Feed</span>
                    </a>
                </div>

                <div class="pt-6 flex flex-wrap items-center justify-center gap-2">
                    <span v-for="pill in techPills" :key="pill" class="rounded-full border border-[#1E263D] bg-[#111524]/60 px-3.5 py-1 font-mono text-xs text-[#818CF8]">
                        #{{ pill }}
                    </span>
                </div>
            </section>

            <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="card in heroCards"
                    :key="card.title"
                    :class="['rounded-2xl border p-6 bg-gradient-to-b from-[#13182C] to-[#0D101E] backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:shadow-2xl', card.border]"
                >
                    <div :class="['h-12 w-12 rounded-xl border border-white/10 flex items-center justify-center text-xl mb-5 bg-[#0B0E1A]', card.iconColor]">
                        <i :class="card.icon"></i>
                    </div>
                    <h3 class="font-display text-base font-bold text-white mb-2.5">{{ card.title }}</h3>
                    <p class="text-xs leading-relaxed text-[#94A3B8]">{{ card.desc }}</p>
                </div>
            </section>

            <section class="rounded-3xl border border-purple-500/30 bg-gradient-to-r from-purple-950/30 via-[#101426] to-cyan-950/30 p-8 sm:p-14 text-center space-y-6 relative overflow-hidden shadow-2xl">
                <div class="max-w-2xl mx-auto space-y-4">
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-white">
                        Build your engineering identity today
                    </h2>
                    <p class="text-sm text-[#94A3B8] leading-relaxed">
                        Publish your technical breakthroughs, share live syntax-highlighted code snippets, and interact directly with senior software engineers.
                    </p>
                    <div class="pt-4 flex flex-wrap justify-center gap-3">
                        <a href="/register" class="rounded-full bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-purple-500/30 hover:scale-105 transition">
                            <i class="fa-solid fa-code mr-2 text-xs"></i>
                            <span>Create Free Profile</span>
                        </a>
                        <a href="/login" class="rounded-full border border-[#28324E] bg-[#111524] px-6 py-3 text-sm font-semibold text-[#CBD5E1] hover:text-white transition">
                            Sign In to Account
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-[#192033] bg-[#0A0D17] py-8 text-xs text-[#64748B]">
            <div class="mx-auto flex max-w-7xl flex-col sm:flex-row items-center justify-between gap-4 px-6">
                <div class="flex items-center gap-3">
                    <div class="h-6 w-6 rounded-lg bg-gradient-to-tr from-cyan-400 to-purple-600 flex items-center justify-center text-white text-[10px]">
                        <i class="fa-solid fa-wifi rotate-45"></i>
                    </div>
                    <span class="font-bold text-white tracking-wide">Dev<span class="text-cyan-400">Connect</span></span>
                    <span>— Designed for modern software engineers</span>
                </div>

                <div class="flex items-center gap-6">
                    <a href="/privacy-policy" class="hover:text-white transition">Privacy Policy</a>
                    <a href="/terms" class="hover:text-white transition">Terms of Service</a>
                    <a href="/feed" class="hover:text-white transition">Feed</a>
                    <a href="/chat" class="hover:text-white transition">Chat Hub</a>
                </div>
            </div>
        </footer>
    </div>
</template>