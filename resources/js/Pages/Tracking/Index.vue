<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    results: Array,
    staff: Array,
    customers: Array,
    symptoms: Array,
    parts: Array,
    filters: Object,
});

const showMore = ref(false);
const aggregateBy = ref(props.filters.aggregate_by || 'flat');
const showAggregateMenu = ref(false);
const activeTab = ref(props.filters.category || 'unit');

const filters = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    search: props.filters.search || '',
    status: props.filters.status || '',
    symptom_id: props.filters.symptom_id || '',
    staff_id: props.filters.staff_id || '',
    customer_id: props.filters.customer_id || '',
    brand: props.filters.brand || '',
    model: props.filters.model || '',
    part_id: props.filters.part_id || '',
    category: activeTab.value,
});

const applyFilters = () => {
    filters.value.category = activeTab.value;
    router.get(route('tracking.index'), filters.value, {
        preserveState: true,
        replace: true
    });
};

const setTab = (tab) => {
    activeTab.value = tab;
    applyFilters();
};

const groupedResults = computed(() => {
    if (aggregateBy.value === 'flat') return { 'Flat Timeline': props.results };
    
    return props.results.reduce((groups, item) => {
        let key = 'Other';
        if (aggregateBy.value === 'state') key = item.status;
        if (aggregateBy.value === 'brand') key = item.item_name.split(' ')[0]; // Extract brand
        if (aggregateBy.value === 'staff') key = item.staff;
        if (aggregateBy.value === 'client') key = item.client;
        if (aggregateBy.value === 'symptom') {
            key = (item.symptoms && item.symptoms !== 'N/A' && item.symptoms !== '') ? item.symptoms.split(',')[0] : 'No Reported Issues';
        }
        
        if (!groups[key]) groups[key] = [];
        groups[key].push(item);
        return groups;
    }, {});
});

const exportExcel = () => {
    const params = { ...filters.value, aggregate_by: aggregateBy.value, group_by: aggregateBy.value };
    window.location.href = route('tracking.csv', params);
};
const exportPdf = () => {
    const params = { ...filters.value, aggregate_by: aggregateBy.value, group_by: aggregateBy.value };
    window.location.href = route('tracking.pdf', params);
};

// Close menu on click outside
const closeMenu = () => showAggregateMenu.value = false;

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    try {
        const date = new Date(dateStr);
        return date.toLocaleString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).replace(',', '');
    } catch (e) {
        return dateStr;
    }
};
</script>

