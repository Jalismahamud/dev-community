<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';

const props = defineProps({ conversations: Array });
const conversations = ref(props.conversations ?? []);
const active = ref(conversations.value[0] ?? null);
const body = ref('');

async function send() {
    if (!active.value || !body.value.trim()) return;
    const response = await axios.post(`/conversations/${active.value.id}/messages`, { body: body.value });
    active.value.messages.push(response.data.data);
    body.value = '';
}

function listen() {
    if (active.value) {
        window.Echo?.private(`conversation.${active.value.id}`).listen('.MessageSent', (event) => active.value.messages.push(event.message));
    }
}

onMounted(listen);
onUnmounted(() => active.value && window.Echo?.leave(`private-conversation.${active.value.id}`));
</script>

<template>
    <main class="min-h-screen bg-[#10141A] px-5 py-8 text-[#E6E6E6]">
        <div class="mx-auto grid max-w-5xl gap-5 md:grid-cols-[260px_1fr]">
            <aside class="surface p-4">
                <a href="/feed" class="font-mono text-xs text-[#0FA3B1]">Back to feed</a>
                <h1 class="font-display mt-5 text-2xl font-bold">Messages</h1>
                <button v-for="conversation in conversations" :key="conversation.id" class="mt-4 block w-full rounded-md border border-[#232A34] p-3 text-left text-sm hover:border-[#F5A623]" @click="active = conversation; listen()">Conversation #{{ conversation.id }}</button>
            </aside>
            <section class="surface flex min-h-[70vh] flex-col p-5">
                <div v-if="active" class="flex-1 space-y-3">
                    <div v-for="message in active.messages" :key="message.id" class="rounded-md border border-[#232A34] bg-[#10141A] p-3 text-sm">{{ message.body }}<div class="mt-1 font-mono text-[10px] text-[#8B949E]">{{ message.created_at }}</div></div>
                </div>
                <div v-else class="flex flex-1 items-center justify-center text-[#8B949E]">Start a conversation from a developer profile.</div>
                <form v-if="active" class="mt-5 flex gap-2" @submit.prevent="send"><input v-model="body" class="amber-focus flex-1 rounded-md border border-[#232A34] bg-[#10141A] p-3 outline-none" placeholder="Message..." /><button class="rounded-md bg-[#F5A623] px-4 font-semibold text-[#10141A]">Send</button></form>
            </section>
        </div>
    </main>
</template>
