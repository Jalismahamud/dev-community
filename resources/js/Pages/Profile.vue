<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
    userTags: { type: Array, default: () => [] },
    techTags: { type: Array, default: () => [] },
});

const profile = props.user?.data ?? props.user;
const avatarPreview = ref(null);
const successMessage = ref('');

const mediaUrl = (path) => (!path ? '' : path.startsWith('http://') || path.startsWith('https://') ? path : `/storage/${path}`);
const userProfileUrl = (u) => (!u ? '#' : `/in/${u.username || u.id}`);

function onAvatarError(e, name = 'User') {
    e.target.onerror = null;
    e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=161B22&color=F5A623&bold=true`;
}

const form = useForm({
    name: profile.name ?? '',
    bio: profile.bio ?? '',
    github_url: profile.github_url ?? '',
    linkedin_url: profile.linkedin_url ?? '',
    current_status: profile.current_status ?? '',
    tag_ids: [...(props.userTags ?? [])],
    avatar: null,
});

function handleAvatarChange(event) {
    const file = event.target.files[0];
    if (file) {
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
}

function toggleTag(tagId) {
    const index = form.tag_ids.indexOf(tagId);
    if (index > -1) {
        form.tag_ids.splice(index, 1);
    } else {
        form.tag_ids.push(tagId);
    }
}

function submit() {
    form.post('/profile', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Profile updated successfully!';
            setTimeout(() => {
                successMessage.value = '';
            }, 3500);
        },
    });
}
</script>

<template>
    <div class="min-h-screen bg-[#090C15] text-[#E2E8F0]">
        <header class="sticky top-0 z-40 border-b border-[#1E253A] bg-[#0E1220]/90 backdrop-blur-md">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-3">
                <a href="/feed" class="flex items-center gap-2.5 font-display text-lg font-bold tracking-tight text-white">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-tr from-cyan-400 via-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                        <i class="fa-solid fa-wifi text-sm rotate-45"></i>
                    </div>
                    <span class="text-white text-base tracking-wide">Dev<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-400">Connect</span></span>
                </a>
                <nav class="flex items-center gap-5 text-xs text-[#94A3B8]">
                    <a href="/feed" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Feed</span>
                    </a>
                    <a :href="userProfileUrl(profile)" class="flex items-center gap-1.5 text-cyan-400 hover:text-cyan-300 transition">
                        <i class="fa-solid fa-eye text-xs"></i>
                        <span>View Public Profile</span>
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-6 py-10 pb-24">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white">Developer Profile Settings</h1>
                    <p class="text-xs text-[#64748B] mt-1">Configure your public engineering presence and tech stack competencies.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        :href="userProfileUrl(profile)"
                        class="rounded-xl border border-[#1E253A] bg-[#141829] px-4 py-2 text-xs font-semibold text-slate-200 hover:text-white hover:border-purple-500/50 transition flex items-center gap-2"
                    >
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs text-purple-400"></i>
                        <span>Public View</span>
                    </a>
                </div>
            </div>

            <div
                v-if="successMessage"
                class="mb-6 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-5 py-3.5 text-xs text-emerald-300 flex items-center gap-2.5 transition"
            >
                <i class="fa-solid fa-circle-check text-sm"></i>
                <span>{{ successMessage }}</span>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <section class="rounded-3xl border border-[#1E253A] bg-[#0E1220] p-6 sm:p-8 space-y-6 shadow-2xl">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                        <div class="relative shrink-0">
                            <div class="h-24 w-24 overflow-hidden rounded-full border-2 border-purple-500/50 bg-[#161B2E] flex items-center justify-center font-display text-2xl font-bold text-purple-300 shadow-xl">
                                <img
                                    v-if="avatarPreview"
                                    :src="avatarPreview"
                                    alt="Preview"
                                    class="h-full w-full object-cover"
                                />
                                <img
                                    v-else-if="profile.avatar_path"
                                    :src="mediaUrl(profile.avatar_path)"
                                    :alt="profile.name"
                                    class="h-full w-full object-cover"
                                    @error="(e) => onAvatarError(e, profile.name)"
                                />
                                <span v-else>{{ profile.name?.charAt(0) ?? 'U' }}</span>
                            </div>
                            <label
                                class="absolute bottom-0 right-0 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-gradient-to-r from-cyan-400 to-blue-500 text-slate-950 shadow-lg hover:scale-110 transition"
                                title="Change avatar photo"
                            >
                                <i class="fa-solid fa-camera text-xs"></i>
                                <input type="file" class="hidden" accept="image/*" @change="handleAvatarChange" />
                            </label>
                        </div>

                        <div class="flex-1 space-y-2">
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <span>{{ form.name || 'Anonymous Developer' }}</span>
                                <i class="fa-solid fa-circle-check text-xs text-cyan-400"></i>
                            </h2>
                            <p class="text-xs text-[#64748B] font-mono">{{ form.current_status || 'Senior Software Engineer' }}</p>

                            <div class="mt-3 max-w-sm">
                                <div class="flex justify-between text-[11px] mb-1 font-mono text-[#64748B]">
                                    <span>Profile Strength</span>
                                    <span class="text-purple-300 font-bold">{{ profile.profile_completion ?? 85 }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-[#121627] overflow-hidden border border-[#1E253A]">
                                    <div
                                        class="h-full bg-gradient-to-r from-cyan-400 via-indigo-500 to-purple-500 transition-all duration-500"
                                        :style="{ width: `${profile.profile_completion ?? 85}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 pt-6 border-t border-[#1E253A]">
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-200">
                                Full Name
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-xl border border-[#1E253A] bg-[#121627] px-4 py-2.5 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 transition"
                                placeholder="Your full name"
                            />
                            <p v-if="form.errors.name" class="text-[11px] text-[#F43F5E]">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-200">
                                Current Role / Headline
                            </label>
                            <input
                                v-model="form.current_status"
                                type="text"
                                class="w-full rounded-xl border border-[#1E253A] bg-[#121627] px-4 py-2.5 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 transition"
                                placeholder="e.g. Senior Backend Engineer @ CloudScale"
                            />
                            <p v-if="form.errors.current_status" class="text-[11px] text-[#F43F5E]">{{ form.errors.current_status }}</p>
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-200">
                                Bio & Engineering Expertise
                            </label>
                            <textarea
                                v-model="form.bio"
                                rows="4"
                                class="w-full rounded-xl border border-[#1E253A] bg-[#121627] px-4 py-3 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 transition leading-relaxed"
                                placeholder="Tell fellow software engineers about your engineering focus, past projects, open source..."
                            ></textarea>
                            <p v-if="form.errors.bio" class="text-[11px] text-[#F43F5E]">{{ form.errors.bio }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-200">
                                GitHub URL
                            </label>
                            <input
                                v-model="form.github_url"
                                type="url"
                                class="w-full rounded-xl border border-[#1E253A] bg-[#121627] px-4 py-2.5 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 transition"
                                placeholder="https://github.com/username"
                            />
                            <p v-if="form.errors.github_url" class="text-[11px] text-[#F43F5E]">{{ form.errors.github_url }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-200">
                                LinkedIn URL
                            </label>
                            <input
                                v-model="form.linkedin_url"
                                type="url"
                                class="w-full rounded-xl border border-[#1E253A] bg-[#121627] px-4 py-2.5 text-xs text-white placeholder-[#64748B] outline-none focus:border-purple-500 transition"
                                placeholder="https://linkedin.com/in/username"
                            />
                            <p v-if="form.errors.linkedin_url" class="text-[11px] text-[#F43F5E]">{{ form.errors.linkedin_url }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-[#1E253A] bg-[#0E1220] p-6 sm:p-8 space-y-4 shadow-2xl">
                    <div>
                        <h2 class="text-sm font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-purple-400"></i>
                            <span>My Tech Stack Competencies</span>
                        </h2>
                        <p class="text-xs text-[#64748B] mt-0.5">Select the technologies that power your stack to personalize your signals feed.</p>
                    </div>

                    <div class="flex flex-wrap gap-2.5 pt-2">
                        <button
                            v-for="tag in props.techTags"
                            :key="tag.id"
                            type="button"
                            class="rounded-xl px-4 py-2 font-mono text-xs transition flex items-center gap-2 border"
                            :class="
                                form.tag_ids.includes(tag.id)
                                    ? 'border-purple-500/70 bg-purple-500/20 text-purple-200 font-semibold shadow-sm'
                                    : 'border-[#1E253A] bg-[#121627] text-[#94A3B8] hover:border-purple-500/40 hover:text-white'
                            "
                            @click="toggleTag(tag.id)"
                        >
                            <i :class="form.tag_ids.includes(tag.id) ? 'fa-solid fa-check text-[10px] text-cyan-400' : 'fa-solid fa-plus text-[10px] opacity-40'"></i>
                            <span>{{ tag.name }}</span>
                        </button>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a
                        href="/feed"
                        class="rounded-xl border border-[#1E253A] bg-[#141829] px-5 py-2.5 text-xs font-semibold text-[#94A3B8] hover:text-white transition"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-2.5 text-xs font-bold text-white shadow-lg shadow-purple-500/25 hover:scale-105 transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <i v-if="form.processing" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        <i v-else class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>{{ form.processing ? 'Saving...' : 'Save Profile Changes' }}</span>
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
