<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    passes: Object,
    filters: Object,
});

const isClosing = ref(false);
const search = ref(props.filters.search || '');
const currentStep = ref('basics'); // 'basics' | 'items' | 'finish'

const form = useForm({
    type: 'inward',
    person_name: '',
    company_name: '',
    vehicle_number: '',
    items: [{ description: '', qty: 1, serial: '' }],
    notes: '',
});

watch(search, (value) => {
    router.get(route('gate-passes.create'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
});

const addItem = () => {
    form.items.push({ description: '', qty: 1, serial: '' });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const handleClose = () => {
    isClosing.value = true;
    setTimeout(() => {
        router.visit(route('gate-passes.index'));
    }, 150);
};

const submit = () => {
    form.post(route('gate-passes.store'), {
        onSuccess: () => {
            // Success redirect is handled by controller
        },
    });
};
</script>

<template>
    <Head title="Create Gate Pass" />

    <AuthenticatedLayout>
        <!-- Background Page Content (M365 Style Gate Passes Index) -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 opacity-75 pointer-events-none select-none">
            <div>
                <h2 class="text-lg font-extrabold text-[#201f1e] mb-1">Gate Pass Registry</h2>
                <p class="text-xs text-[#605e5c] font-semibold">Log inward and outward movement of client goods and assets securely.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <div class="relative flex-1 md:flex-initial">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Search pass #, person, items..." 
                        class="input-field pl-8 w-full md:w-64"
                        disabled
                    />
                </div>
                <button class="bg-[#0078d4] text-white px-4 py-1.5 rounded-sm text-xs font-semibold shadow-sm" disabled>
                    Create Gate Pass
                </button>
            </div>
        </div>

        <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden opacity-75 pointer-events-none select-none">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-[#e1dfdd] text-[#605e5c] uppercase font-bold tracking-wide">
                    <tr>
                        <th class="px-4 py-3">Pass Identifier</th>
                        <th class="px-4 py-3">Direction Type</th>
                        <th class="px-4 py-3">Carrier / Contact coordinates</th>
                        <th class="px-4 py-3">Items & Quantities</th>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3 text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f3f2f1]">
                    <tr v-for="pass in passes.data" :key="pass.id">
                        <td class="px-4 py-3">
                            <div class="font-extrabold text-[#201f1e]">{{ pass.pass_number }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="pass.type === 'inward'" class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">Inward</span>
                            <span v-else class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase bg-orange-50 text-orange-700 border border-orange-200">Outward</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-[#201f1e]">{{ pass.person_name }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div v-for="(item, idx) in pass.items" :key="idx" class="text-xs font-bold text-slate-700">
                                {{ item.qty }}x {{ item.description }}
                            </div>
                        </td>
                        <td class="px-4 py-3 font-semibold text-[#605e5c]">
                            <div>{{ new Date(pass.created_at).toLocaleDateString() }}</div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-[#0078d4] font-extrabold text-[10px]">Edit</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create Sliding Stepper Drawer (M365 Style) -->
        <div 
            class="fixed inset-0 bg-slate-950/45 z-50 flex justify-end transition-opacity duration-200"
            :class="{ 'opacity-0': isClosing }"
            @click.self="handleClose"
        >
            <div 
                class="w-full md:max-w-3xl lg:max-w-4xl bg-white h-full shadow-2xl flex flex-col transform transition-transform duration-200 ease-out"
                :class="isClosing ? 'translate-x-full' : 'translate-x-0'"
            >
                <!-- Drawer Header -->
                <div class="px-6 py-4 border-b border-[#e1dfdd] flex items-center justify-between bg-white select-none">
                    <div class="flex items-center gap-2">
                        <span class="w-1 bg-[#dc2626] h-5 rounded-sm"></span>
                        <h2 class="text-base font-extrabold text-[#201f1e]">Log New Gate Pass</h2>
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
                            <h3 class="text-xs font-bold text-[#605e5c] uppercase tracking-wider">Gate Steps</h3>
                            
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
                                                : (form.person_name 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="form.person_name && currentStep !== 'basics'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>1</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'basics' ? 'text-[#0078d4]' : 'text-[#323130]'">Carrier Details</span>
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
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'items' ? 'text-[#0078d4]' : 'text-[#323130]'">Item Matrix</span>
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
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'finish' ? 'text-[#0078d4]' : 'text-[#323130]'">Review & Save</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-[10px] text-slate-500 font-semibold leading-relaxed border-t border-[#e1dfdd] pt-4">
                            Logging active secure inventory transfers passing gate security checks under MEI logbooks.
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
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Set up gate coordinates</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Specify dispatch routing types and identity logs of active carriers.</p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Movement Direction Type *</label>
                                        <div class="flex flex-wrap gap-5 bg-[#fafafa] p-3 border border-[#e1dfdd] rounded-sm select-none">
                                            <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                                <input type="radio" value="inward" v-model="form.type" class="rounded-full border-[#a19f9d] text-[#0078d4] focus:ring-[#0078d4]">
                                                Inward (Receiving Equipment)
                                            </label>
                                            <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                                <input type="radio" value="outward" v-model="form.type" class="rounded-full border-[#a19f9d] text-[#0078d4] focus:ring-[#0078d4]">
                                                Outward (Dispatching Equipment)
                                            </label>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Carrier Person Name *</label>
                                            <input v-model="form.person_name" type="text" class="input-field font-semibold" placeholder="e.g. Driver / Courier" required />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Carrier Company / Vendor</label>
                                            <input v-model="form.company_name" type="text" class="input-field" placeholder="e.g. Leopard, TCS, etc." />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: ITEMS -->
                            <div v-if="currentStep === 'items'" class="space-y-6">
                                <div class="flex justify-between items-center pb-2 border-b border-[#f3f2f1]">
                                    <div>
                                        <h2 class="text-lg font-bold text-[#201f1e] mb-1">Gate Pass Items</h2>
                                        <p class="text-xs text-[#605e5c] leading-relaxed">Add and list individual items including quantities and serial codes.</p>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="addItem" 
                                        class="bg-white hover:bg-slate-50 border border-[#e1dfdd] text-[#0078d4] text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-sm shadow-sm transition-all"
                                    >
                                        Add Item Row
                                    </button>
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
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                            <div class="md:col-span-2">
                                                <label class="block text-[10px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Item Description *</label>
                                                <input v-model="item.description" type="text" class="input-field bg-white py-1.5 text-xs font-semibold" placeholder="e.g. HP Laptop EliteBook" required />
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Serial Number</label>
                                                <input v-model="item.serial" type="text" class="input-field bg-white py-1.5 text-xs font-mono" placeholder="S/N" />
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Qty *</label>
                                                <input v-model="item.qty" type="number" step="0.1" class="input-field bg-white py-1.5 text-xs font-mono font-bold" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: FINISH -->
                            <div v-if="currentStep === 'finish'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Review and Save</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Confirm and review pass log details before final ledger entry.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Vehicle Number Reference</label>
                                        <input v-model="form.vehicle_number" type="text" class="input-field" placeholder="e.g. ICT-LEA-9821" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">General Notes</label>
                                        <input v-model="form.notes" type="text" class="input-field" placeholder="Enter special dispatcher comments..." />
                                    </div>
                                </div>

                                <div class="border border-[#e1dfdd] rounded-sm overflow-hidden select-none mt-4">
                                    <div class="bg-slate-50 px-4 py-2 border-b border-[#e1dfdd] text-[10px] font-bold text-[#605e5c] uppercase">Summary Overview</div>
                                    <div class="p-4 space-y-3 bg-[#fafafa] text-xs">
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Direction</span>
                                            <span class="font-extrabold uppercase text-[#dc2626]">{{ form.type }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Carrier Person</span>
                                            <span class="font-extrabold text-[#201f1e]">{{ form.person_name }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1">
                                            <span class="font-bold text-[#605e5c]">Total Item Types</span>
                                            <span class="font-extrabold text-[#0078d4]">{{ form.items.filter(i => i.description).length }} Unique Lines</span>
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
                                <button v-if="currentStep !== 'basics'" type="button" @click="currentStep = currentStep === 'finish' ? 'items' : 'basics'" class="btn-secondary">Back</button>
                                <button v-if="currentStep !== 'finish'" type="button" @click="currentStep = currentStep === 'basics' ? 'items' : 'finish'" class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-1.5 rounded-sm text-xs font-bold transition-all">Next</button>
                                <button v-else type="submit" :disabled="form.processing" class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-1.5 rounded-sm text-xs font-extrabold shadow-sm transition-all flex items-center gap-1.5">
                                    <span v-if="form.processing">Saving...</span>
                                    <span v-else>Save Pass</span>
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
