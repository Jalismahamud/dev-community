<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register');
}
</script>

<template>
    <div class="flex min-h-screen flex-col justify-between bg-[#0D1117] px-5 py-10 text-[#E6E6E6]">
        <header class="mx-auto flex w-full max-w-md items-center justify-between">
            <a href="/" class="flex items-center gap-2 font-display text-xl font-bold tracking-tight">
                <i class="fa-solid fa-terminal text-[#F5A623]"></i>
                <span><span class="text-[#F5A623]">dev</span>/community</span>
            </a>
            <a href="/login" class="text-xs font-semibold text-[#8B949E] hover:text-white transition">
                Sign in →
            </a>
        </header>

        <main class="mx-auto my-auto w-full max-w-md">
            <div class="surface border border-[#232A34] bg-[#161B22] p-8 shadow-2xl">
                <div class="mb-6">
                    <p class="font-mono text-xs uppercase tracking-[.2em] text-[#0FA3B1]">Join the network</p>
                    <h1 class="font-display mt-2 text-2xl font-bold text-white">Create developer account</h1>
                    <p class="mt-1 text-xs text-[#8B949E]">Showcase projects, share insights, and connect.</p>
                </div>

                <div v-if="Object.keys(form.errors).length" class="mb-4 rounded-md border border-[#FF6B6B33] bg-[#FF6B6B15] p-3 text-xs text-[#FF6B6B] space-y-1">
                    <div v-for="(err, key) in form.errors" :key="key" class="flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>{{ err }}</span>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-[#8B949E] mb-1.5">Full name</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#8B949E]">
                                <i class="fa-solid fa-user text-xs"></i>
                            </span>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                autocomplete="name"
                                placeholder="Linus Torvalds"
                                class="amber-focus w-full rounded-md border border-[#232A34] bg-[#0D1117] py-2.5 pl-10 pr-3 text-sm text-white placeholder-[#484F58] outline-none transition"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#8B949E] mb-1.5">Email address</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#8B949E]">
                                <i class="fa-solid fa-envelope text-xs"></i>
                            </span>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="email"
                                placeholder="developer@domain.com"
                                class="amber-focus w-full rounded-md border border-[#232A34] bg-[#0D1117] py-2.5 pl-10 pr-3 text-sm text-white placeholder-[#484F58] outline-none transition"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#8B949E] mb-1.5">Password</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#8B949E]">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </span>
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                placeholder="At least 8 characters"
                                class="amber-focus w-full rounded-md border border-[#232A34] bg-[#0D1117] py-2.5 pl-10 pr-10 text-sm text-white placeholder-[#484F58] outline-none transition"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-[#8B949E] hover:text-white"
                                @click="showPassword = !showPassword"
                            >
                                <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-[#8B949E] mb-1.5">Confirm password</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#8B949E]">
                                <i class="fa-solid fa-shield-halved text-xs"></i>
                            </span>
                            <input
                                v-model="form.password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                placeholder="Re-enter password"
                                class="amber-focus w-full rounded-md border border-[#232A34] bg-[#0D1117] py-2.5 pl-10 pr-3 text-sm text-white placeholder-[#484F58] outline-none transition"
                            />
                        </div>
                    </div>

                    <p class="text-[11px] leading-5 text-[#8B949E] pt-1">
                        By registering, you agree to our
                        <a href="/terms" class="text-[#0FA3B1] hover:underline">Terms of Service</a>
                        and
                        <a href="/privacy-policy" class="text-[#0FA3B1] hover:underline">Privacy Policy</a>.
                    </p>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-md bg-[#F5A623] py-2.5 text-sm font-semibold text-[#10141A] transition hover:shadow-[0_0_20px_#F5A62366] disabled:opacity-50"
                    >
                        <i class="fa-solid fa-user-plus text-xs"></i>
                        <span>{{ form.processing ? 'Creating account...' : 'Create account' }}</span>
                    </button>
                </form>

                <div class="mt-6 border-t border-[#232A34] pt-5 text-center text-xs text-[#8B949E]">
                    Already a member?
                    <a href="/login" class="font-semibold text-[#F5A623] hover:underline ml-1">Sign in here</a>
                </div>
            </div>
        </main>

        <footer class="mx-auto flex w-full max-w-md items-center justify-center gap-4 text-xs text-[#8B949E]">
            <a href="/privacy-policy" class="hover:text-white transition">Privacy Policy</a>
            <span>•</span>
            <a href="/terms" class="hover:text-white transition">Terms & Conditions</a>
        </footer>
    </div>
</template>