<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-slate-700 shadow-sm transition-all duration-200 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/25 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
