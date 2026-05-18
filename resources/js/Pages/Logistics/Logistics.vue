<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    stats: Object,
    activeIssuances: Array,
    overdueUnits: Array,
    filters: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const updateFilters = () => {
    router.get(route('reports.logistics'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true, replace: true });
};

watch([startDate, endDate], updateFilters);
</script>

<template>
    <Head title="Logistics Audit" />

    <AuthenticatedLayout>
        <div class="space-y-8">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Logistics Audit</h2>
                    <p class="text-sm text-slate-500 font-bold italic">Asset flow and demo tracking system.</p>
                </div>
                <div class="flex gap-4 items-end">
                    <div class="space-y-1">
                        <InputLabel value="Audit Start" class="text-[9px]" />
                        <TextInput type="date" v-model="startDate" class="text-xs" />
                    </div>
                    <div class="space-y-1">
                        <InputLabel value="Audit End" class="text-[9px]" />
                        <TextInput type="date" v-model="endDate" class="text-xs" />
                    </div>
                </div>
            </div>

            <!-- Asset Flow Matrix -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-emerald-500">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Inward Traffic</div>
                    <div class="text-3xl font-black text-slate-900">{{ stats.inward }}</div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-orange-500">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Outward Traffic</div>
                    <div class="text-3xl font-black text-slate-900">{{ stats.outward }}</div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-indigo-500">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Demo Units Out</div>
                    <div class="text-3xl font-black text-slate-900">{{ stats.demo_issued }}</div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-red-500" :class="{'bg-red-50 border-red-100': stats.overdue_count > 0}">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Overdue Returns</div>
                    <div class="text-3xl font-black text-red-600">{{ stats.overdue_count }}</div>
                </div>
            </div>

            <!-- Demo Aging Monitor -->
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Demo Unit Aging Audit</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-black uppercase border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">Issuance #</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4">Aging (Days)</th>
                                <th class="px-6 py-4">Expected Return</th>
                                <th class="px-6 py-4 text-right">Risk Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 font-bold">
                            <tr v-for="unit in activeIssuances" :key="unit.id" class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-black">{{ unit.issuance_number }}</td>
                                <td class="px-6 py-4 text-slate-900 uppercase">{{ unit.customer.name }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full font-black" :class="unit.aging_days > 7 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'">
                                        {{ unit.aging_days }} DAYS
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ unit.expected_return_date ? new Date(unit.expected_return_date).toLocaleDateString() : 'N/A' }}</td>
                                <td class="px-6 py-4 text-right uppercase text-[10px]">
                                    <span v-if="new Date(unit.expected_return_date) < new Date()" class="text-red-600 font-black">Critically Overdue</span>
                                    <span v-else class="text-slate-400 font-black">Within Cycle</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
