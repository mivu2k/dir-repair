<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="space-y-2">
            <h1 class="text-4xl md:text-5xl font-display font-black tracking-tight text-primary">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="text-sm md:text-base text-zinc-500 font-medium max-w-2xl leading-relaxed">
                {{ subtitle }}
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <slot name="actions" />
        </div>
    </div>
</template>
