<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    quotations: Object,
    filters: Object,
    initialJob: Object,
    initialIntake: Object,
    suggestedItems: Array,
    inventoryParts: Array,
});

const isClosing = ref(false);
const search = ref(props.filters.search || '');
const currentStep = ref('basics'); // 'basics' | 'items' | 'finish'
const showInventory = ref(false);

const form = useForm({
    repair_job_id: props.initialJob?.id || '',
    intake_id: props.initialIntake?.id || '',
    date: new Date().toISOString().split('T')[0],
    valid_until: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    reference: '',
    subject: '',
    notes: 'Quotation valid for 30 days.',
    items: props.suggestedItems.length > 0 
        ? JSON.parse(JSON.stringify(props.suggestedItems)).map(item => ({ 
            item_type: item.item_type || 'part',
            description: item.description || '',
            quantity: item.quantity || 1,
            unit_price: item.unit_price || 0,
            discount: 0, 
            part_id: item.part_id || null, 
            repair_job_id: item.repair_job_id || props.initialJob?.id || '' 
        })) 
        : [{ item_type: 'part', description: '', quantity: 1, unit_price: 0, discount: 0, part_id: null, repair_job_id: props.initialJob?.id || '' }],
});

watch(search, (value) => {
    router.get(route('quotations.create'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
});

const addItem = () => {
    form.items.push({ item_type: 'part', description: '', quantity: 1, unit_price: 0, discount: 0, part_id: null, repair_job_id: props.initialJob?.id || '' });
};

const addPartFromInventory = (part) => {
    form.items.push({
        item_type: 'part',
        description: part.name,
        quantity: 1,
        unit_price: part.price,
        discount: 0,
        part_id: part.id,
        repair_job_id: props.initialJob?.id || '',
    });
    showInventory.value = false;
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.quantity * item.unit_price) - item.discount, 0);
});

const handleClose = () => {
    isClosing.value = true;
    setTimeout(() => {
        router.visit(route('quotations.index'));
    }, 150);
};

const submit = () => {
    form.post(route('quotations.store'));
};

