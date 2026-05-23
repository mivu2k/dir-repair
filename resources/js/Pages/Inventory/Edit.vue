<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    part: Object,
    parts: Array,
    filters: Object,
});

const isClosing = ref(false);
const search = ref(props.filters.search || '');
const currentStep = ref('basics'); // 'basics' | 'ids' | 'finish'

const form = useForm({
    sku: props.part.sku || '',
    name: props.part.name || '',
    brand: props.part.brand || '',
    model: props.part.model || '',
    price: props.part.price || 0,
    stock_quantity: props.part.stock_quantity || 0,
});

const handleSearch = () => {
    router.get(route('parts.edit', props.part.id), { search: search.value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

const handleClose = () => {
    isClosing.value = true;
    setTimeout(() => {
        router.visit(route('parts.index'));
    }, 150);
};

const submit = () => {
    form.put(route('parts.update', props.part.id), {
        onSuccess: () => {
            // Success redirect is handled by controller
        },
    });
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
    <Head :title="`Modify Asset ${part.name}`" />

    <AuthenticatedLayout>
        <!-- Background Page Content (M365 Style Inventory Index) -->
        <div class="space-y-4 opacity-75 pointer-events-none select-none">
            <div class="flex items-center justify-between gap-4">
                <div class="relative flex-1 max-w-sm">
                    <input 
                        type="text" 
                        v-model="search" 
                        @input="handleSearch"
                        placeholder="Search SKU or name..." 
                        class="input-field pl-8 py-2 text-[11px]"
                        disabled
                    >
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button class="btn-primary py-2 px-6 text-[10px]" disabled>
                    Add New Asset
                </button>
            </div>

            <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-[#e1dfdd]">
                            <th class="px-4 py-3 select-none">SKU Identifier</th>
                            <th class="px-4 py-3 select-none">Part Description</th>
                            <th class="px-4 py-3 select-none">Brand/Model</th>
                            <th class="px-4 py-3 text-center select-none">Stock</th>
                            <th class="px-4 py-3 text-right select-none">Unit Price</th>
                            <th class="px-4 py-3 text-right select-none">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f3f2f1]">
                        <tr v-for="p in parts" :key="p.id">
                            <td class="px-4 py-3 font-extrabold text-[#201f1e] font-mono tracking-tighter">{{ p.sku || '---' }}</td>
                            <td class="px-4 py-3 font-bold text-[#323130]">{{ p.name }}</td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-[#605e5c] uppercase">{{ p.brand || 'GENERIC' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-1.5 py-0.5 rounded-sm text-[10px] font-bold font-mono border bg-emerald-50 text-emerald-700 border-emerald-200">
                                    {{ p.stock_quantity }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-[#201f1e] font-mono">{{ formatCurrency(p.price) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-[#0078d4] font-extrabold text-[10px]">Modify</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Asset Sliding Stepper Drawer (M365 Style) -->
        <div 
            class="fixed inset-0 bg-slate-950/45 z-50 flex justify-end transition-opacity duration-200"
            :class="{ 'opacity-0': isClosing }"
            @click.self="handleClose"
        >
            <div 
                class="w-full md:max-w-3xl bg-white h-full shadow-2xl flex flex-col transform transition-transform duration-200 ease-out"
                :class="isClosing ? 'translate-x-full' : 'translate-x-0'"
            >
                <!-- Drawer Header -->
                <div class="px-6 py-4 border-b border-[#e1dfdd] flex items-center justify-between bg-white select-none">
                    <div class="flex items-center gap-2">
                        <span class="w-1 bg-[#dc2626] h-5 rounded-sm"></span>
                        <h2 class="text-base font-extrabold text-[#201f1e]">Modify Inventory Asset</h2>
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
                            <h3 class="text-xs font-bold text-[#605e5c] uppercase tracking-wider">Asset Steps</h3>
                            
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
                                                : (form.name && form.sku 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="form.name && form.sku && currentStep !== 'basics'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>1</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'basics' ? 'text-[#0078d4]' : 'text-[#323130]'">Item Basics</span>
                                </div>
                                
                                <!-- Step 2: IDs -->
                                <div class="relative flex items-center gap-4 group cursor-pointer" @click="currentStep = 'ids'">
                                    <div 
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 z-10 transition-all"
                                        :class="[
                                            currentStep === 'ids' 
                                                ? 'bg-[#0078d4] border-[#0078d4] text-white' 
                                                : (form.brand || form.model 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="(form.brand || form.model) && currentStep !== 'ids'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>2</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'ids' ? 'text-[#0078d4]' : 'text-[#323130]'">Identification</span>
                                </div>
                                
                                <!-- Step 3: Quantities & Pricing -->
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
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'finish' ? 'text-[#0078d4]' : 'text-[#323130]'">Price & Stock</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-[10px] text-slate-500 font-semibold leading-relaxed border-t border-[#e1dfdd] pt-4">
                            Adding or modifying serial spare parts matrix logs to control active logistics stocks.
                        </div>
                    </div>

                    <!-- Right Form Content Area -->
                    <form @submit.prevent="submit" class="flex-1 flex flex-col overflow-hidden bg-white">
                        <!-- Scrollable Content -->
                        <div class="flex-1 overflow-y-auto p-6 md:p-8">
                        
                        <div>
                            <!-- STEP 1: BASICS -->
                            <div v-if="currentStep === 'basics'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Set up asset basics</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Provide unique inventory item identifier codes and descriptions.</p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Unique SKU / Part Number *</label>
                                        <input type="text" v-model="form.sku" class="input-field font-mono uppercase font-bold" placeholder="e.g. CP-65W-USB-C" required />
                                        <div v-if="form.errors.sku" class="text-red-600 text-[10px] font-bold mt-1 uppercase">{{ form.errors.sku }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Formal Description *</label>
                                        <input type="text" v-model="form.name" class="input-field font-semibold" placeholder="e.g. MacBook USB-C Charging Adapter" required />
                                        <div v-if="form.errors.name" class="text-red-600 text-[10px] font-bold mt-1 uppercase">{{ form.errors.name }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: IDS -->
                            <div v-if="currentStep === 'ids'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Manufacturer Specifications</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Log target equipment brand and device model support codes.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Manufacturer Brand</label>
                                        <input type="text" v-model="form.brand" class="input-field uppercase font-bold" placeholder="e.g. Apple, Dell, HP" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Model Ref Reference</label>
                                        <input type="text" v-model="form.model" class="input-field font-mono" placeholder="e.g. A1706, Latitude 5420" />
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: QUANTITIES & PRICING -->
                            <div v-if="currentStep === 'finish'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Pricing & Allocation</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Specify unit procurement prices and initial logistics counts.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Unit Pricing *</label>
                                        <div class="relative flex items-center">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#a19f9d] font-bold text-xs">PKR</span>
                                            <input type="number" v-model="form.price" class="input-field pl-12 font-mono font-bold" placeholder="0" required />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Initial Stock Count *</label>
                                        <input type="number" v-model="form.stock_quantity" class="input-field font-mono font-bold" placeholder="0" required />
                                    </div>
                                </div>

                                <div class="border border-[#e1dfdd] rounded-sm overflow-hidden select-none mt-4">
                                    <div class="bg-slate-50 px-4 py-2 border-b border-[#e1dfdd] text-[10px] font-bold text-[#605e5c] uppercase">Summary Overview</div>
                                    <div class="p-4 space-y-3 bg-[#fafafa] text-xs">
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">SKU Reference</span>
                                            <span class="font-extrabold uppercase font-mono">{{ form.sku }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Price Unit</span>
                                            <span class="font-extrabold text-[#201f1e] font-mono">{{ formatCurrency(form.price) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1">
                                            <span class="font-bold text-[#605e5c]">Stock Count Allocation</span>
                                            <span class="font-extrabold text-[#0078d4]">{{ form.stock_quantity }} Units</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- Sticky Footer Action Row -->
                        <div class="px-6 md:px-8 py-4 border-t border-[#e1dfdd] flex justify-between items-center select-none bg-white flex-shrink-0">
                            <button type="button" @click="handleClose" class="btn-secondary">Cancel</button>
                            <div class="flex gap-2">
                                <button v-if="currentStep !== 'basics'" type="button" @click="currentStep = currentStep === 'finish' ? 'ids' : 'basics'" class="btn-secondary">Back</button>
                                <button v-if="currentStep !== 'finish'" type="button" @click="currentStep = currentStep === 'basics' ? 'ids' : 'finish'" class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-1.5 rounded-sm text-xs font-bold transition-all">Next</button>
                                <button v-else type="submit" :disabled="form.processing" class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-1.5 rounded-sm text-xs font-extrabold shadow-sm transition-all flex items-center gap-1.5">
                                    <span v-if="form.processing">Saving...</span>
                                    <span v-else>Save Changes</span>
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
