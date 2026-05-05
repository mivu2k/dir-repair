<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    intakes: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('intakes.index'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
});
</script>

<template>
    <Head title="Intakes" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex flex-col">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Intake Management</h2>
                    <span class="text-[10px] font-black text-slate-900 uppercase mt-1">Batch Tracking Matrix</span>
                </div>
                <div class="flex items-center gap-2 flex-1 max-w-md justify-end">
                    <div class="relative flex-1 max-w-xs">
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Search intakes..." 
                            class="input-field pl-8 py-1.5"
                        >
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <Link :href="route('intakes.create')" class="btn-primary py-1.5 px-4 text-[10px] uppercase font-black tracking-widest">New Intake</Link>
                </div>
            </div>
            
            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Intake #</th>
                            <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                            <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Units</th>
                            <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Received By</th>
                            <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Date</th>
                            <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Ops</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr 
                            v-for="intake in intakes.data" 
                            :key="intake.id" 
                            class="hover:bg-slate-50 transition-colors cursor-pointer"
                            @click="router.visit(route('intakes.show', intake.id))"
                        >
                            <td class="px-3 py-2 text-[11px] font-bold text-slate-900 font-mono">{{ intake.intake_number }}</td>
                            <td class="px-3 py-2">
                                <div class="flex flex-col">
                                    <span class="text-[11px] font-bold text-slate-700">{{ intake.customer?.name }}</span>
                                    <span class="text-[9px] text-slate-400 font-mono">{{ intake.customer?.phone }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded text-[10px] font-bold font-mono">
                                    {{ intake.repair_jobs?.length || 0 }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-[11px] text-slate-600 font-bold uppercase tracking-tighter">{{ intake.received_by?.name || 'Staff' }}</td>
                            <td class="px-3 py-2 text-right text-[11px] text-slate-400 font-mono">{{ new Date(intake.created_at).toLocaleDateString() }}</td>
                            <td class="px-3 py-2 text-right">
                                <Link :href="route('intakes.show', intake.id)" @click.stop class="text-blue-600 hover:underline font-bold text-[10px]">Open</Link>
                            </td>
                        </tr>
                        <tr v-if="intakes.data.length === 0">
                            <td colspan="6" class="px-3 py-8 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Zero intakes detected</td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div class="px-3 py-2 border-t border-slate-100 flex justify-between items-center bg-slate-50" v-if="intakes.links && intakes.total > 0">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ intakes.total }} Batches Logged</span>
                    <div class="flex gap-1">
                        <Link 
                            v-for="(link, i) in intakes.links" 
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
