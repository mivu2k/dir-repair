<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: 'md',
    },
});

const statusConfig = {
    received: { color: 'bg-blue-600', bg: 'bg-blue-50', text: 'text-blue-600', label: 'Received' },
    diagnosing: { color: 'bg-amber-600', bg: 'bg-amber-50', text: 'text-amber-600', label: 'Diagnosing' },
    waiting_approval: { color: 'bg-violet-600', bg: 'bg-violet-50', text: 'text-violet-600', label: 'Waiting Approval' },
    in_progress: { color: 'bg-orange-600', bg: 'bg-orange-50', text: 'text-orange-600', label: 'In Progress' },
    completed: { color: 'bg-emerald-600', bg: 'bg-emerald-50', text: 'text-emerald-600', label: 'Completed' },
    delivered: { color: 'bg-slate-600', bg: 'bg-slate-50', text: 'text-slate-600', label: 'Delivered' },
    cancelled: { color: 'bg-red-600', bg: 'bg-red-50', text: 'text-red-600', label: 'Cancelled' },
    approved: { color: 'bg-emerald-600', bg: 'bg-emerald-50', text: 'text-emerald-600', label: 'Approved' },
    sent: { color: 'bg-blue-600', bg: 'bg-blue-50', text: 'text-blue-600', label: 'Sent' },
    draft: { color: 'bg-slate-400', bg: 'bg-slate-50', text: 'text-slate-400', label: 'Draft' },
};

const current = computed(() => statusConfig[props.status] || { color: 'bg-slate-600', bg: 'bg-slate-50', text: 'text-slate-600', label: props.status });
</script>

<template>
    <span :class="['inline-flex items-center gap-1 rounded-sm font-black border uppercase tracking-widest leading-none', 
        size === 'xs' ? 'px-1 py-0.5 text-[8px]' : 'px-2 py-1 text-[9px]',
        current.bg, current.text, 'border-current/10']">
        <span :class="['rounded-full', size === 'xs' ? 'w-1 h-1' : 'w-1.5 h-1.5', current.color]"></span>
        {{ current.label }}
    </span>
</template>
