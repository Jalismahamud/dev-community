<script setup>
defineOptions({ name: 'CommentThread' });

const props = defineProps({
    comment: { type: Object, required: true },
});

const emit = defineEmits(['like']);
</script>

<template>
    <div class="border-l border-[#0FA3B1] pl-3" :class="props.comment.depth >= 3 ? 'ml-0' : 'ml-3'">
        <div class="flex items-start justify-between gap-3 text-sm">
            <div>
                <span class="font-mono text-xs text-[#F5A623]">@{{ comment.user?.name }}</span>
                <span class="ml-2 text-[#C7CDD4]">{{ comment.body }}</span>
                <span v-if="comment.is_edited" class="ml-2 text-xs text-[#8B949E]">(edited)</span>
            </div>
            <button class="text-xs text-[#8B949E] hover:text-[#FF6B6B]" @click="emit('like', comment)">Like {{ comment.likes_count }}</button>
        </div>
        <div v-if="comment.replies?.length && comment.depth < 3" class="mt-3 space-y-3">
            <CommentThread v-for="reply in comment.replies" :key="reply.id" :comment="reply" @like="emit('like', $event)" />
        </div>
        <button v-else-if="comment.replies?.length" class="mt-2 font-mono text-xs text-[#0FA3B1]">Continue thread</button>
    </div>
</template>
