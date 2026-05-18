<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    groupedJobs: Object,
    totalJobs: Number,
    totalRevenue: Number,
    inventoryReport: Array,
    technicians: Array,
    customers: Array,
    filters: Object,
});

const filters = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    technician_id: props.filters.technician_id || '',
    customer_id: props.filters.customer_id || '',
    status: props.filters.status || '',
    brand: props.filters.brand || '',
    model: props.filters.model || '',
    search: props.filters.search || '',
    group_by: props.filters.group_by || 'none',
});

const updateFilters = () => {
    router.get(route('reports.index'), filters.value, {
        preserveState: true,
        replace: true
    });
};

const resetFilters = () => {
    filters.value = {
        start_date: '',
        end_date: '',
        technician_id: '',
        customer_id: '',
        status: '',
        brand: '',
        model: '',
        search: '',
    };
    updateFilters();
};

watch(filters, updateFilters, { deep: true });

const exportPdf = () => {
    window.location.href = route('reports.pdf', filters.value);
};

const exportExcel = () => {
    window.location.href = route('reports.excel', filters.value);
};
</script>

<template>
    <Head title="Technical & Financial Reports" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">Operational Reports</h2>
                    <p class="text-xs text-slate-500 font-bold">Comprehensive technical and financial audit.</p>
                </div>
                <div class="flex gap-2">
                    <button @click="resetFilters" class="text-[10px] font-black uppercase text-slate-400 hover:text-slate-900 mr-2">Reset</button>
                    <button @click="exportPdf" class="border border-slate-200 text-red-500 px-4 py-2 rounded font-black text-[10px] uppercase hover:bg-red-50 transition-all">PDF</button>
                    <button @click="exportExcel" class="border border-slate-200 text-emerald-600 px-4 py-2 rounded font-black text-[10px] uppercase hover:bg-emerald-50 transition-all">Excel</button>
                </div>
            </div>

            <!-- Advanced Filter Matrix -->
            <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div class="space-y-1">
                        <InputLabel value="From Date" class="text-[9px]" />
                        <TextInput type="date" v-model="filters.start_date" class="w-full text-xs" />
                    </div>
                    <div class="space-y-1">
                        <InputLabel value="To Date" class="text-[9px]" />
                        <TextInput type="date" v-model="filters.end_date" class="w-full text-xs" />
                    </div>
                    <div class="space-y-1">
                        <InputLabel value="Technician" class="text-[9px]" />
                        <select v-model="filters.technician_id" class="w-full border-slate-200 rounded text-xs py-2">
                            <option value="">All Staff</option>
                            <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <InputLabel value="Status" class="text-[9px]" />
                        <select v-model="filters.status" class="w-full border-slate-200 rounded text-xs py-2">
                            <option value="">All Status</option>
                            <option value="received">Received</option>
                            <option value="diagnosing">Diagnosing</option>
                            <option value="quoted">Quoted</option>
                            <option value="approved">Approved</option>
                            <option value="repairing">Repairing</option>
                            <option value="completed">Completed</option>
                            <option value="delivered">Delivered</option>
                        </select>
                    </div>
                    <div class="space-y-1 lg:col-span-1">
                        <InputLabel value="Analyze By" class="text-[9px]" />
                        <select v-model="filters.group_by" class="w-full border-slate-200 rounded text-xs py-2 bg-slate-50 font-bold">
                            <option value="none">Flat List</option>
                            <option value="technician">By Technician</option>
                            <option value="customer">By Customer</option>
                            <option value="status">By Status</option>
                            <option value="symptom">By Symptom</option>
                        </select>
                    </div>
                    <div class="space-y-1 lg:col-span-1">
                        <InputLabel value="Search Identifier" class="text-[9px]" />
                        <TextInput type="text" v-model="filters.search" placeholder="Job #, Serial..." class="w-full text-xs" />
                    </div>
                </div>
            </div>

            <!-- Financial Summary Matrix -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm border-l-4 border-l-emerald-500">
                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Projected Revenue</div>
                    <div class="text-2xl font-black text-slate-900">Rs. {{ totalRevenue.toLocaleString() }}</div>
                </div>
                <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm border-l-4 border-l-indigo-500">
                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Jobs Processed</div>
                    <div class="text-2xl font-black text-slate-900">{{ totalJobs }}</div>
                </div>
                <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm border-l-4 border-l-orange-500">
                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Inventory Value</div>
                    <div class="text-2xl font-black text-slate-900">Rs. {{ inventoryReport.reduce((acc, i) => acc + i.total_revenue, 0).toLocaleString() }}</div>
                </div>
            </div>

            <!-- Job Ledger -->
            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-black uppercase tracking-widest border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4">Job Details</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Quote Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 font-bold">
                        <template v-for="(items, groupName) in groupedJobs" :key="groupName">
                            <tr v-if="filters.group_by !== 'none'" class="bg-slate-900 text-white">
                                <td colspan="4" class="px-6 py-2 text-[10px] font-black uppercase tracking-widest">{{ groupName }} ({{ items.length }})</td>
                            </tr>
                            <tr v-for="job in items" :key="job.id" 
                                @click="router.visit(route('jobs.show', job.job_number))"
                                class="hover:bg-slate-50 cursor-pointer">
                                <td class="px-6 py-4">
                                    <div class="text-slate-900 font-black">{{ job.job_number }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase">{{ job.brand }} {{ job.device_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ job.customer.name }}</td>
                                <td class="px-6 py-4 text-center"><StatusBadge :status="job.status" /></td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">
                                    Rs. {{ (job.approved_quotation?.total_amount || 0).toLocaleString() }}
                                </td>
                            </tr>
                        </template>
                        <tr v-if="Object.values(groupedJobs).flat().length === 0">
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400 italic">No records match the current filter matrix.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
