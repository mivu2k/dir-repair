<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    jobs: Array,
    groupedData: Object,
    inventoryReport: Array,
    filters: Object,
    technicians: Array,
    customers: Array,
    symptoms: Array,
    parts: Array,
});

const filters = ref({
    search: props.filters.search || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    group_by: props.filters.group_by || 'none',
    technician_id: props.filters.technician_id || '',
    customer_id: props.filters.customer_id || '',
    status: props.filters.status || '',
    brand: props.filters.brand || '',
    model: props.filters.model || '',
    symptom_id: props.filters.symptom_id || '',
    part_id: props.filters.part_id || '',
});

const activeTab = ref('jobs');
const showAdvanced = ref(false);

const applyFilters = () => {
    router.get(route('reports.index'), filters.value, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = {
        search: '',
        start_date: '',
        end_date: '',
        group_by: 'none',
        technician_id: '',
        customer_id: '',
        status: '',
        brand: '',
        model: '',
        symptom_id: '',
        part_id: '',
    };
    applyFilters();
};

const exportData = (format) => {
    const params = new URLSearchParams(filters.value).toString();
    const url = `${route('reports.export')}?format=${format}&${params}`;
    window.open(url, '_blank');
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        maximumFractionDigits: 0,
    }).format(amount || 0);
};
</script>