const getJobNumber = (jobId) => {
    if (!jobId) return 'N/A';
    const job = props.initialIntake?.repair_jobs?.find(j => j.id === jobId);
    return job?.job_number || props.initialJob?.job_number || 'Linked';
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        maximumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head title="Build Quotation" />

    <AuthenticatedLayout>
        <!-- Background Page Content (M365 Style Quotations Index) -->
        <div class="space-y-4 opacity-75 pointer-events-none select-none">
            <div class="flex items-center justify-between gap-4">
                <div class="relative flex-1 max-w-sm">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Search quotations..." 
                        class="input-field pl-8"
                        disabled
                    >
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            
            <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-[#e1dfdd]">
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Quote #</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Job #</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Amount</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Ops</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f3f2f1]">
                        <tr v-for="quote in quotations.data" :key="quote.id">
                            <td class="px-3 py-2 font-bold font-mono">{{ quote.quotation_number }}</td>
                            <td class="px-3 py-2 font-mono font-bold">{{ quote.repair_job?.job_number || 'N/A' }}</td>
                            <td class="px-3 py-2 font-bold truncate max-w-[150px]">{{ quote.repair_job?.customer?.name }}</td>
                            <td class="px-3 py-2 text-right font-bold text-blue-600">{{ formatCurrency(quote.total_amount) }}</td>
                            <td class="px-3 py-2">
                                <StatusBadge :status="quote.status" size="xs" />
                            </td>
                            <td class="px-3 py-2 text-right">
                                <span class="text-blue-600 font-bold text-[10px]">Open</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- sliding Drawer overlay (M365 Style) -->
        <div 
            class="fixed inset-0 bg-slate-950/45 z-50 flex justify-end transition-opacity duration-200"
            :class="{ 'opacity-0': isClosing }"
            @click.self="handleClose"
        >
            <div 
                class="w-full md:max-w-4xl lg:max-w-5xl bg-white h-full shadow-2xl flex flex-col transform transition-transform duration-200 ease-out"
                :class="isClosing ? 'translate-x-full' : 'translate-x-0'"
            >
                <!-- Drawer Header -->
                <div class="px-6 py-4 border-b border-[#e1dfdd] flex items-center justify-between bg-white select-none">
                    <div class="flex items-center gap-2">
                        <span class="w-1 bg-[#dc2626] h-5 rounded-sm"></span>
                        <h2 class="text-base font-extrabold text-[#201f1e]">Build Financial Quotation</h2>
                    </div>
                    <button 
                        @click="handleClose"
                        class="w-8 h-8 rounded-sm hover:bg-[#f3f2f1] flex items-center justify-center text-slate-500 hover:text-slate-800 transition-colors"
                    >
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Drawer Body -->
                <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
                    
                    <!-- Left Stepper Panel -->
                    <div class="w-full md:w-56 bg-[#f3f2f1] border-r border-[#e1dfdd] p-6 flex flex-col justify-between select-none">
                        <div class="space-y-6">
                            <!-- Stepper header -->
                            <h3 class="text-xs font-bold text-[#605e5c] uppercase tracking-wider">Quotation Steps</h3>
                            
                            <!-- Timeline steps -->
                            <div class="relative pl-2 space-y-8">
                                <!-- Connecting timeline line -->
                                <div class="absolute left-[21px] top-[10px] bottom-[10px] w-[1px] bg-[#a19f9d]"></div>
                                
                                <!-- Step 1: Basics -->
                                <div class="relative flex items-center gap-4 group cursor-pointer" @click="currentStep = 'basics'">
                                    <div 
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 z-10 transition-all"
                                        :class="[
                                            currentStep === 'basics' 
                                                ? 'bg-[#0078d4] border-[#0078d4] text-white' 
                                                : (form.subject 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="form.subject && currentStep !== 'basics'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>1</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'basics' ? 'text-[#0078d4]' : 'text-[#323130]'">Quote Basics</span>
                                </div>
                                
                                <!-- Step 2: Items -->
                                <div class="relative flex items-center gap-4 group cursor-pointer" @click="currentStep = 'items'">
                                    <div 
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 z-10 transition-all"
                                        :class="[
                                            currentStep === 'items' 
                                                ? 'bg-[#0078d4] border-[#0078d4] text-white' 
                                                : (form.items.length > 0 && form.items[0].description 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="form.items.length > 0 && form.items[0].description && currentStep !== 'items'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>2</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'items' ? 'text-[#0078d4]' : 'text-[#323130]'">Product Matrix</span>
                                </div>
                                
                                <!-- Step 3: Finish -->
                                <div class="relative flex items-center gap-4 group cursor-pointer" @click="currentStep = 'finish'">
                                    <div 
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 z-10 transition-all"
                                        :class="[
                                            currentStep === 'finish' 
                                                ? 'bg-[#0078d4] border-[#0078d4] text-white' 
                                                : 'bg-white border-[#a19f9d] text-slate-600'
                                        ]"
                                    >
                                        <span>3</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'finish' ? 'text-[#0078d4]' : 'text-[#323130]'">Deploy & Notes</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-[10px] text-slate-500 font-semibold leading-relaxed border-t border-[#e1dfdd] pt-4">
                            Deploying pricing allocations for labor, accessories, or parts. Upon approval, job logs automatically update.
                        </div>
                    </div>

                    <!-- Right Form Content Area -->
                    <form @submit.prevent="submit" class="flex-1 flex flex-col overflow-hidden bg-white">
                        <!-- Scrollable Content -->
                        <div class="flex-1 overflow-y-auto p-6 md:p-8">
                        
                        <div>
                            <!-- STEP 1: BASICS -->
                            <div v-if="currentStep === 'basics'" class="space-y-6 animate-slide-up">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Set up quotation basics</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Specify entity bounds, date stamps, and master subject descriptions.</p>
                                </div>

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Master Entity Reference</label>
                                            <div class="text-xs font-bold text-slate-800 bg-slate-50 px-3 py-2 rounded-sm border border-[#e1dfdd] font-mono">
                                                {{ initialJob?.job_number || initialIntake?.intake_number || 'General Matrix' }}
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Subject Reference *</label>
                                            <input type="text" v-model="form.subject" placeholder="e.g. Logic Board Batch repair" class="input-field font-semibold" required>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-[#f3f2f1] pt-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Quotation Date stamp</label>
                                            <input type="date" v-model="form.date" class="input-field">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Validity Limit Date</label>
                                            <input type="date" v-model="form.valid_until" class="input-field">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: ITEMS -->
                            <div v-if="currentStep === 'items'" class="space-y-6 animate-slide-up">
                                <div class="flex justify-between items-center pb-2 border-b border-[#f3f2f1]">
                                    <div>
                                        <h2 class="text-lg font-bold text-[#201f1e] mb-1">Product Pricing Matrix</h2>
                                        <p class="text-xs text-[#605e5c] leading-relaxed">Customize items, units pricing, and individual labor allocations.</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button 
                                            type="button" 
                                            @click="showInventory = !showInventory" 
                                            class="bg-white hover:bg-slate-50 border border-[#e1dfdd] text-[#0078d4] text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-sm shadow-sm transition-all"
                                        >
                                            Inventory Matrix
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="addItem" 
                                            class="bg-[#0078d4] hover:bg-[#005a9e] text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-sm shadow-sm transition-all"
                                        >
                                            Add Row
                                        </button>
                                    </div>
                                </div>

                                <!-- Inventory quick selector slider -->
                                <div v-if="showInventory" class="bg-slate-900 rounded-sm p-4 space-y-3 animate-slide-up select-none">
                                    <div class="flex justify-between items-center border-b border-slate-800 pb-2">
                                        <h4 class="text-[10px] font-extrabold text-white uppercase tracking-wider">Inventory Matrix Assets</h4>
                                        <button @click="showInventory = false" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 overflow-y-auto max-h-[160px]">
                                        <button 
                                            v-for="part in inventoryParts" 
                                            :key="part.id"
                                            type="button"
                                            @click="addPartFromInventory(part)"
                                            class="text-left p-2.5 bg-slate-800 border border-slate-700 rounded-sm hover:border-[#0078d4] hover:bg-slate-750 transition-all group"
                                        >
                                            <div class="text-[10px] font-bold text-white group-hover:text-blue-400 truncate">{{ part.name }}</div>
                                            <div class="flex justify-between items-center mt-1">
                                                <span class="text-[8px] text-slate-500 font-extrabold uppercase">{{ part.brand || 'GENERIC' }}</span>
                                                <span class="text-[10px] font-black text-emerald-400 font-mono">{{ formatCurrency(part.price) }}</span>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div v-for="(item, index) in form.items" :key="index" class="p-4 bg-slate-50 rounded-sm border border-[#e1dfdd] relative">
                                        <button 
                                            v-if="form.items.length > 1" 
                                            type="button" 
                                            @click="removeItem(index)" 
                                            class="absolute top-2.5 right-2.5 text-slate-400 hover:text-red-600 transition-colors"
                                        >
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                        
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3">
                                                <div class="flex-1">
                                                    <label class="block text-[10px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Item Description / Task Naming *</label>
                                                    <input v-model="item.description" type="text" class="input-field bg-white py-1.5 text-xs font-semibold" placeholder="e.g. Logic board IC replacement" required />
                                                </div>
                                                <div v-if="initialIntake" class="w-32">
                                                    <label class="block text-[10px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Linked job</label>
                                                    <select v-model="item.repair_job_id" class="input-field bg-white py-1.5 text-[10px] font-bold uppercase cursor-pointer">
                                                        <option value="">Global/No Job</option>
                                                        <option v-for="job in initialIntake.repair_jobs" :key="job.id" :value="job.id">{{ job.job_number }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                                <div>
                                                    <label class="block text-[9px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Pricing Group</label>
                                                    <select v-model="item.item_type" class="input-field bg-white py-1.5 text-[10px] font-bold uppercase cursor-pointer">
                                                        <option value="part">Hardware</option>
                                                        <option value="labor">Labor</option>
                                                        <option value="misc">Miscellaneous</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-[9px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Quantity *</label>
                                                    <input v-model="item.quantity" type="number" step="0.01" class="input-field bg-white py-1.5 text-xs font-mono font-bold" required />
                                                </div>
                                                <div>
                                                    <label class="block text-[9px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Unit Price *</label>
                                                    <input v-model="item.unit_price" type="number" step="1" class="input-field bg-white py-1.5 text-xs font-mono font-bold" required />
                                                </div>
                                                <div>
                                                    <label class="block text-[9px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Discount Deduction</label>
                                                    <input v-model="item.discount" type="number" step="1" class="input-field bg-white py-1.5 text-xs font-mono font-bold" />
                                                </div>
                                            </div>
                                            
                                            <div class="flex justify-between items-center pt-2 border-t border-[#f3f2f1] text-[10px] font-bold text-slate-400 uppercase">
                                                <span>Deduction status: <span v-if="item.part_id" class="text-emerald-600 font-extrabold">Inventory Link Active</span><span v-else>Custom Line</span></span>
                                                <span class="text-[#201f1e] font-extrabold font-mono">Row Total: {{ formatCurrency(item.quantity * item.unit_price - item.discount) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: FINISH -->
                            <div v-if="currentStep === 'finish'" class="space-y-6 animate-slide-up">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Review and deploy</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Add terms and conditions and review final credit allocation totals before deployment.</p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Operational Terms & Conditions</label>
                                        <textarea v-model="form.notes" rows="2" class="input-field" placeholder="Enter quote terms..."></textarea>
                                    </div>

                                    <div class="border border-[#e1dfdd] rounded-sm overflow-hidden select-none">
                                        <div class="bg-slate-50 px-4 py-2 border-b border-[#e1dfdd] text-[10px] font-bold text-[#605e5c] uppercase">Deployment Summary</div>
                                        <div class="p-4 space-y-4 bg-[#fafafa]">
                                            <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1] text-xs">
                                                <span class="font-bold text-[#605e5c]">Quotation Subject</span>
                                                <span class="font-extrabold text-[#201f1e]">{{ form.subject || 'Not specified' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1] text-xs">
                                                <span class="font-bold text-[#605e5c]">Date Stamp</span>
                                                <span class="font-semibold text-slate-700 font-mono">{{ new Date(form.date).toLocaleDateString() }}</span>
                                            </div>
                                            <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1] text-xs">
                                                <span class="font-bold text-[#605e5c]">Valid Until</span>
                                                <span class="font-semibold text-slate-700 font-mono">{{ new Date(form.valid_until).toLocaleDateString() }}</span>
                                            </div>
                                            <div class="flex justify-between items-center py-1.5 text-sm">
                                                <span class="font-black text-slate-400 uppercase tracking-wider text-xs">Total Accumulation Due</span>
                                                <span class="font-black text-red-600 font-mono tracking-tighter">{{ formatCurrency(subtotal) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- Sticky Footer Action Row -->
                        <div class="px-6 md:px-8 py-4 border-t border-[#e1dfdd] flex justify-between items-center select-none bg-white flex-shrink-0">
                            <button 
                                type="button" 
                                @click="handleClose" 
                                class="btn-secondary"
                            >
                                Cancel
                            </button>
                            
                            <div class="flex gap-2">
                                <!-- Back Button -->
                                <button 
                                    v-if="currentStep !== 'basics'"
                                    type="button" 
                                    @click="currentStep = currentStep === 'finish' ? 'items' : 'basics'"
                                    class="btn-secondary"
                                >
                                    Back
                                </button>
                                
                                <!-- Next Button -->
                                <button 
                                    v-if="currentStep !== 'finish'"
                                    type="button" 
                                    @click="currentStep = currentStep === 'basics' ? 'items' : 'finish'"
                                    class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-1.5 rounded-sm text-xs font-bold transition-all"
                                >
                                    Next
                                </button>
                                
                                <!-- Submit/Finish Button -->
                                <button 
                                    v-else
                                    type="submit" 
                                    :disabled="form.processing" 
                                    class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-1.5 rounded-sm text-xs font-extrabold shadow-sm transition-all flex items-center gap-1.5"
                                >
                                    <span v-if="form.processing">Deploying...</span>
                                    <span v-else>Deploy Quotation</span>
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.animate-slide-in-right {
    animation: slideInRight 0.2s cubic-bezier(0.1, 0.9, 0.2, 1) forwards;
}
@keyframes slideInRight {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
</style>
