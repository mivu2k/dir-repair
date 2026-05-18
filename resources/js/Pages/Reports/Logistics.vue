<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    stats: Object,
    dailyMovement: Array,
    overdueUnits: Array,
    activeIssuances: Array,
    filters: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

watch([startDate, endDate], () => {
    router.get(route('reports.logistics'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true, replace: true });
});
</script>

<template>
    <Head title="Logistics Audit" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-end">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Logistics Audit</h2>
                <p class="text-sm text-slate-500 font-medium">Comprehensive tracking of asset flow and unit movements.</p>
            </div>
            <div class="flex gap-4">
                <div>
                    <InputLabel value="From" />
                    <TextInput type="date" v-model="startDate" class="text-xs" />
                </div>
                <div>
                    <InputLabel value="To" />
                    <TextInput type="date" v-model="endDate" class="text-xs" />
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Inward Passes</div>
                <div class="text-3xl font-black text-emerald-600">{{ stats.inward_count }}</div>
                <div class="text-[10px] text-slate-500 mt-2">Units Received at Gate</div>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Outward Passes</div>
                <div class="text-3xl font-black text-orange-600">{{ stats.outward_count }}</div>
                <div class="text-[10px] text-slate-500 mt-2">Units Dispatched from Gate</div>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Active Demos</div>
                <div class="text-3xl font-black text-indigo-600">{{ stats.demo_issued }}</div>
                <div class="text-[10px] text-slate-500 mt-2">Currently out with Customers</div>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm" :class="{'border-red-200 bg-red-50': overdueUnits.length > 0}">
                <div class="text-xs font-black text-red-400 uppercase tracking-widest mb-1">Overdue Returns</div>
                <div class="text-3xl font-black text-red-600">{{ overdueUnits.length }}</div>
                <div class="text-[10px] text-red-500 mt-2 font-bold" v-if="overdueUnits.length > 0">Immediate Action Required</div>
                <div class="text-[10px] text-slate-500 mt-2" v-else>All demos on schedule</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Unit Audit List -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Unit Audit (Active Demos)</h3>
                    <span class="text-[10px] font-bold text-slate-400">TRACKING {{ activeIssuances.length }} UNITS</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-black uppercase tracking-widest border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3">Pass # / Client</th>
                                <th class="px-6 py-3 text-center">Aging</th>
                                <th class="px-6 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="unit in activeIssuances" :key="unit.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ unit.issuance_number }}</div>
                                    <div class="text-slate-500">{{ unit.customer.name }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-block px-3 py-1 rounded-full font-black" 
                                         :class="unit.aging_days > 7 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'">
                                        {{ unit.aging_days }} DAYS
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div v-if="new Date(unit.expected_return_date) < new Date()" class="text-red-600 font-black uppercase text-[10px]">Overdue</div>
                                    <div v-else class="text-slate-400 font-black uppercase text-[10px]">On Schedule</div>
                                </td>
                            </tr>
                            <tr v-if="activeIssuances.length === 0">
                                <td colspan="3" class="px-6 py-8 text-center text-slate-400 font-medium italic">No active demo issuances found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Asset Flow Activity -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Asset Flow (Movement Log)</h3>
                    <div class="flex gap-2">
                        <span class="flex items-center gap-1 text-[10px] font-bold text-emerald-600"><span class="w-2 h-2 rounded-full bg-emerald-600"></span> IN</span>
                        <span class="flex items-center gap-1 text-[10px] font-bold text-orange-600"><span class="w-2 h-2 rounded-full bg-orange-600"></span> OUT</span>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Simple List of daily counts if charts are complex, but let's do a clean list -->
                    <div class="space-y-4">
                        <div v-for="(day, idx) in dailyMovement" :key="idx" class="flex items-center justify-between p-3 border-b border-slate-50 last:border-0">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded bg-slate-100 flex flex-col items-center justify-center">
                                    <span class="text-[8px] font-black text-slate-400 uppercase leading-none">{{ new Date(day.date).toLocaleString('default', { month: 'short' }) }}</span>
                                    <span class="text-sm font-black text-slate-900 leading-none">{{ new Date(day.date).getDate() }}</span>
                                </div>
                                <div class="font-bold text-slate-800 text-sm">Gate Activity Recorded</div>
                            </div>
                            <div class="flex gap-4">
                                <div class="text-center">
                                    <div class="text-[8px] font-black text-slate-400 uppercase">IN</div>
                                    <div class="text-sm font-black text-emerald-600">{{ day.type === 'inward' ? day.count : 0 }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-[8px] font-black text-slate-400 uppercase">OUT</div>
                                    <div class="text-sm font-black text-orange-600">{{ day.type === 'outward' ? day.count : 0 }}</div>
                                </div>
                            </div>
                        </div>
                        <div v-if="dailyMovement.length === 0" class="text-center py-10 text-slate-400 italic">No movement recorded in this period.</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
