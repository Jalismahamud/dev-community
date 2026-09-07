<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post('/login');
</script>
<template><main class="flex min-h-screen items-center justify-center bg-[#10141A] px-5 text-[#E6E6E6]"><form class="surface w-full max-w-md p-7" @submit.prevent="submit"><p class="font-mono text-xs uppercase tracking-[.2em] text-[#0FA3B1]">/auth/login</p><h1 class="font-display mt-3 text-3xl font-bold">Welcome back.</h1><label class="mt-6 block text-sm text-[#8B949E]">Email<input v-model="form.email" type="email" class="amber-focus mt-2 w-full rounded-md border border-[#232A34] bg-[#10141A] p-3 outline-none" /></label><label class="mt-4 block text-sm text-[#8B949E]">Password<input v-model="form.password" type="password" class="amber-focus mt-2 w-full rounded-md border border-[#232A34] bg-[#10141A] p-3 outline-none" /></label><button class="mt-6 w-full rounded-md bg-[#F5A623] p-3 font-semibold text-[#10141A]">Enter community →</button><a href="/register" class="mt-4 block text-center text-sm text-[#0FA3B1]">Create an account</a></form></main></template>