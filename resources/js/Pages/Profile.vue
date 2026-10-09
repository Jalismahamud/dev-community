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
    <div class="min-h-screen bg-[#0D1117] text-[#E6E6E6]">
        <!-- Top Navigation -->
        <header class="sticky top-0 z-40 border-b border-[#232A34] bg-[#161B22]/95 backdrop-blur">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-3">
                <a href="/feed" class="flex items-center gap-2 font-display text-lg font-bold tracking-tight text-white">
                    <i class="fa-solid fa-terminal text-[#F5A623]"></i>
                    <span><span class="text-[#F5A623]">dev</span>/community</span>
                </a>
                <nav class="flex items-center gap-4 text-xs text-[#8B949E]">
                    <a href="/feed" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Feed</span>
                    </a>
                    <a :href="userProfileUrl(profile)" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fa-solid fa-eye text-xs"></i>
                        <span>View Public Profile</span>
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-5 py-8 pb-24">
            <!-- Header Banner -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-[#0FA3B1]">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Settings & Identity</span>
                    </div>
                    <h1 class="font-display mt-1 text-2xl sm:text-3xl font-extrabold text-white">Developer Profile</h1>
                    <p class="text-xs text-[#8B949E] mt-1">Configure your public engineering presence and tech competencies.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        :href="userProfileUrl(profile)"
                        class="rounded-md border border-[#232A34] bg-[#161B22] px-4 py-2 text-xs font-semibold text-[#C7CDD4] hover:border-[#0FA3B1] hover:text-[#53D2DE] transition flex items-center gap-2"
                    >
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                        <span>Public View</span>
                    </a>
                </div>
            </div>

            <!-- Flash Success Notification -->
            <div
                v-if="successMessage"
                class="mb-6 rounded-lg border border-[#10B981]/50 bg-[#10B981]/10 px-4 py-3 text-xs text-[#10B981] flex items-center gap-2.5 transition"
            >
                <i class="fa-solid fa-circle-check text-sm"></i>
                <span>{{ successMessage }}</span>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Profile Card & Avatar Section -->
                <section class="surface border border-[#232A34] bg-[#161B22] p-6 rounded-xl space-y-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                        <!-- Avatar Display / Uploader -->
                        <div class="relative shrink-0">
                            <div class="h-24 w-24 overflow-hidden rounded-full border-2 border-[#F5A623] bg-[#0D1117] flex items-center justify-center font-display text-2xl font-bold text-[#F5A623] shadow-md">
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
                                class="absolute bottom-0 right-0 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-[#F5A623] text-[#10141A] shadow-lg hover:scale-110 transition"
                                title="Change avatar photo"
                            >
                                <i class="fa-solid fa-camera text-xs"></i>
                                <input type="file" class="hidden" accept="image/*" @change="handleAvatarChange" />
                            </label>
                        </div>

                        <!-- User Meta & Completion -->
                        <div class="flex-1 space-y-2">
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <span>{{ form.name || 'Anonymous Developer' }}</span>
                                <i class="fa-solid fa-circle-check text-xs text-[#0FA3B1]"></i>
                            </h2>
                            <p class="text-xs text-[#8B949E] font-mono">// {{ form.current_status || 'Software Engineer' }}</p>

                            <!-- Profile Completion Bar -->
                            <div class="mt-3 max-w-sm">
                                <div class="flex justify-between text-[11px] mb-1 font-mono text-[#8B949E]">
                                    <span>Profile Strength</span>
                                    <span class="text-[#53D2DE] font-bold">{{ profile.profile_completion ?? 75 }}%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-[#0D1117] overflow-hidden border border-[#232A34]">
                                    <div
                                        class="h-full bg-gradient-to-r from-[#0FA3B1] to-[#F5A623] transition-all duration-500"
                                        :style="{ width: `${profile.profile_completion ?? 75}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Fields Grid -->
                    <div class="grid gap-4 sm:grid-cols-2 pt-4 border-t border-[#232A34]">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[#C7CDD4] flex items-center gap-1.5">
                                <i class="fa-solid fa-signature text-[#0FA3B1]"></i>
                                <span>Full Name</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-md border border-[#232A34] bg-[#0D1117] px-3.5 py-2.5 text-xs text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623] transition"
                                placeholder="Your full name"
                            />
                            <p v-if="form.errors.name" class="text-[11px] text-[#FF6B6B]">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[#C7CDD4] flex items-center gap-1.5">
                                <i class="fa-solid fa-briefcase text-[#F5A623]"></i>
                                <span>Current Role / Status Headline</span>
                            </label>
                            <input
                                v-model="form.current_status"
                                type="text"
                                class="w-full rounded-md border border-[#232A34] bg-[#0D1117] px-3.5 py-2.5 text-xs text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623] transition"
                                placeholder="e.g. Senior Backend Engineer @ ScaleTech"
                            />
                            <p v-if="form.errors.current_status" class="text-[11px] text-[#FF6B6B]">{{ form.errors.current_status }}</p>
                        </div>

                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="block text-xs font-semibold text-[#C7CDD4] flex items-center gap-1.5">
                                <i class="fa-solid fa-pen-nib text-[#0FA3B1]"></i>
                                <span>Bio & Background (Markdown supported)</span>
                            </label>
                            <textarea
                                v-model="form.bio"
                                rows="4"
                                class="w-full rounded-md border border-[#232A34] bg-[#0D1117] px-3.5 py-2.5 text-xs text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623] transition leading-5"
                                placeholder="Tell fellow engineers about your core expertise, past projects, open source contributions..."
                            ></textarea>
                            <p v-if="form.errors.bio" class="text-[11px] text-[#FF6B6B]">{{ form.errors.bio }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[#C7CDD4] flex items-center gap-1.5">
                                <i class="fa-brands fa-github text-[#8B949E]"></i>
                                <span>GitHub URL</span>
                            </label>
                            <input
                                v-model="form.github_url"
                                type="url"
                                class="w-full rounded-md border border-[#232A34] bg-[#0D1117] px-3.5 py-2.5 text-xs text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623] transition"
                                placeholder="https://github.com/username"
                            />
                            <p v-if="form.errors.github_url" class="text-[11px] text-[#FF6B6B]">{{ form.errors.github_url }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[#C7CDD4] flex items-center gap-1.5">
                                <i class="fa-brands fa-linkedin text-[#0A66C2]"></i>
                                <span>LinkedIn URL</span>
                            </label>
                            <input
                                v-model="form.linkedin_url"
                                type="url"
                                class="w-full rounded-md border border-[#232A34] bg-[#0D1117] px-3.5 py-2.5 text-xs text-white placeholder-[#8B949E] outline-none focus:border-[#F5A623] transition"
                                placeholder="https://linkedin.com/in/username"
                            />
                            <p v-if="form.errors.linkedin_url" class="text-[11px] text-[#FF6B6B]">{{ form.errors.linkedin_url }}</p>
                        </div>
                    </div>
                </section>

                <!-- Tech Competencies & Stack Tags Section -->
                <section class="surface border border-[#232A34] bg-[#161B22] p-6 rounded-xl space-y-4 shadow-sm">
                    <div>
                        <h2 class="text-sm font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-[#F5A623]"></i>
                            <span>My Tech Stack & Competencies</span>
                        </h2>
                        <p class="text-xs text-[#8B949E] mt-0.5">Select the technologies that power your work. These help curate your custom feed.</p>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <button
                            v-for="tag in props.techTags"
                            :key="tag.id"
                            type="button"
                            class="rounded-full px-3 py-1.5 font-mono text-xs transition flex items-center gap-1.5 border"
                            :class="
                                form.tag_ids.includes(tag.id)
                                    ? 'border-[#0FA3B1] bg-[#0FA3B125] text-[#53D2DE] font-semibold'
                                    : 'border-[#232A34] bg-[#0D1117] text-[#8B949E] hover:border-[#8B949E] hover:text-white'
                            "
                            @click="toggleTag(tag.id)"
                        >
                            <i :class="form.tag_ids.includes(tag.id) ? 'fa-solid fa-check text-[10px]' : 'fa-solid fa-plus text-[10px] opacity-40'"></i>
                            <span>{{ tag.name }}</span>
                        </button>
                    </div>
                </section>

                <!-- Action Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a
                        href="/feed"
                        class="rounded-md border border-[#232A34] bg-[#161B22] px-5 py-2.5 text-xs font-semibold text-[#8B949E] hover:text-white transition"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-[#F5A623] px-6 py-2.5 text-xs font-bold text-[#10141A] hover:shadow-[0_0_15px_#F5A62355] transition flex items-center gap-2 disabled:opacity-50"
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
