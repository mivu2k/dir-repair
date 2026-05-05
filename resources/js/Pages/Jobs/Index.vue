<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    jobs: Object,
    filters: Object,
    technicians: Array,
    statuses: Array,
});

const filters = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    technician_id: props.filters.technician_id || '',
});

watch(filters, () => {
    router.get(route('jobs.index'), filters.value, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}, { deep: true });
</script>

<template>
    <Head title="Repair Pipeline" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <!-- Global Filter Bar -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white border border-slate-200 rounded-lg p-2.5">
                <div class="relative flex-1 max-w-sm">
                    <input 
                        v-model="filters.search" 
                        type="text" 
                        placeholder="Search Serial, Job, or Customer..." 
                        class="input-field pl-8"
                    >
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <div class="flex items-center gap-2">
                    <select v-model="filters.status" class="input-field py-1.5 w-auto capitalize">
                        <option value="">All States</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ s.replace('_', ' ') }}</option>
                    </select>
                    <select v-model="filters.technician_id" class="input-field py-1.5 w-auto">
                        <option value="">All Staff</option>
                        <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                    <Link :href="route('intakes.create')" class="btn-primary">New Intake</Link>
                </div>
            </div>

            <!-- Pipeline Matrix -->
            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 whitespace-nowrap">
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Job ID / Intake</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Customer Entity</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Hardware Ref</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Workflow State</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Staff</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Ops</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="job in jobs.data" :key="job.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-black text-slate-900 font-mono tracking-tight">{{ job.job_number }}</span>
                                        <span v-if="job.intake" class="text-[9px] font-bold text-blue-500 uppercase tracking-tighter">Ref: {{ job.intake.intake_number }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-bold text-slate-700 truncate max-w-[140px]">{{ job.customer.name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ job.customer.phone }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] text-slate-700 font-medium">{{ job.brand }} {{ job.device_name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ job.serial_number || 'NO-SERIAL' }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <StatusBadge :status="job.status" size="xs" />
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">{{ job.technician?.name || 'Unassigned' }}</span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <Link :href="route('jobs.show', job.job_number)" class="text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Functional Pagination -->
                <div class="px-3 py-2 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sector {{ jobs.from }}-{{ jobs.to }} of {{ jobs.total }} Units</span>
                    <div class="flex gap-1">
                        <Link 
                            v-for="(link, i) in jobs.links" 
                            :key="i" 
                            :href="link.url || '#'" 
                            :class="[
                                'px-1.5 py-0.5 rounded text-[9px] font-black transition-all border', 
                                link.active ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50',
                                !link.url && 'opacity-30 pointer-events-none'
                            ]" 
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
