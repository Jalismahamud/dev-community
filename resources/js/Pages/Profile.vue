<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ user: Object, techTags: Array });
const profile = props.user.data ?? props.user;
const form = useForm({
    name: profile.name ?? '',
    bio: profile.bio ?? '',
    github_url: profile.github_url ?? '',
    linkedin_url: profile.linkedin_url ?? '',
    current_status: profile.current_status ?? '',
    tag_ids: [],
    avatar: null,
});

const submit = () => form.post('/profile', { forceFormData: true });
</script>

<template>
    <main class="min-h-screen bg-[#10141A] px-5 py-8 text-[#E6E6E6]">
        <div class="mx-auto max-w-3xl">
            <a href="/feed" class="font-mono text-sm text-[#0FA3B1]">Back to feed</a>
            <section class="surface mt-6 p-6">
                <p class="font-mono text-xs uppercase tracking-[.2em] text-[#0FA3B1]">developer profile</p>
                <h1 class="font-display mt-2 text-3xl font-bold">Tune your signal.</h1>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <input v-model="form.name" class="amber-focus rounded-md border border-[#232A34] bg-[#10141A] p-3 outline-none" placeholder="Name" />
                    <input v-model="form.current_status" class="amber-focus rounded-md border border-[#232A34] bg-[#10141A] p-3 outline-none" placeholder="Currently working on" />
                    <textarea v-model="form.bio" class="amber-focus min-h-32 rounded-md border border-[#232A34] bg-[#10141A] p-3 outline-none md:col-span-2" placeholder="Short bio"></textarea>
                    <input v-model="form.github_url" class="amber-focus rounded-md border border-[#232A34] bg-[#10141A] p-3 outline-none" placeholder="GitHub URL" />
                    <input v-model="form.linkedin_url" class="amber-focus rounded-md border border-[#232A34] bg-[#10141A] p-3 outline-none" placeholder="LinkedIn URL" />
                </div>
                <label class="mt-5 block text-sm text-[#8B949E]">Avatar<input type="file" class="mt-2 block text-sm" accept="image/*" @change="form.avatar = $event.target.files[0]" /></label>
                <div class="mt-5 flex flex-wrap gap-2">
                    <label v-for="tag in techTags" :key="tag.id" class="tag-chip cursor-pointer px-3 py-1 text-xs"><input v-model="form.tag_ids" type="checkbox" :value="tag.id" class="mr-2" />{{ tag.name }}</label>
                </div>
                <button class="mt-6 rounded-md bg-[#F5A623] px-5 py-3 font-semibold text-[#10141A]" @click="submit">Save profile</button>
            </section>
        </div>
    </main>
</template>
