<script setup>
</script>

<template>
    <div class="min-h-screen bg-[#0D1117] text-[#E6E6E6]">
        <header class="border-b border-[#232A34] bg-[#161B22]/80 backdrop-blur sticky top-0 z-40">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-4">
                <a href="/" class="flex items-center gap-2 font-display text-xl font-bold tracking-tight">
                    <i class="fa-solid fa-terminal text-[#F5A623]"></i>
                    <span><span class="text-[#F5A623]">dev</span>/community</span>
                </a>
                <div class="flex items-center gap-4 text-xs font-medium text-[#8B949E]">
                    <a href="/login" class="hover:text-white transition">Sign in</a>
                    <a href="/register" class="rounded-md bg-[#F5A623] px-3 py-1.5 font-semibold text-[#10141A] hover:shadow-[0_0_12px_#F5A62344] transition">
                        Join Community
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-5 py-12">
            <article class="surface border border-[#232A34] bg-[#161B22] p-8 sm:p-12 space-y-8">
                <div>
                    <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-[#0FA3B1]">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Legal Documentation</span>
                    </div>
                    <h1 class="font-display mt-2 text-3xl sm:text-4xl font-bold text-white">Privacy Policy</h1>
                    <p class="mt-2 text-xs text-[#8B949E] font-mono">Last updated: October 2026</p>
                </div>

                <section class="space-y-3 text-sm leading-7 text-[#C7CDD4]">
                    <h2 class="font-display text-xl font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-database text-[#F5A623] text-sm"></i>
                        1. Information We Collect
                    </h2>
                    <p>
                        We collect information necessary to provide developer collaboration features, including your name, email address, profile avatar, technical skills, biography, and social profiles (GitHub, LinkedIn).
                    </p>
                    <p>
                        When you publish content, we store markdown texts, code snippets, compressed WebP media files, and interaction metadata (likes, shares, comments, bookmarks).
                    </p>
                </section>

                <section class="space-y-3 text-sm leading-7 text-[#C7CDD4]">
                    <h2 class="font-display text-xl font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-server text-[#0FA3B1] text-sm"></i>
                        2. How Your Data Is Stored & Protected
                    </h2>
                    <p>
                        Your media uploads are processed using server-side compression and WebP standards to minimize bandwidth while retaining crisp visual fidelity. Passwords are cryptographically hashed using standard Bcrypt rounds.
                    </p>
                    <p>
                        We do not sell, rent, or trade your personal information to third-party advertising networks.
                    </p>
                </section>

                <section class="space-y-3 text-sm leading-7 text-[#C7CDD4]">
                    <h2 class="font-display text-xl font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-user-shield text-[#53D2DE] text-sm"></i>
                        3. Community Data & Public Contributions
                    </h2>
                    <p>
                        Content marked for public feed distribution, including code contributions and discussion threads, is indexed and visible to registered and visiting developers across the platform.
                    </p>
                </section>

                <section class="space-y-3 text-sm leading-7 text-[#C7CDD4]">
                    <h2 class="font-display text-xl font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-envelope text-[#F5A623] text-sm"></i>
                        4. Contact & Inquiries
                    </h2>
                    <p>
                        If you have inquiries regarding privacy practices, account deletion, or data exports, contact the community administration team directly.
                    </p>
                </section>

                <div class="border-t border-[#232A34] pt-6 flex items-center justify-between text-xs text-[#8B949E]">
                    <a href="/terms" class="hover:text-white transition">Read Terms & Conditions →</a>
                    <a href="/" class="hover:text-white transition">Return to Home</a>
                </div>
            </article>
        </main>
    </div>
</template>