<template>
    <Head title="Technical Audit Hub" />

    <AuthenticatedLayout>
        <div @click="closeMenu" class="space-y-6 max-w-[1600px] mx-auto pb-12">
            <!-- Top Filter Bar -->
            <div @click.stop class="bg-white border border-slate-200 rounded p-4 shadow-sm space-y-4">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input v-model="filters.search" type="text" placeholder="Search everything..." class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-md leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-900 focus:border-slate-900 sm:text-sm font-medium">
                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model="filters.start_date" type="date" class="border border-slate-200 rounded-md px-3 py-2 text-sm font-bold">
                        <input v-model="filters.end_date" type="date" class="border border-slate-200 rounded-md px-3 py-2 text-sm font-bold">
                    </div>
                    <select v-model="filters.status" class="border border-slate-200 rounded-md px-4 py-2 text-sm font-bold">
                        <option value="">All Statuses</option>
                        <option value="received">Received</option>
                        <option value="diagnosing">Diagnosing</option>
                        <option value="repairing">Repairing</option>
                        <option value="completed">Completed</option>
                        <option value="delivered">Delivered</option>
                    </select>
                    <select v-model="filters.symptom_id" class="border border-slate-200 rounded-md px-4 py-2 text-sm font-bold">
                        <option value="">All Symptoms</option>
                        <option v-for="s in symptoms" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <button @click="showMore = !showMore" class="text-[10px] font-black uppercase text-slate-400 flex items-center gap-1 hover:text-slate-900">
                        MORE <svg class="w-2.5 h-2.5 transition-transform" :class="{'rotate-180': showMore}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="flex gap-2">
                        <button @click="applyFilters" class="bg-slate-900 text-white px-8 py-2 rounded font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition-all">Analyze</button>
                        <button @click="exportPdf" class="border border-slate-200 text-red-500 px-4 py-2 rounded font-black text-[10px] uppercase hover:bg-red-50 transition-all">PDF</button>
                        <button @click="exportExcel" class="border border-slate-200 text-emerald-600 px-4 py-2 rounded font-black text-[10px] uppercase hover:bg-emerald-50 transition-all">Excel</button>
                    </div>
                </div>

                <!-- Expanded Filters -->
                <div v-if="showMore" class="grid grid-cols-5 gap-4 pt-4 border-t border-slate-50 animate-slide-down">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Staff Specialist</label>
                        <select v-model="filters.staff_id" class="w-full border-slate-200 rounded-md px-3 py-2 text-xs font-bold bg-slate-50">
                            <option value="">All Staff</option>
                            <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Client Entity</label>
                        <select v-model="filters.customer_id" class="w-full border-slate-200 rounded-md px-3 py-2 text-xs font-bold bg-slate-50">
                            <option value="">All Clients</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Manufacturer</label>
                        <input v-model="filters.brand" type="text" placeholder="e.g. Apple" class="w-full border-slate-200 rounded-md px-3 py-2 text-xs font-bold placeholder-slate-300">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Model ID</label>
                        <input v-model="filters.model" type="text" placeholder="e.g. A2633" class="w-full border-slate-200 rounded-md px-3 py-2 text-xs font-bold placeholder-slate-300">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Hardware Asset</label>
                        <select v-model="filters.part_id" class="w-full border-slate-200 rounded-md px-3 py-2 text-xs font-bold bg-slate-50">
                            <option value="">All Parts</option>
                            <option v-for="p in parts" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div @click.stop class="border-b border-slate-200 flex gap-8">
                <button @click="setTab('unit')" :class="activeTab === 'unit' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400'" class="pb-3 px-1 border-b-2 font-black text-xs uppercase tracking-widest transition-all">Unit Audit</button>
                <button @click="setTab('demo')" :class="activeTab === 'demo' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400'" class="pb-3 px-1 border-b-2 font-black text-xs uppercase tracking-widest transition-all">Demo Audit</button>
                <button @click="setTab('gate')" :class="activeTab === 'gate' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400'" class="pb-3 px-1 border-b-2 font-black text-xs uppercase tracking-widest transition-all">Gate Pass</button>
                <button @click="setTab('flow')" :class="activeTab === 'flow' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400'" class="pb-3 px-1 border-b-2 font-black text-xs uppercase tracking-widest transition-all">Flow Audit</button>
            </div>

            <div class="flex justify-between items-center">
                <div class="relative">
                    <button @click.stop="showAggregateMenu = !showAggregateMenu" class="text-[9px] font-black text-slate-400 uppercase tracking-wider flex items-center gap-2 hover:text-slate-900">
                        AGGREGATE BY: <span class="text-slate-900 flex items-center gap-1">{{ aggregateBy.toUpperCase() }} <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></span>
                    </button>
                    <!-- Dropdown Menu -->
                    <div v-if="showAggregateMenu" class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1">
                        <button @click="aggregateBy = 'flat'; showAggregateMenu = false" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                            <span class="mr-2" :class="aggregateBy === 'flat' ? 'opacity-100' : 'opacity-0'">✓</span> Flat Timeline
                        </button>
                        <button @click="aggregateBy = 'state'; showAggregateMenu = false" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                            <span class="mr-2" :class="aggregateBy === 'state' ? 'opacity-100' : 'opacity-0'">✓</span> Workflow State
                        </button>
                        <button @click="aggregateBy = 'brand'; showAggregateMenu = false" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                            <span class="mr-2" :class="aggregateBy === 'brand' ? 'opacity-100' : 'opacity-0'">✓</span> Manufacturer
                        </button>
                        <button @click="aggregateBy = 'staff'; showAggregateMenu = false" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                            <span class="mr-2" :class="aggregateBy === 'staff' ? 'opacity-100' : 'opacity-0'">✓</span> Specialist Staff
                        </button>
                        <button @click="aggregateBy = 'client'; showAggregateMenu = false" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                            <span class="mr-2" :class="aggregateBy === 'client' ? 'opacity-100' : 'opacity-0'">✓</span> Client Entity
                        </button>
                        <button @click="aggregateBy = 'symptom'; showAggregateMenu = false" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                            <span class="mr-2" :class="aggregateBy === 'symptom' ? 'opacity-100' : 'opacity-0'">✓</span> Symptom Class
                        </button>
                    </div>
                </div>
                <h1 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Technical Audit Hub</h1>
            </div>

            <!-- Result Table -->
            <div class="bg-white border border-slate-200 rounded shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Job ID</th>
                            <th class="px-6 py-4">Hardware Matrix / Client Entity</th>
                            <th class="px-6 py-4">Current State</th>
                            <th class="px-6 py-4 text-right">Staff</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template v-for="(groupItems, groupName) in groupedResults" :key="groupName">
                            <!-- Group Header -->
                            <tr v-if="aggregateBy !== 'flat'" class="bg-slate-900 text-white">
                                <td colspan="4" class="px-6 py-2 text-[10px] font-black uppercase tracking-widest">
                                    {{ groupName }} ({{ groupItems.length }} Units)
                                </td>
                            </tr>
                            
                            <tr v-for="(res, idx) in groupItems" :key="res.id" 
                                @click="router.visit(res.url)"
                                class="group hover:bg-slate-50/80 transition-all cursor-pointer">
                                <td class="px-6 py-6 font-mono text-sm tracking-tighter">
                                    <a :href="res.url" class="px-2 py-1 bg-slate-100 rounded text-slate-900 font-black hover:bg-slate-900 hover:text-white transition-all text-[11px]">
                                        {{ res.id }}
                                    </a>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-1">
                                            <div class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ res.item_name }}</div>
                                            <div class="text-[10px] font-bold text-slate-400 mt-1 flex items-center gap-2">
                                                <span class="text-slate-900 uppercase">{{ res.client }}</span>
                                                <span class="opacity-30">|</span>
                                                <span>{{ res.item_sub }}</span>
                                                <span v-if="res.serial !== 'N/A'" class="opacity-30">|</span>
                                                <span v-if="res.serial !== 'N/A'" class="font-mono text-[9px]">SN: {{ res.serial }}</span>
                                            </div>
                                            <div v-if="res.symptoms && res.symptoms !== 'N/A'" class="mt-2 text-[9px] font-bold text-red-400/80 uppercase tracking-wider line-clamp-1">
                                                Issues: {{ res.symptoms }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="inline-flex items-center w-fit px-2 py-0.5 rounded border text-[8px] font-black uppercase tracking-wider bg-white" 
                                            :class="res.status === 'RECEIVED' ? 'text-blue-500 border-blue-100' : 'text-orange-500 border-orange-100'">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="res.status === 'RECEIVED' ? 'bg-blue-500' : 'bg-orange-500'"></span>
                                            {{ res.status }}
                                        </span>
                                        <div class="text-[8px] font-black text-slate-300 uppercase tracking-widest pl-1">
                                            Last Update: {{ formatDate(res.date) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6 text-right">
                                    <div class="text-[10px] font-black text-slate-900 uppercase tracking-wider">{{ res.staff }}</div>
                                    <div class="text-[8px] font-bold text-slate-400 uppercase mt-0.5">{{ res.source }} AGENT</div>
                                </td>
                            </tr>
                        </template>
                        
                        <tr v-if="results.length === 0">
                            <td colspan="4" class="px-6 py-20 text-center">
                                <div class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">No Data aggregated in current matrix</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-slide-down {
    animation: slideDown 0.2s ease-out;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
