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
                        <i class="fa-solid fa-scale-balanced"></i>
                        <span>Platform Standards</span>
                    </div>
                    <h1 class="font-display mt-2 text-3xl sm:text-4xl font-bold text-white">Terms & Conditions</h1>
                    <p class="mt-2 text-xs text-[#8B949E] font-mono">Last updated: October 2026</p>
                </div>

                <section class="space-y-3 text-sm leading-7 text-[#C7CDD4]">
                    <h2 class="font-display text-xl font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-handshake text-[#F5A623] text-sm"></i>
                        1. Community Code of Conduct
                    </h2>
                    <p>
                        Dev Community is dedicated to civil, respectful, and insightful engineering discourse. Harassment, hateful speech, unsolicited commercial spam, or abusive behavior will result in swift account termination.
                    </p>
                </section>

                <section class="space-y-3 text-sm leading-7 text-[#C7CDD4]">
                    <h2 class="font-display text-xl font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-code text-[#0FA3B1] text-sm"></i>
                        2. Content Ownership & Code Licenses
                    </h2>
                    <p>
                        You retain full intellectual ownership of original articles, thoughts, and architectural insights posted to your account. By publishing publicly, you grant the platform a license to display and distribute this content to community members.
                    </p>
                    <p>
                        When sharing open-source code snippets, please ensure you respect respective licensing rights (MIT, Apache, GPL).
                    </p>
                </section>

                <section class="space-y-3 text-sm leading-7 text-[#C7CDD4]">
                    <h2 class="font-display text-xl font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-ban text-[#FF6B6B] text-sm"></i>
                        3. Moderation & Content Removal
                    </h2>
                    <p>
                        Our platform employs peer reporting and automated moderation tools. Posts containing malicious payload links, exploits, credentials, or defamatory assertions are subject to immediate removal.
                    </p>
                </section>

                <div class="border-t border-[#232A34] pt-6 flex items-center justify-between text-xs text-[#8B949E]">
                    <a href="/privacy-policy" class="hover:text-white transition">Read Privacy Policy →</a>
                    <a href="/" class="hover:text-white transition">Return to Home</a>
                </div>
            </article>
        </main>
    </div>
</template>
