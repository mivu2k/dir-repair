<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    issuances: Object,
    customers: Array,
    filters: Object,
});

const isClosing = ref(false);
const search = ref(props.filters.search || '');
const currentStep = ref('basics'); // 'basics' | 'items' | 'finish'

const form = useForm({
    customer_id: '',
    items: [{ name: '', serial: '', accessories: '' }],
    expected_return_date: '',
    notes: '',
    reference_letter: '',
    department: '',
});

watch(search, (value) => {
    router.get(route('demo-issuances.create'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
});

const addItem = () => {
    form.items.push({ name: '', serial: '', accessories: '' });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const handleClose = () => {
    isClosing.value = true;
    setTimeout(() => {
        router.visit(route('demo-issuances.index'));
    }, 150);
};

const submit = () => {
    form.post(route('demo-issuances.store'), {
        onSuccess: () => {
            // Success redirect is handled by controller
        },
    });
};
</script>

<template>
    <Head title="Issue Demo Goods" />

    <AuthenticatedLayout>
        <!-- Background Page Content (M365 Style Demo Goods Index) -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 opacity-75 pointer-events-none select-none">
            <div>
                <h2 class="text-lg font-extrabold text-[#201f1e] mb-1">Demo Goods Matrix</h2>
                <p class="text-xs text-[#605e5c] font-semibold">Manage and log operational equipment dispatched to clients for field trials.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <div class="relative flex-1 md:flex-initial">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Search demo #, client, serial..." 
                        class="input-field pl-8 w-full md:w-64"
                        disabled
                    />
                </div>
                <button class="bg-[#0078d4] text-white px-4 py-1.5 rounded-sm text-xs font-semibold shadow-sm" disabled>
                    Issue Demo Items
                </button>
            </div>
        </div>

        <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden opacity-75 pointer-events-none select-none">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-[#e1dfdd] text-[#605e5c] uppercase font-bold tracking-wide">
                    <tr>
                        <th class="px-4 py-3">ID / Customer</th>
                        <th class="px-4 py-3">Items Description</th>
                        <th class="px-4 py-3">Timeline Coordinates</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f3f2f1]">
                    <tr v-for="issuance in issuances.data" :key="issuance.id">
                        <td class="px-4 py-3">
                            <div class="font-extrabold text-[#201f1e]">{{ issuance.issuance_number }}</div>
                            <div class="font-bold text-[#605e5c] mt-0.5">{{ issuance.customer.name }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div v-for="(item, idx) in issuance.items" :key="idx" class="mb-2 last:mb-0 border-l-2 border-[#dc2626] pl-2">
                                <div class="font-bold text-[#323130]">{{ item.name }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-slate-500 font-semibold">{{ new Date(issuance.issued_at).toLocaleDateString() }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <StatusBadge :status="issuance.status" />
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-[#0078d4] font-extrabold text-[10px]">Edit</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Issue Sliding Stepper Drawer (M365 Style) -->
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
                        <h2 class="text-base font-extrabold text-[#201f1e]">Issue New Demo Equipment</h2>
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
                            <h3 class="text-xs font-bold text-[#605e5c] uppercase tracking-wider">Timeline Steps</h3>
                            
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
                                                : (form.customer_id 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="form.customer_id && currentStep !== 'basics'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>1</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'basics' ? 'text-[#0078d4]' : 'text-[#323130]'">Client Coordinates</span>
                                </div>
                                
                                <!-- Step 2: Items -->
                                <div class="relative flex items-center gap-4 group cursor-pointer" @click="currentStep = 'items'">
                                    <div 
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 z-10 transition-all"
                                        :class="[
                                            currentStep === 'items' 
                                                ? 'bg-[#0078d4] border-[#0078d4] text-white' 
                                                : (form.items.length > 0 && form.items[0].name 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="form.items.length > 0 && form.items[0].name && currentStep !== 'items'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>2</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'items' ? 'text-[#0078d4]' : 'text-[#323130]'">Equipment Items</span>
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
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'finish' ? 'text-[#0078d4]' : 'text-[#323130]'">Review & Finish</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-[10px] text-slate-500 font-semibold leading-relaxed border-t border-[#e1dfdd] pt-4">
                            Logging active serial nodes issued to customer trial departments under MEI trial logs.
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
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Set up trial coordinates</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Select the recipient customer profile and log target departments.</p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Recipient Customer *</label>
                                        <select v-model="form.customer_id" class="input-field font-semibold" required>
                                            <option value="">Select a customer profile...</option>
                                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.phone }})</option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Reference Letter / Document #</label>
                                            <input v-model="form.reference_letter" type="text" class="input-field" placeholder="e.g. MEI-LETTER-109">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Department / Section</label>
                                            <input v-model="form.department" type="text" class="input-field" placeholder="e.g. Procurement Office">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: ITEMS -->
                            <div v-if="currentStep === 'items'" class="space-y-6">
                                <div class="flex justify-between items-center pb-2 border-b border-[#f3f2f1]">
                                    <div>
                                        <h2 class="text-lg font-bold text-[#201f1e] mb-1">Equipment Details</h2>
                                        <p class="text-xs text-[#605e5c] leading-relaxed">Add and list individual items including accessories and serial numbers.</p>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="addItem" 
                                        class="bg-white hover:bg-slate-50 border border-[#e1dfdd] text-[#0078d4] text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-sm shadow-sm transition-all"
                                    >
                                        Add Another Item
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
                                        
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[10px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Item Description Name *</label>
                                                <input v-model="item.name" type="text" class="input-field bg-white py-1.5 text-xs font-semibold" placeholder="e.g. MacBook Pro M1 16GB" required />
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Serial Number</label>
                                                    <input v-model="item.serial" type="text" class="input-field bg-white py-1.5 text-xs font-mono" placeholder="SN-..." />
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-[#605e5c] uppercase tracking-wide mb-1">Included Accessories</label>
                                                    <input v-model="item.accessories" type="text" class="input-field bg-white py-1.5 text-xs" placeholder="e.g. Charger, USB Cable" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: FINISH -->
                            <div v-if="currentStep === 'finish'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Review and finish</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Specify returns timelines and review parameters before saving trial record.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Expected Return Date</label>
                                        <input v-model="form.expected_return_date" type="date" class="input-field" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">General Notes</label>
                                        <input v-model="form.notes" type="text" class="input-field" placeholder="Add optional trial comments..." />
                                    </div>
                                </div>

                                <div class="border border-[#e1dfdd] rounded-sm overflow-hidden select-none mt-4">
                                    <div class="bg-slate-50 px-4 py-2 border-b border-[#e1dfdd] text-[10px] font-bold text-[#605e5c] uppercase">Summary Overview</div>
                                    <div class="p-4 space-y-3 bg-[#fafafa] text-xs">
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Recipient</span>
                                            <span class="font-extrabold text-[#201f1e]">{{ customers.find(c => c.id === form.customer_id)?.name || 'Not selected' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Total Equipment Nodes</span>
                                            <span class="font-bold text-[#201f1e]">{{ form.items.filter(i => i.name).length }} Items</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1">
                                            <span class="font-bold text-[#605e5c]">Due Return Date</span>
                                            <span class="font-extrabold text-[#0078d4]">{{ form.expected_return_date ? new Date(form.expected_return_date).toLocaleDateString() : 'No return set' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div><!-- /scrollable -->

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
                                    <span v-if="form.processing">Posting...</span>
                                    <span v-else>Save Record</span>
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