<template>
    <Head title="Reports" />

    <AuthenticatedLayout>
        <div class="space-y-4 max-w-7xl mx-auto">
            <!-- Minimalist Filter Hub -->
            <div class="bg-white border border-slate-200 rounded-lg p-3 space-y-3 shadow-sm">
                <div class="flex flex-col md:flex-row gap-3 items-center">
                    <div class="relative flex-1 w-full md:max-w-xs">
                        <input 
                            v-model="filters.search" 
                            @keyup.enter="applyFilters"
                            type="text" 
                            class="input-field pl-8 py-2"
                            placeholder="Search everything..."
                        >
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                        <input type="date" v-model="filters.start_date" class="input-field py-2 w-auto text-[11px]">
                        <input type="date" v-model="filters.end_date" class="input-field py-2 w-auto text-[11px]">
                        
                        <select v-model="filters.status" class="input-field py-2 w-auto text-[11px]">
                            <option value="">All Statuses</option>
                            <option v-for="s in ['received', 'diagnosing', 'waiting_approval', 'in_progress', 'completed', 'delivered', 'cancelled']" :key="s" :value="s">{{ s.replace('_', ' ') }}</option>
                        </select>

                        <!-- Symptom Filter Pulled to Main Row -->
                        <select v-model="filters.symptom_id" class="input-field py-2 w-auto text-[11px] font-bold text-slate-900 border-indigo-200 bg-indigo-50/30">
                            <option value="">All Symptoms</option>
                            <option v-for="s in symptoms" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>

                        <button @click="showAdvanced = !showAdvanced" class="px-3 py-2 text-[10px] font-black uppercase text-slate-400 hover:text-slate-900 transition-colors flex items-center gap-1">
                            More
                            <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': showAdvanced }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div class="h-6 w-px bg-slate-200 mx-1"></div>

                        <button @click="applyFilters" class="btn-primary py-2 px-6 text-[10px]">Analyze</button>
                        
                        <div class="flex border border-slate-200 rounded overflow-hidden shadow-sm">
                            <button @click="exportData('pdf')" class="px-4 py-2 bg-white hover:bg-slate-50 text-[10px] font-black text-rose-600 border-r border-slate-200 uppercase tracking-widest">PDF</button>
                            <button @click="exportData('excel')" class="px-4 py-2 bg-white hover:bg-slate-50 text-[10px] font-black text-emerald-600 uppercase tracking-widest">Excel</button>
                        </div>
                    </div>
                </div>

                <!-- Advanced Filter Matrix -->
                <div v-if="showAdvanced" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 pt-3 border-t border-slate-100 animate-fade-in">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Staff Specialist</label>
                        <select v-model="filters.technician_id" class="input-field py-1.5 text-[11px]">
                            <option value="">All Staff</option>
                            <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Client Entity</label>
                        <select v-model="filters.customer_id" class="input-field py-1.5 text-[11px]">
                            <option value="">All Clients</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Manufacturer</label>
                        <input v-model="filters.brand" type="text" class="input-field py-1.5 text-[11px]" placeholder="e.g. Apple">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Model ID</label>
                        <input v-model="filters.model" type="text" class="input-field py-1.5 text-[11px]" placeholder="e.g. A2633">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Hardware Asset</label>
                        <select v-model="filters.part_id" class="input-field py-1.5 text-[11px]">
                            <option value="">All Parts</option>
                            <option v-for="p in parts" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button @click="resetFilters" class="text-[9px] font-black text-slate-400 hover:text-red-500 uppercase tracking-widest transition-colors mb-2">Reset Filters</button>
                    </div>
                </div>
            </div>

            <!-- Tab Switcher -->
            <div class="flex gap-6 border-b border-slate-100 px-1">
                <button @click="activeTab = 'jobs'" :class="['pb-2 text-[10px] font-black uppercase tracking-[0.2em] transition-all border-b-2', activeTab === 'jobs' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600']">Unit Audit</button>
                <button @click="activeTab = 'inventory'" :class="['pb-2 text-[10px] font-black uppercase tracking-[0.2em] transition-all border-b-2', activeTab === 'inventory' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600']">Asset Flow</button>
            </div>

            <!-- Content Area -->
            <div v-if="activeTab === 'jobs'" class="space-y-6">
                <!-- Grouping Option -->
                <div class="flex items-center gap-3 px-1">
                    <div class="flex items-center gap-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                        <span>Aggregate By:</span>
                        <select v-model="filters.group_by" @change="applyFilters" class="bg-transparent border-none p-0 text-[10px] font-black text-slate-900 focus:ring-0 cursor-pointer uppercase tracking-widest">
                            <option value="none">Flat Timeline</option>
                            <option value="status">Workflow State</option>
                            <option value="brand">Manufacturer</option>
                            <option value="technician">Specialist Staff</option>
                            <option value="customer">Client Entity</option>
                            <option value="symptom">Symptom Class</option>
                        </select>
                    </div>
                </div>

                <div v-if="groupedData && filters.group_by !== 'none'" class="space-y-6">
                    <div v-for="(groupJobs, groupName) in groupedData" :key="groupName" class="space-y-3">
                        <div class="flex items-center justify-between px-2">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-slate-900 rounded-full"></span>
                                <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">{{ groupName }}</h3>
                                <span class="text-[9px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">{{ groupJobs.length }} Units</span>
                            </div>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                            <table class="w-full text-left text-[11px]">
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="job in groupJobs" :key="job.id" class="group hover:bg-slate-50 cursor-pointer" @click="router.visit(route('jobs.show', job.job_number))">
                                        <td class="px-4 py-3 font-mono text-slate-400 w-24 group-hover:text-slate-900">{{ job.job_number }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="font-black text-slate-900 uppercase">{{ job.brand }} {{ job.model }}</span>
                                                <span class="text-slate-300">/</span>
                                                <span class="text-slate-500 font-bold">{{ job.customer?.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <StatusBadge :status="job.status" size="xs" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                    <table class="w-full text-left text-[11px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="px-4 py-3">Job ID</th>
                                <th class="px-4 py-3">Hardware Matrix / Client Entity</th>
                                <th class="px-4 py-3 text-center">Current State</th>
                                <th class="px-4 py-3 text-center">Staff</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="job in jobs" :key="job.id" class="group hover:bg-slate-50 cursor-pointer transition-colors" @click="router.visit(route('jobs.show', job.job_number))">
                                <td class="px-4 py-4 font-mono text-slate-400 group-hover:text-slate-900">{{ job.job_number }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-black text-slate-900 uppercase tracking-tight">{{ job.brand }} {{ job.model }}</span>
                                        <span class="text-slate-400 text-[10px] font-bold mt-0.5">{{ job.customer?.name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center"><StatusBadge :status="job.status" size="xs" /></td>
                                <td class="px-4 py-4 text-center uppercase text-[9px] font-black text-slate-400 group-hover:text-slate-900">{{ job.technician?.name || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="jobs.length === 0" class="p-20 text-center text-slate-300 text-[11px] font-black uppercase tracking-[0.4em]">Zero analytical points detected</div>
                </div>
            </div>

            <!-- Inventory Tab -->
            <div v-if="activeTab === 'inventory'" class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm animate-fade-in">
                <table class="w-full text-left text-[11px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                            <th class="px-4 py-4">Hardware Asset Name</th>
                            <th class="px-4 py-4">Manufacturer</th>
                            <th class="px-4 py-4 text-center">Flow Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in inventoryReport" :key="item.name" class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 font-black text-slate-900 uppercase">{{ item.name }}</td>
                            <td class="px-4 py-4 text-slate-500 font-bold uppercase text-[10px]">{{ item.brand }}</td>
                            <td class="px-4 py-4 text-center font-mono font-black">
                                <span class="bg-slate-900 text-white px-2 py-0.5 rounded text-[10px]">{{ item.total_qty }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="inventoryReport.length === 0" class="p-20 text-center text-slate-300 text-[11px] font-black uppercase tracking-[0.4em]">Zero asset flows detected</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
