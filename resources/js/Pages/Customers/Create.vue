<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const currentStep = ref('basics'); // 'basics' | 'contact' | 'finish'

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

const submit = () => {
    form.post(route('customers.store'));
};
</script>

<template>
    <Head title="Add a customer" />

    <AuthenticatedLayout>
        <!-- Microsoft 365 Admin Center Styled Drawer Panel container -->
        <div class="max-w-5xl mx-auto bg-white border border-[#e1dfdd] shadow-sm rounded-sm overflow-hidden min-h-[500px] flex flex-col md:flex-row mt-4">
            
            <!-- Left Side Stepper (M365 progress timeline) -->
            <div class="w-full md:w-60 bg-[#f3f2f1]/50 border-r border-[#e1dfdd] p-6 select-none flex flex-col justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#201f1e] mb-6">Add a customer</h2>
                    <div class="m365-stepper space-y-4 border-r-0 pr-0">
                        <button 
                            type="button" 
                            @click="currentStep = 'basics'"
                            class="m365-step w-full text-left" 
                            :class="{ 'active': currentStep === 'basics' }"
                        >
                            <span class="m365-step-circle">1</span>
                            <span>Basics</span>
                        </button>
                        
                        <button 
                            type="button" 
                            @click="currentStep = 'contact'"
                            class="m365-step w-full text-left" 
                            :class="{ 'active': currentStep === 'contact' }"
                        >
                            <span class="m365-step-circle">2</span>
                            <span>Contact options</span>
                        </button>
                        
                        <button 
                            type="button" 
                            @click="currentStep = 'finish'"
                            class="m365-step w-full text-left" 
                            :class="{ 'active': currentStep === 'finish' }"
                        >
                            <span class="m365-step-circle">3</span>
                            <span>Finish</span>
                        </button>
                    </div>
                </div>
                
                <div class="text-[10px] text-slate-400 font-semibold leading-relaxed">
                    Set up the client profile for transactional dispatch.
                </div>
            </div>

            <!-- Right Side Form Content -->
            <form @submit.prevent="submit" class="flex-1 p-8 flex flex-col justify-between bg-white">
                
                <!-- STEP 1: BASICS -->
                <div v-if="currentStep === 'basics'" class="space-y-6 animate-slide-up">
                    <div>
                        <h2 class="text-xl font-bold text-[#201f1e] mb-1">Set up the basics</h2>
                        <p class="text-xs text-[#605e5c] leading-relaxed">To get started, fill out some basic information about the customer you are adding to the system database.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Full name *</label>
                            <input v-model="form.name" type="text" class="input-field" placeholder="e.g. Atta-ur-Rahman" required>
                            <div v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#323130] mb-1.5">Phone number *</label>
                                <input v-model="form.phone" type="text" class="input-field" placeholder="e.g. 03001234567" required>
                                <div v-if="form.errors.phone" class="text-red-600 text-xs mt-1">{{ form.errors.phone }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#323130] mb-1.5">Email address</label>
                                <input v-model="form.email" type="email" class="input-field" placeholder="e.g. customer@mei.com.pk">
                                <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Organization / Department</label>
                            <input v-model="form.organization" type="text" class="input-field" placeholder="e.g. Account Department">
                        </div>
                    </div>
                </div>

                <!-- STEP 2: CONTACT OPTIONS -->
                <div v-if="currentStep === 'contact'" class="space-y-6 animate-slide-up">
                    <div>
                        <h2 class="text-xl font-bold text-[#201f1e] mb-1">Set up optional settings</h2>
                        <p class="text-xs text-[#605e5c] leading-relaxed font-medium">Select notification channels and add supplementary contact coordinates for this user profile.</p>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Physical Address</label>
                            <textarea v-model="form.address" rows="2" class="input-field" placeholder="e.g. Main GT Road, Islamabad."></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Preferred dispatch channel</label>
                            <div class="flex gap-6 mt-2">
                                <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                    <input type="radio" value="phone" v-model="form.communication_preference" class="rounded-sm border-[#a19f9d] text-[#0078d4] shadow-sm focus:ring-[#0078d4] focus:ring-offset-0">
                                    Phone Call
                                </label>
                                <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                    <input type="radio" value="email" v-model="form.communication_preference" class="rounded-sm border-[#a19f9d] text-[#0078d4] shadow-sm focus:ring-[#0078d4] focus:ring-offset-0">
                                    Email
                                </label>
                                <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                    <input type="radio" value="whatsapp" v-model="form.communication_preference" class="rounded-sm border-[#a19f9d] text-[#0078d4] shadow-sm focus:ring-[#0078d4] focus:ring-offset-0">
                                    WhatsApp
                                </label>
                            </div>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-[#f3f2f1]">
                            <label class="flex items-center gap-2 text-xs font-medium text-[#323130] cursor-pointer">
                                <input type="checkbox" v-model="form.opt_in_whatsapp" class="rounded-sm border-[#a19f9d] text-[#0078d4] focus:ring-[#0078d4]">
                                Automatically send WhatsApp intake receipt notifications
                            </label>
                            <label class="flex items-center gap-2 text-xs font-medium text-[#323130] cursor-pointer">
                                <input type="checkbox" v-model="form.opt_in_sms" class="rounded-sm border-[#a19f9d] text-[#0078d4] focus:ring-[#0078d4]">
                                Require this client to receive automated SMS updates on repair status
                            </label>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: FINISH -->
                <div v-if="currentStep === 'finish'" class="space-y-6 animate-slide-up">
                    <div>
                        <h2 class="text-xl font-bold text-[#201f1e] mb-1">Review and finish</h2>
                        <p class="text-xs text-[#605e5c] leading-relaxed">Ensure all parameters are input correctly before submitting. You can always amend these coordinates later.</p>
                    </div>

                    <div class="space-y-3 border border-[#e1dfdd] p-4 bg-[#f9fafb] rounded-sm">
                        <div class="flex justify-between text-xs py-1 border-b border-[#f3f2f1]">
                            <span class="font-bold text-slate-500">Name</span>
                            <span class="font-semibold text-slate-800">{{ form.name || 'Not provided' }}</span>
                        </div>
                        <div class="flex justify-between text-xs py-1 border-b border-[#f3f2f1]">
                            <span class="font-bold text-slate-500">Phone</span>
                            <span class="font-semibold text-slate-800">{{ form.phone || 'Not provided' }}</span>
                        </div>
                        <div class="flex justify-between text-xs py-1 border-b border-[#f3f2f1]">
                            <span class="font-bold text-slate-500">Email</span>
                            <span class="font-semibold text-slate-800">{{ form.email || 'Not provided' }}</span>
                        </div>
                        <div class="flex justify-between text-xs py-1">
                            <span class="font-bold text-slate-500">Preference</span>
                            <span class="font-semibold text-slate-800 capitalize">{{ form.communication_preference }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Additional remarks / notes</label>
                        <textarea v-model="form.notes" rows="2" class="input-field" placeholder="Enter notes..."></textarea>
                    </div>
                </div>

                <!-- Stepper Footer Action Buttons -->
                <div class="mt-8 pt-6 border-t border-[#e1dfdd] flex justify-between items-center select-none">
                    <button 
                        type="button" 
                        @click="$inertia.visit(route('customers.index'))" 
                        class="btn-secondary"
                    >
                        Cancel
                    </button>
                    
                    <div class="flex gap-2">
                        <!-- Previous button -->
                        <button 
                            v-if="currentStep !== 'basics'"
                            type="button" 
                            @click="currentStep = currentStep === 'finish' ? 'contact' : 'basics'"
                            class="btn-secondary"
                        >
                            Back
                        </button>
                        
                        <!-- Next Button (Step 1 & 2) -->
                        <button 
                            v-if="currentStep !== 'finish'"
                            type="button" 
                            @click="currentStep = currentStep === 'basics' ? 'contact' : 'finish'"
                            class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-1.5 rounded-sm text-xs font-semibold"
                        >
                            Next
                        </button>
                        
                        <!-- Submit Button (Step 3) -->
                        <button 
                            v-else
                            type="submit" 
                            :disabled="form.processing" 
                            class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-1.5 rounded-sm text-xs font-semibold shadow-sm"
                        >
                            Finish adding
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>
