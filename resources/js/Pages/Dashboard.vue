<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    metrics: Object,
    statusBreakdown: Object,
    recentJobs: Array,
});
</script>

<template>
    <Head title="Terminal" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <!-- Ultra-Compact Metrics -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-soft flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Intake (24h)</p>
                        <h3 class="text-lg font-black text-slate-900 mt-1 leading-none">{{ metrics.today_jobs }}</h3>
                    </div>
                    <div class="w-7 h-7 rounded bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                </div>
                <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-soft flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Active Bench</p>
                        <h3 class="text-lg font-black text-slate-900 mt-1 leading-none">{{ metrics.active_repairs }}</h3>
                    </div>
                    <div class="w-7 h-7 rounded bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                </div>
                <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-soft flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Awaiting Approval</p>
                        <h3 class="text-lg font-black text-slate-900 mt-1 leading-none">{{ metrics.pending_approvals }}</h3>
                    </div>
                    <div class="w-7 h-7 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>
                <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-soft flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Total Units</p>
                        <h3 class="text-lg font-black text-slate-900 mt-1 leading-none">{{ Object.values(statusBreakdown).reduce((a, b) => a + b, 0) }}</h3>
                    </div>
                    <div class="w-7 h-7 rounded bg-slate-100 text-slate-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                <!-- Operational Matrix Table -->
                <div class="lg:col-span-3 space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Pipeline</h3>
                        <Link :href="route('jobs.index')" class="text-[9px] font-black text-blue-600 uppercase tracking-widest">Full Matrix</Link>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-soft">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">ID</th>
                                    <th class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Entity</th>
                                    <th class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Hardware</th>
                                    <th class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">State</th>
                                    <th class="px-3 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Ops</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="job in recentJobs" :key="job.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2 text-[11px] font-bold text-slate-900 font-mono">{{ job.job_number }}</td>
                                    <td class="px-3 py-2 text-[11px] text-slate-700 truncate max-w-[120px]">{{ job.customer?.name }}</td>
                                    <td class="px-3 py-2 text-[11px] text-slate-600">{{ job.brand }} {{ job.device_name }}</td>
                                    <td class="px-3 py-2">
                                        <StatusBadge :status="job.status" size="xs" />
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <Link :href="route('jobs.show', job.job_number)" class="text-blue-600 hover:text-blue-800 transition-colors">
                                            <svg class="w-3.5 h-3.5 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Load Analysis -->
                <div class="space-y-2">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Load Distribution</h3>
                    <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-soft space-y-3">
                        <div v-for="(count, status) in statusBreakdown" :key="status" class="space-y-1">
                            <div class="flex justify-between text-[9px] font-bold uppercase tracking-wider text-slate-500">
                                <span class="truncate max-w-[100px]">{{ status.replace('_', ' ') }}</span>
                                <span>{{ count }}</span>
                            </div>
                            <div class="h-1 bg-slate-50 rounded-full overflow-hidden">
                                <div class="h-full bg-slate-900" :style="{ width: (count / recentJobs.length * 20) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
