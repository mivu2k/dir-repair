<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    results: Array,
    staff: Array,
    customers: Array,
    symptoms: Array,
    parts: Array,
    brands: Array,
    models: Array,
    filters: Object,
});

const aggregateBy = ref(props.filters.aggregate_by || 'flat');
const showAggregateMenu = ref(false);
const activeTab = ref(props.filters.category || 'unit');

// Drawers State
const isFilterDrawerOpen = ref(false);
const isColumnsDrawerOpen = ref(false);

// Active Columns Visibility
const visibleColumns = ref({
    matrix: true,
    state: true,
    staff: true,
    symptoms: false,
    date: true,
    source: false,
    brand: false,
    model: false,
    serial: false,
    accessories: false,
    dept: false
});

// Custom Filter Form State
const customFilterForm = ref({
    name: props.filters.name || '',
    category: activeTab.value,
    status: props.filters.status || '',
    statuses: Array.isArray(props.filters.statuses) ? props.filters.statuses : (props.filters.status ? [props.filters.status] : []),
    brand: props.filters.brand || '',
    brands: Array.isArray(props.filters.brands) ? props.filters.brands : (props.filters.brand ? [props.filters.brand] : []),
    model: props.filters.model || '',
    models: Array.isArray(props.filters.models) ? props.filters.models : (props.filters.model ? [props.filters.model] : []),
    staff_id: props.filters.staff_id || '',
    staff_ids: Array.isArray(props.filters.staff_ids) ? props.filters.staff_ids : (props.filters.staff_id ? [props.filters.staff_id] : []),
    customer_id: props.filters.customer_id || '',
    customer_ids: Array.isArray(props.filters.customer_ids) ? props.filters.customer_ids : (props.filters.customer_id ? [props.filters.customer_id] : []),
    symptom_id: props.filters.symptom_id || '',
    symptom_ids: Array.isArray(props.filters.symptom_ids) ? props.filters.symptom_ids : (props.filters.symptom_id ? [props.filters.symptom_id] : []),
    part_id: props.filters.part_id || '',
    part_ids: Array.isArray(props.filters.part_ids) ? props.filters.part_ids : (props.filters.part_id ? [props.filters.part_id] : []),
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    search: props.filters.search || '',
});

// Preset Standard Filters
const showFilterSetDropdown = ref(false);

const applyCustomFilters = () => {
    isFilterDrawerOpen.value = false;
    router.get(route('tracking.index'), {
        ...customFilterForm.value,
        category: activeTab.value,
        aggregate_by: aggregateBy.value
    }, {
        preserveState: true,
        replace: true
    });
};

const applyFilterPreset = (categoryName) => {
    showFilterSetDropdown.value = false;
    activeTab.value = categoryName;
    customFilterForm.value.category = categoryName;
    router.get(route('tracking.index'), {
        category: categoryName,
        aggregate_by: aggregateBy.value
    }, {
        preserveState: true,
        replace: true
    });
};

const resetFilters = () => {
    customFilterForm.value = {
        name: '',
        category: 'unit',
        status: '',
        statuses: [],
        brand: '',
        brands: [],
        model: '',
        models: [],
        staff_id: '',
        staff_ids: [],
        customer_id: '',
        customer_ids: [],
        symptom_id: '',
        symptom_ids: [],
        part_id: '',
        part_ids: [],
        start_date: '',
        end_date: '',
        search: '',
    };
    applyCustomFilters();
};

const applyAggregation = (type) => {
    aggregateBy.value = type;
    showAggregateMenu.value = false;
    router.get(route('tracking.index'), {
        ...customFilterForm.value,
        category: activeTab.value,
        aggregate_by: type
    }, {
        preserveState: true,
        replace: true
    });
};

const groupedResults = computed(() => {
    if (aggregateBy.value === 'flat') return { 'Flat Timeline': props.results };
    
    return props.results.reduce((groups, item) => {
        let key = 'Other';
        if (aggregateBy.value === 'state') key = item.status || 'UNSPECIFIED';
        if (aggregateBy.value === 'brand') key = item.item_name ? item.item_name.split(' ')[0] : 'UNKNOWN';
        if (aggregateBy.value === 'staff') key = item.staff || 'UNASSIGNED';
        if (aggregateBy.value === 'client') key = item.client || 'UNKNOWN';
        if (aggregateBy.value === 'symptom') {
            key = (item.symptoms && item.symptoms !== 'N/A' && item.symptoms !== '') ? item.symptoms.split(',')[0] : 'No Reported Issues';
        }
        
        if (!groups[key]) groups[key] = [];
        groups[key].push(item);
        return groups;
    }, {});
});

