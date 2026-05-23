<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const currentStep = ref('basics'); // 'basics' | 'contact' | 'finish'
const isClosing = ref(false);

const form = useForm({
    name: '',
    phone: '',
    email: '',
    organization: '',
    address: '',
    communication_preference: 'phone',
    notes: '',
    opt_in_whatsapp: true,
    opt_in_sms: true,
});

const search = ref(props.filters.search || '');

// Watch for search filter changes on the background table
watch(search, (value) => {
    router.get(route('customers.create'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
});

const handleClose = () => {
    isClosing.value = true;
    setTimeout(() => {
        router.visit(route('customers.index'));
    }, 150);
};

const submit = () => {
    form.post(route('customers.store'), {
        onSuccess: () => {
            // Success redirect is handled by controller
        }
    });
};
</script>

<template>
    <Head title="Add a customer" />

    <AuthenticatedLayout>
        <!-- Background Page Content (M365 Style Clients Index) -->
        <div class="space-y-4 opacity-75 pointer-events-none select-none">
            <div class="flex items-center justify-between gap-4">
                <div class="relative flex-1 max-w-sm">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Search clients..." 
                        class="input-field pl-8"
                        disabled
                    >
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button class="btn-primary" disabled>New Client</button>
            </div>
            
            <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-[#e1dfdd]">
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Name</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Organization</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Jobs</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Ops</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f3f2f1]">
                        <tr 
                            v-for="customer in customers.data" 
                            :key="customer.id" 
                            class="hover:bg-slate-50 transition-colors"
                        >
                            <td class="px-3 py-2 text-[11px] font-bold text-slate-900">{{ customer.name }}</td>
                            <td class="px-3 py-2 text-[11px] text-slate-600 font-mono">{{ customer.phone }}</td>
                            <td class="px-3 py-2 text-[11px] text-slate-500">{{ customer.organization || '---' }}</td>
                            <td class="px-3 py-2 text-[11px] text-slate-500 font-bold">{{ customer.repair_jobs_count }}</td>
                            <td class="px-3 py-2 text-right">
                                <span class="text-blue-600 font-bold text-[10px]">Open</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- sliding Drawer overlay (M365 Add a User Drawer Style) -->
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
                        <h2 class="text-base font-extrabold text-[#201f1e]">Add a customer</h2>
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
                    <div class="w-full md:w-60 bg-[#f3f2f1] border-r border-[#e1dfdd] p-6 flex flex-col justify-between select-none">
                        <div class="space-y-6">
                            <!-- Stepper header -->
                            <h3 class="text-xs font-bold text-[#605e5c] uppercase tracking-wider">Registration Steps</h3>
                            
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
                                                : (form.name && form.phone 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="form.name && form.phone && currentStep !== 'basics'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>1</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'basics' ? 'text-[#0078d4]' : 'text-[#323130]'">Basics</span>
                                </div>
                                
                                <!-- Step 2: Contact Options -->
                                <div class="relative flex items-center gap-4 group cursor-pointer" @click="currentStep = 'contact'">
                                    <div 
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 z-10 transition-all"
                                        :class="[
                                            currentStep === 'contact' 
                                                ? 'bg-[#0078d4] border-[#0078d4] text-white' 
                                                : (form.address || form.communication_preference !== 'phone' 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="(form.address || form.communication_preference !== 'phone') && currentStep !== 'contact'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>2</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'contact' ? 'text-[#0078d4]' : 'text-[#323130]'">Settings & Preference</span>
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
                            Registering new client account coordinates under MEI operational database records.
                        </div>
                    </div>

                    <!-- Right Form Content Area -->
                    <form @submit.prevent="submit" class="flex-1 p-6 md:p-8 overflow-y-auto bg-white flex flex-col justify-between h-full">
                        
                        <div>
                            <!-- STEP 1: BASICS -->
                            <div v-if="currentStep === 'basics'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Set up the basics</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Fill out basic details about the new customer to start managing their service requests.</p>
                                </div>

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Full name *</label>
                                            <input v-model="form.name" type="text" class="input-field" placeholder="e.g. Atta-ur-Rahman" required>
                                            <div v-if="form.errors.name" class="text-red-600 text-xs mt-1 font-semibold">{{ form.errors.name }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Organization / Company</label>
                                            <input v-model="form.organization" type="text" class="input-field" placeholder="e.g. Account Department">
                                            <div v-if="form.errors.organization" class="text-red-600 text-xs mt-1 font-semibold">{{ form.errors.organization }}</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Phone number *</label>
                                            <input v-model="form.phone" type="text" class="input-field" placeholder="e.g. 03001234567" required>
                                            <div v-if="form.errors.phone" class="text-red-600 text-xs mt-1 font-semibold">{{ form.errors.phone }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Email address</label>
                                            <input v-model="form.email" type="email" class="input-field" placeholder="e.g. customer@mei.com.pk">
                                            <div v-if="form.errors.email" class="text-red-600 text-xs mt-1 font-semibold">{{ form.errors.email }}</div>
                                        </div>
                                    </div>

                                    <div class="pt-4 border-t border-[#f3f2f1] space-y-3">
                                        <label class="flex items-start gap-2.5 text-xs font-medium text-[#323130] cursor-pointer">
                                            <input type="checkbox" v-model="form.opt_in_whatsapp" class="rounded-sm border-[#a19f9d] text-[#0078d4] mt-0.5 focus:ring-[#0078d4]">
                                            <span>Automatically send WhatsApp intake receipt notifications</span>
                                        </label>
                                        <label class="flex items-start gap-2.5 text-xs font-medium text-[#323130] cursor-pointer">
                                            <input type="checkbox" v-model="form.opt_in_sms" class="rounded-sm border-[#a19f9d] text-[#0078d4] mt-0.5 focus:ring-[#0078d4]">
                                            <span>Require this client to receive automated SMS updates on repair status</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: CONTACT OPTIONS -->
                            <div v-if="currentStep === 'contact'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Set up optional settings</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Specify physical mailing address and default communication preferences for receipts.</p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Physical Address</label>
                                        <textarea v-model="form.address" rows="3" class="input-field" placeholder="e.g. Main GT Road, Sector I-9, Islamabad."></textarea>
                                        <div v-if="form.errors.address" class="text-red-600 text-xs mt-1 font-semibold">{{ form.errors.address }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-2">Preferred Dispatch Channel</label>
                                        <div class="flex flex-wrap gap-5 mt-1 bg-[#fafafa] p-3 border border-[#e1dfdd] rounded-sm">
                                            <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                                <input type="radio" value="phone" v-model="form.communication_preference" class="rounded-full border-[#a19f9d] text-[#0078d4] focus:ring-[#0078d4]">
                                                Phone Call
                                            </label>
                                            <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                                <input type="radio" value="email" v-model="form.communication_preference" class="rounded-full border-[#a19f9d] text-[#0078d4] focus:ring-[#0078d4]">
                                                Email Address
                                            </label>
                                            <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                                <input type="radio" value="whatsapp" v-model="form.communication_preference" class="rounded-full border-[#a19f9d] text-[#0078d4] focus:ring-[#0078d4]">
                                                WhatsApp Notification
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: FINISH -->
                            <div v-if="currentStep === 'finish'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Review and finish</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Review the profile summary to ensure correctness before finalizing this customer profile.</p>
                                </div>

                                <div class="border border-[#e1dfdd] rounded-sm overflow-hidden select-none">
                                    <div class="bg-slate-50 px-4 py-2 border-b border-[#e1dfdd] text-[10px] font-bold text-[#605e5c] uppercase">Summary Details</div>
                                    <div class="p-4 space-y-3.5 text-xs bg-[#fafafa]">
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Full Name</span>
                                            <span class="font-extrabold text-[#201f1e]">{{ form.name || 'Not provided' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Phone Number</span>
                                            <span class="font-bold text-[#201f1e] font-mono">{{ form.phone || 'Not provided' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Email Address</span>
                                            <span class="font-medium text-[#201f1e] font-mono">{{ form.email || 'Not provided' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Organization</span>
                                            <span class="font-medium text-[#201f1e]">{{ form.organization || '---' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Mailing Address</span>
                                            <span class="font-medium text-[#201f1e] max-w-xs text-right truncate">{{ form.address || '---' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1">
                                            <span class="font-bold text-[#605e5c]">Notification preference</span>
                                            <span class="font-extrabold text-[#0078d4] capitalize">{{ form.communication_preference }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-xs font-semibold text-[#323130] mb-1.5">Internal notes / Remarks</label>
                                    <textarea v-model="form.notes" rows="2" class="input-field" placeholder="Add any special client remarks or internal notes..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Stepper Footer Action Row -->
                        <div class="mt-8 pt-4 border-t border-[#e1dfdd] flex justify-between items-center select-none bg-white">
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
                                    @click="currentStep = currentStep === 'finish' ? 'contact' : 'basics'"
                                    class="btn-secondary"
                                >
                                    Back
                                </button>
                                
                                <!-- Next Button -->
                                <button 
                                    v-if="currentStep !== 'finish'"
                                    type="button" 
                                    @click="currentStep = currentStep === 'basics' ? 'contact' : 'finish'"
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
                                    <span v-if="form.processing">Registering...</span>
                                    <span v-else>Finish adding</span>
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
/* Stepper animation */
.animate-slide-in-right {
    animation: slideInRight 0.2s cubic-bezier(0.1, 0.9, 0.2, 1) forwards;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
    }
    to {
        transform: translateX(0);
    }
}
</style>
