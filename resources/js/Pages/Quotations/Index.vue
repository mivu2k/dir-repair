<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    quotations: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('quotations.index'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        maximumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head title="Quotations" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none">
                <div class="relative w-full sm:max-w-sm">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Search quotations..." 
                        class="input-field w-full"
                        style="padding-left: 2.25rem !important;"
                    >
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                </div>
            </div>
            
            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left min-w-[600px] sm:min-w-full">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Quote #</th>
                                <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Job #</th>
                                <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                                <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Amount</th>
                                <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Ops</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        <tr 
                            v-for="quote in quotations.data" 
                            :key="quote.id" 
                            class="hover:bg-slate-50 transition-colors cursor-pointer"
                            @click="router.visit(route('quotations.show', quote.id))"
                        >
                            <td class="px-3 py-2 text-[11px] font-bold text-slate-900 font-mono">{{ quote.quotation_number }}</td>
                            <td class="px-3 py-2 text-[11px] text-slate-600 font-mono font-bold">{{ quote.repair_job?.job_number || 'N/A' }}</td>
                            <td class="px-3 py-2 text-[11px] text-slate-700 font-bold truncate max-w-[150px]">{{ quote.repair_job?.customer?.name }}</td>
                            <td class="px-3 py-2 text-[11px] text-right font-bold text-blue-600">{{ formatCurrency(quote.total_amount) }}</td>
                            <td class="px-3 py-2">
                                <StatusBadge :status="quote.status" size="xs" />
                            </td>
                            <td class="px-3 py-2 text-right">
                                <Link :href="route('quotations.show', quote.id)" @click.stop class="text-blue-600 hover:underline font-bold text-[10px]">Open</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
                
                <div class="px-3 py-2 border-t border-slate-100 flex justify-between items-center bg-slate-50" v-if="quotations.links">
                    <span class="text-[9px] font-bold text-slate-400 uppercase">{{ quotations.total }} Financials</span>
                    <div class="flex gap-1">
                        <Link 
                            v-for="(link, i) in quotations.links" 
                            :key="i" 
                            :href="link.url || '#'" 
                            :class="[
                                'px-1.5 py-0.5 rounded text-[9px] font-black transition-all border', 
                                link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50',
                                !link.url && 'opacity-30'
                            ]" 
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