const exportExcel = () => {
    const params = { 
        ...customFilterForm.value, 
        category: activeTab.value,
        aggregate_by: aggregateBy.value, 
        group_by: aggregateBy.value 
    };
    window.location.href = route('tracking.csv', params);
};

const exportPdf = () => {
    const params = { 
        ...customFilterForm.value, 
        category: activeTab.value,
        aggregate_by: aggregateBy.value, 
        group_by: aggregateBy.value 
    };
    window.location.href = route('tracking.pdf', params);
};

const resetColumns = () => {
    visibleColumns.value = {
        matrix: true,
        state: true,
        staff: true,
        symptoms: false,
        date: true,
        source: false,
        brand: false,
        model: false,
        serial: false,
        accessories: false,
        dept: false
    };
    isColumnsDrawerOpen.value = false;
};

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

const getCategoryLabel = (cat) => {
    if (cat === 'unit') return 'Service Audits';
    if (cat === 'demo') return 'Demo Trials';
    if (cat === 'gate') return 'Gate Clearances';
    if (cat === 'flow') return 'All Movements';
    return cat;
};
</script>

<template>
    <Head title="Technical Audit Hub" />

    <AuthenticatedLayout>
        <div class="max-w-[1600px] mx-auto pb-12 select-none">
            <!-- Breadcrumbs mirroring M365 top -->
            <div class="flex items-center gap-1.5 text-[11px] text-[#605e5c] font-semibold mb-2">
                <span class="hover:underline cursor-pointer">Home</span>
                <span>&gt;</span>
                <span class="text-[#323130]">Technical Audit Hub</span>
            </div>

            <!-- Page Title -->
            <div class="mb-5">
                <h1 class="text-xl font-extrabold text-[#201f1e] leading-none mb-1">Technical Audit Hub</h1>
                <p class="text-xs text-[#605e5c] font-semibold">Analyze, filter, and audit product inventory flows, diagnostic metrics, and gate logs.</p>
            </div>

            <!-- M365 Action Toolbar (Header Buttons) -->
            <div class="bg-white border border-[#e1dfdd] border-b-0 p-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3 select-none">
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <!-- Inline Search field -->
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input 
                            v-model="customFilterForm.search" 
                            @keyup.enter="applyCustomFilters"
                            type="text" 
                            placeholder="Search in scope..." 
                            style="padding-left: 2.25rem !important;"
                            class="block w-full pr-3 py-1.5 border border-[#a19f9d] rounded-sm text-xs font-semibold bg-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#0078d4] focus:border-[#0078d4]"
                        >
                    </div>

                    <button 
                        @click="isFilterDrawerOpen = true"
                        class="flex items-center gap-1.5 text-xs font-semibold text-[#0078d4] hover:text-[#005a9e] transition-colors py-1.5 px-3 rounded hover:bg-slate-100"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Custom Filter
                    </button>

                    <button 
                        @click="isColumnsDrawerOpen = true"
                        class="flex items-center gap-1.5 text-xs font-semibold text-[#0078d4] hover:text-[#005a9e] transition-colors py-1.5 px-3 rounded hover:bg-slate-100"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 002-2h-2a2 2 0 00-2 2" />
                        </svg>
                        Choose columns
                    </button>

                    <!-- M365 Aggregate Dropdown -->
                    <div class="relative">
                        <button 
                            @click="showAggregateMenu = !showAggregateMenu"
                            class="flex items-center gap-1.5 text-xs font-semibold text-[#0078d4] hover:text-[#005a9e] transition-colors py-1.5 px-3 rounded hover:bg-slate-100"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Aggregate ({{ aggregateBy.toUpperCase() }})
                            <svg class="w-2.5 h-2.5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                        </button>

                        <div v-if="showAggregateMenu" class="absolute left-0 mt-1 z-30 w-48 rounded shadow-lg bg-white border border-[#e1dfdd] py-1 animate-slide-down">
                            <button @click="applyAggregation('flat')" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                <span class="mr-2" :class="aggregateBy === 'flat' ? 'text-[#0078d4]' : 'opacity-0'">✓</span> Flat Timeline
                            </button>
                            <button @click="applyAggregation('state')" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                <span class="mr-2" :class="aggregateBy === 'state' ? 'text-[#0078d4]' : 'opacity-0'">✓</span> Workflow State
                            </button>
                            <button @click="applyAggregation('brand')" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                <span class="mr-2" :class="aggregateBy === 'brand' ? 'text-[#0078d4]' : 'opacity-0'">✓</span> Manufacturer
                            </button>
                            <button @click="applyAggregation('staff')" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                <span class="mr-2" :class="aggregateBy === 'staff' ? 'text-[#0078d4]' : 'opacity-0'">✓</span> Specialist Staff
                            </button>
                            <button @click="applyAggregation('client')" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                <span class="mr-2" :class="aggregateBy === 'client' ? 'text-[#0078d4]' : 'opacity-0'">✓</span> Client Entity
                            </button>
                            <button @click="applyAggregation('symptom')" class="flex items-center w-full px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                <span class="mr-2" :class="aggregateBy === 'symptom' ? 'text-[#0078d4]' : 'opacity-0'">✓</span> Symptom Class
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="flex items-center gap-2 justify-end w-full md:w-auto border-t border-[#e1dfdd] pt-2 md:border-t-0 md:pt-0">
                    <button 
                        @click="exportPdf"
                        class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-red-700 transition-colors py-1.5 px-3 rounded hover:bg-red-50"
                    >
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Export to PDF
                    </button>

                    <button 
                        @click="exportExcel"
                        class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-emerald-700 transition-colors py-1.5 px-3 rounded hover:bg-emerald-50"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export to Excel
                    </button>
                </div>
            </div>

            <!-- M365 Filter Set & Custom Presets Row -->
            <div class="bg-white border border-[#e1dfdd] p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Swipe-scroll wrapper for controls on mobile -->
                <div class="flex flex-nowrap items-center gap-2.5 overflow-x-auto pb-1.5 -mx-3 px-3 scrollbar-none sm:overflow-visible sm:pb-0 sm:mx-0 sm:px-0">
                    <!-- Filter Set Button Dropdown -->
                    <div class="relative flex-shrink-0">
                        <button 
                            @click="showFilterSetDropdown = !showFilterSetDropdown"
                            class="flex items-center gap-1.5 text-xs font-bold text-slate-700 bg-white border border-[#a19f9d] rounded px-3 py-1.5 shadow-xs hover:bg-[#f3f2f1] select-none"
                        >
                            Filter set: <span class="text-[#0078d4] font-black capitalize">{{ getCategoryLabel(activeTab) }}</span>
                            <svg class="w-2.5 h-2.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                        </button>

                        <!-- Filter Set Submenu -->
                        <div v-if="showFilterSetDropdown" class="absolute left-0 mt-1 z-30 w-52 rounded shadow-lg bg-white border border-[#e1dfdd] py-1 animate-slide-down">
                            <button @click="isFilterDrawerOpen = true; showFilterSetDropdown = false" class="flex items-center w-full px-4 py-2.5 text-xs font-semibold text-[#0078d4] hover:bg-slate-100 border-b border-slate-100">
                                <span class="mr-2 font-bold">+</span> New filter
                            </button>
                            
                            <div class="px-4 py-1 text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Standard filters set</div>
                            <button @click="applyFilterPreset('unit')" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 pl-8">
                                Service Audits
                            </button>
                            <button @click="applyFilterPreset('demo')" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 pl-8">
                                Demo Trials
                            </button>
                            <button @click="applyFilterPreset('gate')" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 pl-8">
                                Gate Clearances
                            </button>
                            <button @click="applyFilterPreset('flow')" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 pl-8">
                                All Movements
                            </button>
                        </div>
                    </div>

                    <!-- Active Filter Presets Pills -->
                    <button 
                        @click="applyFilterPreset('unit')"
                        :class="[
                            'text-xs font-semibold px-3 py-1.5 rounded-full border transition-all select-none flex-shrink-0',
                            activeTab === 'unit' ? 'bg-[#0078d4] text-white border-[#0078d4]' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'
                        ]"
                    >
                        Service Audits
                    </button>

                    <button 
                        @click="applyFilterPreset('demo')"
                        :class="[
                            'text-xs font-semibold px-3 py-1.5 rounded-full border transition-all select-none flex-shrink-0',
                            activeTab === 'demo' ? 'bg-[#0078d4] text-white border-[#0078d4]' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'
                        ]"
                    >
                        Demo Trials
                    </button>

                    <button 
                        @click="applyFilterPreset('gate')"
                        :class="[
                            'text-xs font-semibold px-3 py-1.5 rounded-full border transition-all select-none flex-shrink-0',
                            activeTab === 'gate' ? 'bg-[#0078d4] text-white border-[#0078d4]' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'
                        ]"
                    >
                        Gate Clearances
                    </button>

                    <button 
                        @click="applyFilterPreset('flow')"
                        :class="[
                            'text-xs font-semibold px-3 py-1.5 rounded-full border transition-all select-none flex-shrink-0',
                            activeTab === 'flow' ? 'bg-[#0078d4] text-white border-[#0078d4]' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'
                        ]"
                    >
                        All Movements
                    </button>
                </div>

                <!-- Reset Active filters button -->
                <button 
                    v-if="props.filters.search || props.filters.status || (props.filters.statuses && props.filters.statuses.length > 0) || props.filters.brand || (props.filters.brands && props.filters.brands.length > 0) || props.filters.model || (props.filters.models && props.filters.models.length > 0) || props.filters.staff_id || (props.filters.staff_ids && props.filters.staff_ids.length > 0) || props.filters.customer_id || (props.filters.customer_ids && props.filters.customer_ids.length > 0) || props.filters.symptom_id || (props.filters.symptom_ids && props.filters.symptom_ids.length > 0) || props.filters.part_id || (props.filters.part_ids && props.filters.part_ids.length > 0) || props.filters.start_date"
                    @click="resetFilters"
                    class="text-xs font-bold text-red-600 hover:text-red-800 underline transition-colors flex-shrink-0 self-end sm:self-auto ml-0 sm:ml-auto mt-1 sm:mt-0"
                >
                    Clear custom filters
                </button>
            </div>

            <!-- M365 Data Grid Table Wrapper -->
            <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden">
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-xs min-w-[900px] sm:min-w-full">
                        <thead class="bg-slate-50 border-b border-[#e1dfdd] text-[#605e5c] uppercase font-bold tracking-wide">
                            <tr>
                                <th class="w-8 px-4 py-3 select-none">
                                    <input type="checkbox" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                </th>
                                <th class="px-4 py-3 select-none">Job ID / Reference</th>
                                <th v-if="visibleColumns.brand" class="px-4 py-3 select-none">Manufacturer</th>
                                <th v-if="visibleColumns.model" class="px-4 py-3 select-none">Model ID</th>
                                <th v-if="visibleColumns.serial" class="px-4 py-3 select-none">Serial Number</th>
                                <th v-if="visibleColumns.matrix" class="px-4 py-3 select-none">Hardware Matrix / Client Entity</th>
                                <th v-if="visibleColumns.accessories" class="px-4 py-3 select-none">Accessories</th>
                                <th v-if="visibleColumns.dept" class="px-4 py-3 select-none">Department / Ref</th>
                                <th v-if="visibleColumns.state" class="px-4 py-3 select-none">Current State</th>
                                <th v-if="visibleColumns.staff" class="px-4 py-3 select-none">Staff Specialist</th>
                                <th v-if="visibleColumns.symptoms" class="px-4 py-3 select-none">Symptoms / Issues</th>
                                <th v-if="visibleColumns.date" class="px-4 py-3 select-none">Last Updated</th>
                                <th v-if="visibleColumns.source" class="px-4 py-3 select-none">Source Agent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f3f2f1]">
                            <template v-for="(groupItems, groupName) in groupedResults" :key="groupName">
                                <!-- Aggregate Group Header -->
                                <tr v-if="aggregateBy !== 'flat'" class="bg-[#f3f2f1] text-[#323130]">
                                    <td class="px-4 py-2.5"></td>
                                    <td colspan="15" class="px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-widest text-slate-700">
                                        {{ groupName }} ({{ groupItems.length }} batches/units)
                                    </td>
                                </tr>
                                
                                <tr 
                                    v-for="res in groupItems" 
                                    :key="res.id" 
                                    @click="router.visit(res.url)"
                                    class="hover:bg-[#f3f2f1]/50 cursor-pointer transition-all duration-75 select-none"
                                >
                                    <td class="px-4 py-3" @click.stop>
                                        <input type="checkbox" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                    </td>
                                    <td class="px-4 py-3 font-mono font-extrabold text-[#201f1e]">
                                        <span class="hover:underline text-[#0078d4]">{{ res.id }}</span>
                                    </td>
                                    <td v-if="visibleColumns.brand" class="px-4 py-3 font-bold text-slate-700 uppercase tracking-tight">
                                        {{ res.brand_name }}
                                    </td>
                                    <td v-if="visibleColumns.model" class="px-4 py-3 font-semibold text-slate-600">
                                        {{ res.model_name }}
                                    </td>
                                    <td v-if="visibleColumns.serial" class="px-4 py-3 font-mono text-[11px] font-bold text-slate-500">
                                        {{ res.serial }}
                                    </td>
                                    <td v-if="visibleColumns.matrix" class="px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="font-extrabold text-[#323130] uppercase text-[12px]">{{ res.item_name }}</span>
                                            <div class="flex items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                                                <span class="font-bold text-[#605e5c] uppercase">{{ res.client }}</span>
                                                <span class="opacity-30">|</span>
                                                <span>{{ res.item_sub }}</span>
                                                <span v-if="res.serial !== 'N/A'" class="opacity-30">|</span>
                                                <span v-if="res.serial !== 'N/A'" class="font-mono text-[9px] font-semibold text-slate-400">SN: {{ res.serial }}</span>
                                            </div>
                                            <!-- Nested Issues preview -->
                                            <div v-if="res.symptoms && res.symptoms !== 'N/A'" class="text-[9px] font-extrabold text-red-500 uppercase tracking-wide mt-1">
                                                Diagnostic Faults: {{ res.symptoms }}
                                            </div>
                                        </div>
                                    </td>
                                    <td v-if="visibleColumns.accessories" class="px-4 py-3 text-slate-500 max-w-[200px] truncate">
                                        {{ res.accessories }}
                                    </td>
                                    <td v-if="visibleColumns.dept" class="px-4 py-3 text-slate-600 font-semibold uppercase text-[11px]">
                                        {{ res.dept }}
                                    </td>
                                    <td v-if="visibleColumns.state" class="px-4 py-3">
                                        <div class="flex flex-col gap-1">
                                            <span 
                                                class="inline-flex items-center w-fit px-2 py-0.5 rounded border text-[9px] font-extrabold uppercase tracking-wider bg-white"
                                                :class="[
                                                    ['RECEIVED', 'INWARD', 'PENDING'].includes(res.status) ? 'text-blue-600 border-blue-200 bg-blue-50/10' :
                                                    ['COMPLETED', 'DELIVERED', 'RETURNED', 'APPROVED'].includes(res.status) ? 'text-emerald-700 border-emerald-200 bg-emerald-50/10' :
                                                    'text-amber-700 border-amber-200 bg-amber-50/10'
                                                ]"
                                            >
                                                <span 
                                                    class="w-1.5 h-1.5 rounded-full mr-1.5" 
                                                    :class="[
                                                        ['RECEIVED', 'INWARD', 'PENDING'].includes(res.status) ? 'bg-blue-600' :
                                                        ['COMPLETED', 'DELIVERED', 'RETURNED', 'APPROVED'].includes(res.status) ? 'bg-emerald-700' :
                                                        'bg-amber-600'
                                                    ]"
                                                ></span>
                                                {{ res.status }}
                                            </span>
                                        </div>
                                    </td>
                                    <td v-if="visibleColumns.staff" class="px-4 py-3 font-bold text-slate-700 uppercase tracking-tighter">
                                        {{ res.staff }}
                                    </td>
                                    <td v-if="visibleColumns.symptoms" class="px-4 py-3 text-[11px] text-slate-500 max-w-[200px] truncate">
                                        {{ res.symptoms || '-' }}
                                    </td>
                                    <td v-if="visibleColumns.date" class="px-4 py-3 text-slate-400 font-mono font-medium">
                                        {{ formatDate(res.date) }}
                                    </td>
                                    <td v-if="visibleColumns.source" class="px-4 py-3">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-sm bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-tight">{{ res.source }}</span>
                                    </td>
                                </tr>
                            </template>

                            <tr v-if="results.length === 0">
                                <td colspan="15" class="px-6 py-20 text-center select-none">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Zero Technical entries compiled in tracking scope</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary (Batches Total) -->
                <div class="px-4 py-3 bg-[#fafafa] border-t border-[#e1dfdd] text-[10px] font-bold text-[#605e5c] uppercase tracking-wider">
                    Total Compiled Data scope: {{ results.length }} Audit elements loaded
                </div>
            </div>
        </div>

        <!-- Custom Filter Side Drawer (Right Alignment) -->
        <div v-if="isFilterDrawerOpen" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <!-- Overlay Drawer Backplate -->
                <div @click="isFilterDrawerOpen = false" class="absolute inset-0 bg-slate-950/40 backdrop-blur-xs transition-opacity duration-300"></div>

                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="pointer-events-auto w-screen max-w-md transform transition-all duration-200">
                        <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-2xl border-l border-[#e1dfdd]">
                            <!-- Drawer Title Header -->
                            <div class="px-6 py-5 border-b border-[#e1dfdd] flex items-center justify-between">
                                <h2 class="text-md font-extrabold text-[#201f1e]">Custom filter</h2>
                                <button @click="isFilterDrawerOpen = false" class="w-8 h-8 rounded hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-900 focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <!-- Drawer Form Fields -->
                            <div class="flex-1 px-6 py-6 space-y-4">
                                <p class="text-xs text-slate-500 font-semibold mb-4">Choose the conditions for your custom filter</p>
                                
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-[#201f1e]">Name your filter *</label>
                                    <input v-model="customFilterForm.name" type="text" placeholder="Enter filter name" class="block w-full border border-[#a19f9d] rounded-sm text-xs font-semibold px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0078d4] focus:border-[#0078d4]">
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-[#201f1e]">Workflow State / Status</label>
                                    <div class="border border-[#a19f9d] rounded-sm bg-white p-2.5 h-36 overflow-y-auto space-y-2.5 shadow-sm fluent-scrollbar">
                                        <!-- Service Job Statuses -->
                                        <div>
                                            <div class="text-[9px] font-black text-[#0078d4] uppercase tracking-wider mb-1">Service & Repair</div>
                                            <div class="grid grid-cols-1 gap-1.5 pl-1">
                                                <div v-for="st in [
                                                    { value: 'received', label: 'Received' },
                                                    { value: 'diagnosing', label: 'Diagnosing' },
                                                    { value: 'repairing', label: 'Repairing' },
                                                    { value: 'completed', label: 'Completed' },
                                                    { value: 'delivered', label: 'Delivered' }
                                                ]" :key="st.value" class="flex items-center gap-2.5">
                                                    <input type="checkbox" :id="'status-chk-' + st.value" :value="st.value" v-model="customFilterForm.statuses" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4] cursor-pointer">
                                                    <label :for="'status-chk-' + st.value" class="text-xs font-semibold text-slate-700 select-none cursor-pointer">{{ st.label }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Demo Statuses -->
                                        <div>
                                            <div class="text-[9px] font-black text-[#0078d4] uppercase tracking-wider mb-1">Demo & Trial</div>
                                            <div class="grid grid-cols-1 gap-1.5 pl-1">
                                                <div v-for="st in [
                                                    { value: 'pending', label: 'Pending' },
                                                    { value: 'issued', label: 'Issued' },
                                                    { value: 'returned', label: 'Returned' },
                                                    { value: 'cancelled', label: 'Cancelled' }
                                                ]" :key="st.value" class="flex items-center gap-2.5">
                                                    <input type="checkbox" :id="'status-chk-' + st.value" :value="st.value" v-model="customFilterForm.statuses" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4] cursor-pointer">
                                                    <label :for="'status-chk-' + st.value" class="text-xs font-semibold text-slate-700 select-none cursor-pointer">{{ st.label }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Gate Statuses -->
                                        <div>
                                            <div class="text-[9px] font-black text-[#0078d4] uppercase tracking-wider mb-1">Gate Pass</div>
                                            <div class="grid grid-cols-1 gap-1.5 pl-1">
                                                <div v-for="st in [
                                                    { value: 'approved', label: 'Approved' }
                                                ]" :key="st.value" class="flex items-center gap-2.5">
                                                    <input type="checkbox" :id="'status-chk-' + st.value" :value="st.value" v-model="customFilterForm.statuses" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4] cursor-pointer">
                                                    <label :for="'status-chk-' + st.value" class="text-xs font-semibold text-slate-700 select-none cursor-pointer">{{ st.label }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3.5">
                                    <!-- Manufacturer -->
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-[#201f1e]">Manufacturer</label>
                                        <div class="border border-[#a19f9d] rounded-sm bg-white p-2 h-28 overflow-y-auto space-y-1.5 shadow-sm fluent-scrollbar">
                                            <div v-for="b in brands" :key="b" class="flex items-center gap-2.5">
                                                <input 
                                                    type="checkbox" 
                                                    :id="'brand-chk-' + b" 
                                                    :value="b" 
                                                    v-model="customFilterForm.brands"
                                                    class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4] cursor-pointer"
                                                >
                                                <label :for="'brand-chk-' + b" class="text-[10px] font-semibold text-slate-700 select-none cursor-pointer leading-tight">{{ b }}</label>
                                            </div>
                                            <div v-if="brands.length === 0" class="text-[9px] text-slate-400 italic text-center py-2">None</div>
                                        </div>
                                    </div>

                                    <!-- Model ID -->
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-[#201f1e]">Model ID</label>
                                        <div class="border border-[#a19f9d] rounded-sm bg-white p-2 h-28 overflow-y-auto space-y-1.5 shadow-sm fluent-scrollbar">
                                            <div v-for="m in models" :key="m" class="flex items-center gap-2.5">
                                                <input 
                                                    type="checkbox" 
                                                    :id="'model-chk-' + m" 
                                                    :value="m" 
                                                    v-model="customFilterForm.models"
                                                    class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4] cursor-pointer"
                                                >
                                                <label :for="'model-chk-' + m" class="text-[10px] font-semibold text-slate-700 select-none cursor-pointer leading-tight">{{ m }}</label>
                                            </div>
                                            <div v-if="models.length === 0" class="text-[9px] text-slate-400 italic text-center py-2">None</div>
                                        </div>
                                    </div>

                                    <!-- Staff Specialist -->
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-[#201f1e]">Staff Specialist</label>
                                        <div class="border border-[#a19f9d] rounded-sm bg-white p-2 h-28 overflow-y-auto space-y-1.5 shadow-sm fluent-scrollbar">
                                            <div v-for="s in staff" :key="s.id" class="flex items-center gap-2.5">
                                                <input 
                                                    type="checkbox" 
                                                    :id="'staff-chk-' + s.id" 
                                                    :value="s.id" 
                                                    v-model="customFilterForm.staff_ids"
                                                    class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4] cursor-pointer"
                                                >
                                                <label :for="'staff-chk-' + s.id" class="text-[10px] font-semibold text-slate-700 select-none cursor-pointer leading-tight">{{ s.name }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Client Entity -->
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-[#201f1e]">Client Entity</label>
                                        <div class="border border-[#a19f9d] rounded-sm bg-white p-2 h-28 overflow-y-auto space-y-1.5 shadow-sm fluent-scrollbar">
                                            <div v-for="c in customers" :key="c.id" class="flex items-center gap-2.5">
                                                <input 
                                                    type="checkbox" 
                                                    :id="'customer-chk-' + c.id" 
                                                    :value="c.id" 
                                                    v-model="customFilterForm.customer_ids"
                                                    class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4] cursor-pointer"
                                                >
                                                <label :for="'customer-chk-' + c.id" class="text-[10px] font-semibold text-slate-700 select-none cursor-pointer leading-tight">{{ c.name }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Symptom Class -->
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-[#201f1e]">Symptom Class</label>
                                        <div class="border border-[#a19f9d] rounded-sm bg-white p-2 h-28 overflow-y-auto space-y-1.5 shadow-sm fluent-scrollbar">
                                            <div v-for="s in symptoms" :key="s.id" class="flex items-center gap-2.5">
                                                <input 
                                                    type="checkbox" 
                                                    :id="'symptom-chk-' + s.id" 
                                                    :value="s.id" 
                                                    v-model="customFilterForm.symptom_ids"
                                                    class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4] cursor-pointer"
                                                >
                                                <label :for="'symptom-chk-' + s.id" class="text-[10px] font-semibold text-slate-700 select-none cursor-pointer leading-tight">{{ s.name }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hardware Asset -->
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-[#201f1e]">Hardware Asset</label>
                                        <div class="border border-[#a19f9d] rounded-sm bg-white p-2 h-28 overflow-y-auto space-y-1.5 shadow-sm fluent-scrollbar">
                                            <div v-for="p in parts" :key="p.id" class="flex items-center gap-2.5">
                                                <input 
                                                    type="checkbox" 
                                                    :id="'part-chk-' + p.id" 
                                                    :value="p.id" 
                                                    v-model="customFilterForm.part_ids"
                                                    class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4] cursor-pointer"
                                                >
                                                <label :for="'part-chk-' + p.id" class="text-[10px] font-semibold text-slate-700 select-none cursor-pointer leading-tight">{{ p.name }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 pt-2">
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-[#201f1e]">Start Date</label>
                                        <input v-model="customFilterForm.start_date" type="date" class="block w-full border border-[#a19f9d] rounded-sm text-xs font-semibold px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0078d4] focus:border-[#0078d4]">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-[#201f1e]">End Date</label>
                                        <input v-model="customFilterForm.end_date" type="date" class="block w-full border border-[#a19f9d] rounded-sm text-xs font-semibold px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0078d4] focus:border-[#0078d4]">
                                    </div>
                                </div>
                            </div>

                            <!-- Drawer Buttons -->
                            <div class="px-6 py-4 bg-slate-50 border-t border-[#e1dfdd] flex items-center gap-3">
                                <button @click="applyCustomFilters" class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-2 rounded-sm text-xs font-bold shadow-sm transition-all">Add</button>
                                <button @click="isFilterDrawerOpen = false" class="bg-white border border-[#a19f9d] hover:bg-[#f3f2f1] text-[#323130] px-5 py-2 rounded-sm text-xs font-bold transition-all">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Choose Columns Side Drawer (Right Alignment) -->
        <div v-if="isColumnsDrawerOpen" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <!-- Overlay Backplate -->
                <div @click="isColumnsDrawerOpen = false" class="absolute inset-0 bg-slate-950/40 backdrop-blur-xs transition-opacity duration-300"></div>

                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="pointer-events-auto w-screen max-w-md transform transition-all duration-200">
                        <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-2xl border-l border-[#e1dfdd]">
                            <!-- Drawer Title Header -->
                            <div class="px-6 py-5 border-b border-[#e1dfdd] flex items-center justify-between">
                                <h2 class="text-md font-extrabold text-[#201f1e]">Choose columns</h2>
                                <button @click="isColumnsDrawerOpen = false" class="w-8 h-8 rounded hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-900 focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <!-- Column List Toggles -->
                            <div class="flex-1 px-6 py-6 space-y-4">
                                <p class="text-xs text-slate-500 font-semibold mb-4">Select the columns to display in your technical audit list</p>
                                
                                <div class="space-y-3.5">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-id-opt" checked disabled class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-id-opt" class="text-xs font-bold text-slate-400 select-none">Job ID / Reference (Mandatory)</label>
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-brand-opt" v-model="visibleColumns.brand" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-brand-opt" class="text-xs font-bold text-[#201f1e] select-none">Manufacturer</label>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-model-opt" v-model="visibleColumns.model" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-model-opt" class="text-xs font-bold text-[#201f1e] select-none">Model ID</label>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-serial-opt" v-model="visibleColumns.serial" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-serial-opt" class="text-xs font-bold text-[#201f1e] select-none">Serial Number</label>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-matrix-opt" v-model="visibleColumns.matrix" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-matrix-opt" class="text-xs font-bold text-[#201f1e] select-none">Hardware Matrix / Client Entity</label>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-accessories-opt" v-model="visibleColumns.accessories" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-accessories-opt" class="text-xs font-bold text-[#201f1e] select-none">Accessories</label>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-dept-opt" v-model="visibleColumns.dept" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-dept-opt" class="text-xs font-bold text-[#201f1e] select-none">Department / Reference</label>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-state-opt" v-model="visibleColumns.state" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-state-opt" class="text-xs font-bold text-[#201f1e] select-none">Current State / Status</label>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-staff-opt" v-model="visibleColumns.staff" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-staff-opt" class="text-xs font-bold text-[#201f1e] select-none">Staff Specialist</label>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-symptoms-opt" v-model="visibleColumns.symptoms" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-symptoms-opt" class="text-xs font-bold text-[#201f1e] select-none">Symptoms / Issues</label>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-date-opt" v-model="visibleColumns.date" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-date-opt" class="text-xs font-bold text-[#201f1e] select-none">Last Updated Timestamp</label>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" id="col-source-opt" v-model="visibleColumns.source" class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4]">
                                        <label for="col-source-opt" class="text-xs font-bold text-[#201f1e] select-none">Source System Agent</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Drawer Buttons -->
                            <div class="px-6 py-4 bg-slate-50 border-t border-[#e1dfdd] flex items-center gap-3">
                                <button @click="isColumnsDrawerOpen = false" class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-2 rounded-sm text-xs font-bold shadow-sm transition-all">Save</button>
                                <button @click="resetColumns" class="bg-white border border-[#a19f9d] hover:bg-[#f3f2f1] text-[#323130] px-5 py-2 rounded-sm text-xs font-bold transition-all">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-slide-down {
    animation: slideDown 0.25s cubic-bezier(0.1, 0.9, 0.2, 1);
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Sleek custom scrollbars mirroring M365 Fluent design */
.fluent-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.fluent-scrollbar::-webkit-scrollbar-track {
    background: #fafafa;
}
.fluent-scrollbar::-webkit-scrollbar-thumb {
    background: #c8c6c4;
    border-radius: 3px;
}
.fluent-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a19f9d;
}

/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-none {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>
