<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    salesOrder: Object,
});

const isPaymentDrawerOpen = ref(false);
const currentStep = ref('basics'); // 'basics' | 'details' | 'finish'

const paymentForm = useForm({
    amount: Math.round(props.salesOrder.total_amount - props.salesOrder.amount_paid),
    payment_method: 'cash',
    reference_number: '',
    notes: '',
});

const recordPayment = () => {
    paymentForm.post(route('sales-orders.payments.store', props.salesOrder.id), {
        preserveScroll: true,
        onSuccess: () => {
            paymentForm.reset('reference_number', 'notes');
            paymentForm.amount = Math.round(props.salesOrder.total_amount - props.salesOrder.amount_paid);
            isPaymentDrawerOpen.value = false;
            currentStep.value = 'basics';
        },
    });
};

const deliverJobForm = useForm({
    status: 'delivered',
    note: 'Invoice fully paid. Device(s) delivered to customer.'
});
const deliverJob = () => {
    if (props.salesOrder.repair_job) {
        deliverJobForm.post(route('jobs.status', props.salesOrder.repair_job.job_number), {
            preserveScroll: true,
        });
    } else if (props.salesOrder.intake) {
        deliverJobForm.post(route('intakes.status.update', props.salesOrder.intake.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="salesOrder.order_number" />

    <AuthenticatedLayout>
        <!-- Microsoft 365 style top title actions bar -->
        <PageHeader :title="salesOrder.order_number" subtitle="Sales Order Details">
            <template #actions>
                <div class="flex flex-wrap gap-2 items-center select-none">
                    <a :href="route('sales-orders.pdf', salesOrder.id)" target="_blank" class="btn-secondary">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        A4 Invoice
                    </a>
                    
                    <a v-if="salesOrder.repair_job" :href="route('jobs.pos', { job: salesOrder.repair_job.job_number, type: 'delivery' })" target="_blank" class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-4 py-1.5 rounded-sm text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        POS Receipt
                    </a>
                    <a v-else-if="salesOrder.intake" :href="route('jobs.pos', { job: salesOrder.intake.id, type: 'intake_delivery' })" target="_blank" class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-4 py-1.5 rounded-sm text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        POS Receipt
                    </a>
                    
                    <button 
                        v-if="salesOrder.payment_status !== 'paid'" 
                        @click="isPaymentDrawerOpen = true" 
                        class="bg-[#dc2626] hover:bg-[#b91c1c] text-white px-4 py-1.5 rounded-sm text-xs font-semibold flex items-center gap-1.5 transition-all"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Record Payment
                    </button>
                    
                    <span :class="['px-2.5 py-1 rounded-sm text-xs font-semibold border capitalize', 
                        salesOrder.payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 
                        (salesOrder.payment_status === 'partial' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : 'bg-red-50 text-red-700 border-red-200')]">
                        {{ salesOrder.payment_status }}
                    </span>
                    
                    <button v-if="salesOrder.payment_status === 'paid'" @click="deliverJob" :disabled="deliverJobForm.processing" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-1.5 rounded-sm text-xs font-semibold transition-all">
                        Mark Delivered
                    </button>
                </div>
            </template>
        </PageHeader>

        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4">
            
            <!-- Left Side Details Area (Stepped/Data Grid Layout) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Summary Block -->
                <div class="bg-white border border-[#e1dfdd] rounded-sm p-6">
                    <div class="flex justify-between items-start mb-6 pb-6 border-b border-[#f3f2f1]">
                        <div>
                            <h2 class="text-lg font-bold text-[#201f1e] mb-1">SALES INVOICE</h2>
                            <p class="text-[10px] text-[#605e5c] font-mono tracking-wider font-semibold">{{ salesOrder.order_number }}</p>
                            <p class="text-xs text-slate-500 mt-3 font-semibold">Reference: <Link :href="route('quotations.show', salesOrder.quotation.id)" class="text-[#0078d4] hover:underline">{{ salesOrder.quotation.quotation_number }}</Link></p>
                        </div>
                        <div class="text-right text-xs">
                            <p class="font-bold text-[#201f1e]">{{ salesOrder.customer.name }}</p>
                            <p class="text-slate-500 mt-1">{{ salesOrder.customer.phone }}</p>
                        </div>
                    </div>

                    <!-- M365 Data Grid styled table -->
                    <table class="w-full text-left text-xs mb-6 border-0">
                        <thead>
                            <tr class="border-b border-[#e1dfdd] text-[#605e5c]">
                                <th class="py-2 px-1 w-3/5 font-bold uppercase select-none">Line Item Description</th>
                                <th class="py-2 px-1 text-center font-bold uppercase select-none">Qty</th>
                                <th class="py-2 px-1 text-right font-bold uppercase select-none">Unit Price</th>
                                <th class="py-2 px-1 text-right font-bold uppercase select-none">Total Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f3f2f1]">
                            <tr v-for="item in salesOrder.quotation.items" :key="item.id" class="hover:bg-slate-50/50">
                                <td class="py-3 px-1">
                                    <p class="font-bold text-slate-800">{{ item.description }}</p>
                                    <p class="text-[9.5px] text-slate-400 font-bold uppercase tracking-wide mt-1">{{ item.item_type }}</p>
                                </td>
                                <td class="py-3 px-1 text-center font-bold text-slate-700">{{ item.quantity }}</td>
                                <td class="py-3 px-1 text-right font-semibold text-slate-700">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(item.unit_price).toFixed(0) }}</td>
                                <td class="py-3 px-1 text-right font-bold text-[#201f1e]">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(item.total_price).toFixed(0) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-[#e1dfdd]">
                                <td colspan="3" class="py-3 px-1 text-right text-slate-400 font-bold uppercase">Subtotal</td>
                                <td class="py-3 px-1 text-right font-semibold text-slate-700">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.total_amount).toFixed(0) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="py-2 px-1 text-right text-slate-400 font-bold uppercase">Total Due</td>
                                <td class="py-2 px-1 text-right font-bold text-slate-700">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.total_amount).toFixed(0) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="py-2 px-1 text-right text-emerald-600 font-bold uppercase">Amount Paid</td>
                                <td class="py-2 px-1 text-right text-emerald-600 font-bold">-{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.amount_paid).toFixed(0) }}</td>
                            </tr>
                            <tr class="bg-[#fafafa] border-t-2 border-[#e1dfdd]">
                                <td colspan="3" class="py-3 pr-4 text-right font-bold text-sm text-[#201f1e] uppercase">Balance Remaining</td>
                                <td class="py-3 px-1 text-right font-black text-sm text-red-600">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.total_amount - salesOrder.amount_paid).toFixed(0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Payment History Block -->
                <div class="bg-white border border-[#e1dfdd] rounded-sm p-6" v-if="salesOrder.payments.length > 0">
                    <div class="flex items-center gap-2 border-b border-[#f3f2f1] pb-2 mb-4">
                        <span class="w-1.5 h-4 bg-[#0078d4] rounded-sm"></span>
                        <h3 class="font-bold text-sm text-[#201f1e]">Payment History Logs</h3>
                    </div>
                    <table class="w-full text-left text-xs border-0">
                        <thead class="bg-[#fafafa] border-b border-[#e1dfdd]">
                            <tr>
                                <th class="px-3 py-2 font-bold text-[#605e5c] uppercase select-none">Receipt Date</th>
                                <th class="px-3 py-2 font-bold text-[#605e5c] uppercase select-none">Method</th>
                                <th class="px-3 py-2 font-bold text-[#605e5c] uppercase select-none">Transaction Ref</th>
                                <th class="px-3 py-2 text-right font-bold text-[#605e5c] uppercase select-none">Amount Credit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f3f2f1]">
                            <tr v-for="payment in salesOrder.payments" :key="payment.id" class="hover:bg-slate-50/50">
                                <td class="px-3 py-3 font-semibold text-slate-600">{{ new Date(payment.created_at).toLocaleString() }}</td>
                                <td class="px-3 py-3 capitalize font-bold text-slate-700">{{ payment.payment_method.replace('_', ' ') }}</td>
                                <td class="px-3 py-3 text-slate-400 font-mono">{{ payment.reference_number || '-' }}</td>
                                <td class="px-3 py-3 text-right font-black text-emerald-600">+{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Math.round(payment.amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Side Panel Area (Quick Summary Cards) -->
            <div class="space-y-6">
                <!-- Status & Payment Info Card -->
                <div class="bg-white border border-[#e1dfdd] rounded-sm p-6 space-y-4">
                    <div class="flex items-center gap-2 border-b border-[#f3f2f1] pb-2">
                        <span class="w-1.5 h-4 bg-[#dc2626] rounded-sm"></span>
                        <h3 class="font-bold text-sm text-[#201f1e]">Invoice Summary</h3>
                    </div>
                    
                    <div class="space-y-3.5 text-xs">
                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                            <span class="font-semibold text-slate-500">Order Total</span>
                            <span class="font-extrabold text-slate-800">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.total_amount).toFixed(0) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                            <span class="font-semibold text-slate-500">Total Settled</span>
                            <span class="font-bold text-emerald-600">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.amount_paid).toFixed(0) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="font-bold text-slate-500">Amount Outstanding</span>
                            <span class="font-black text-red-600">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.total_amount - salesOrder.amount_paid).toFixed(0) }}</span>
                        </div>
                    </div>

                    <!-- Payment Button or Settled Badge -->
                    <div v-if="salesOrder.payment_status !== 'paid'" class="pt-2">
                        <button 
                            @click="isPaymentDrawerOpen = true" 
                            class="w-full bg-[#dc2626] hover:bg-[#b91c1c] text-white py-1.5 rounded-sm text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Record Payment
                        </button>
                    </div>
                    <div v-else class="bg-emerald-50 border border-emerald-200 rounded-sm p-4 text-center select-none">
                        <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h4 class="font-extrabold text-emerald-800 text-[11px] uppercase tracking-wider mb-0.5">Fully Paid</h4>
                        <p class="text-[10px] text-emerald-600 font-semibold">Balance is fully settled.</p>
                    </div>
                </div>

                <!-- Job / Intake Reference blocks -->
                <div v-if="salesOrder.repair_job" class="bg-white border border-[#e1dfdd] rounded-sm p-5">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">Job Reference</h3>
                    <p class="font-mono text-[#0078d4] font-bold text-xs mb-1.5"><Link :href="route('jobs.show', salesOrder.repair_job.job_number)" class="hover:underline">{{ salesOrder.repair_job.job_number }}</Link></p>
                    <p class="text-xs font-semibold text-[#323130]">{{ salesOrder.repair_job.brand }} {{ salesOrder.repair_job.device_name }}</p>
                    <div class="mt-3">
                        <span class="inline-flex px-2 py-0.5 rounded-sm text-[10px] font-semibold border capitalize bg-slate-50 text-slate-600 border-slate-200">
                            Status: {{ salesOrder.repair_job.status }}
                        </span>
                    </div>
                </div>
                
                <div v-else-if="salesOrder.intake" class="bg-white border border-[#e1dfdd] rounded-sm p-5">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">Intake Reference</h3>
                    <p class="font-mono text-[#0078d4] font-bold text-xs mb-1.5"><Link :href="route('intakes.show', salesOrder.intake.id)" class="hover:underline">{{ salesOrder.intake.intake_number }}</Link></p>
                    <p class="text-xs font-semibold text-[#323130]">{{ salesOrder.intake.repair_jobs.length }} Devices Included</p>
                </div>
            </div>
        </div>

        <!-- sliding Drawer overlay (M365 Style Record Payment Drawer) -->
        <div 
            v-if="isPaymentDrawerOpen"
            class="fixed inset-0 bg-slate-950/45 z-50 flex justify-end transition-opacity duration-200"
            @click.self="isPaymentDrawerOpen = false"
        >
            <div class="w-full md:max-w-3xl bg-white h-full shadow-2xl flex flex-col transform transition-transform duration-200 ease-out translate-x-0">
                <!-- Drawer Header -->
                <div class="px-6 py-4 border-b border-[#e1dfdd] flex items-center justify-between bg-white select-none">
                    <div class="flex items-center gap-2">
                        <span class="w-1 bg-[#dc2626] h-5 rounded-sm"></span>
                        <h2 class="text-base font-extrabold text-[#201f1e]">Record Payment</h2>
                    </div>
                    <button 
                        @click="isPaymentDrawerOpen = false"
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
                            <h3 class="text-xs font-bold text-[#605e5c] uppercase tracking-wider">Payment Steps</h3>
                            
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
                                                : (paymentForm.amount > 0 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="paymentForm.amount > 0 && currentStep !== 'basics'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>1</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'basics' ? 'text-[#0078d4]' : 'text-[#323130]'">Payment Basics</span>
                                </div>
                                
                                <!-- Step 2: Details -->
                                <div class="relative flex items-center gap-4 group cursor-pointer" @click="currentStep = 'details'">
                                    <div 
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 z-10 transition-all"
                                        :class="[
                                            currentStep === 'details' 
                                                ? 'bg-[#0078d4] border-[#0078d4] text-white' 
                                                : (paymentForm.reference_number || paymentForm.notes 
                                                    ? 'bg-emerald-600 border-emerald-600 text-white' 
                                                    : 'bg-white border-[#a19f9d] text-slate-600')
                                        ]"
                                    >
                                        <svg v-if="(paymentForm.reference_number || paymentForm.notes) && currentStep !== 'details'" class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span v-else>2</span>
                                    </div>
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'details' ? 'text-[#0078d4]' : 'text-[#323130]'">Reference & Notes</span>
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
                                    <span class="text-xs font-bold transition-colors" :class="currentStep === 'finish' ? 'text-[#0078d4]' : 'text-[#323130]'">Review & Apply</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-[10px] text-slate-500 font-semibold leading-relaxed border-t border-[#e1dfdd] pt-4">
                            Applying credit receipt allocation. Outstanding balance will update automatically upon posting.
                        </div>
                    </div>

                    <!-- Right Form Content Area -->
                    <form @submit.prevent="recordPayment" class="flex-1 p-6 md:p-8 overflow-y-auto bg-white flex flex-col justify-between h-full">
                        
                        <div>
                            <!-- STEP 1: BASICS -->
                            <div v-if="currentStep === 'basics'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Set up transaction basics</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Specify credit allocation and selection of transaction channels.</p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Amount *</label>
                                        <div class="relative flex items-center">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#a19f9d] font-bold text-xs">{{ $page.props.settings.currency_symbol || 'PKR' }}</span>
                                            <input v-model="paymentForm.amount" type="number" step="1" min="1" :max="Math.round(salesOrder.total_amount - salesOrder.amount_paid)" class="input-field pl-12" required>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Payment Method</label>
                                        <div class="flex flex-wrap gap-5 mt-1 bg-[#fafafa] p-3 border border-[#e1dfdd] rounded-sm">
                                            <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                                <input type="radio" value="cash" v-model="paymentForm.payment_method" class="rounded-full border-[#a19f9d] text-[#0078d4] focus:ring-[#0078d4]">
                                                Cash
                                            </label>
                                            <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                                <input type="radio" value="card" v-model="paymentForm.payment_method" class="rounded-full border-[#a19f9d] text-[#0078d4] focus:ring-[#0078d4]">
                                                Credit/Debit Card
                                            </label>
                                            <label class="flex items-center gap-2 text-xs font-semibold text-[#323130] cursor-pointer">
                                                <input type="radio" value="bank_transfer" v-model="paymentForm.payment_method" class="rounded-full border-[#a19f9d] text-[#0078d4] focus:ring-[#0078d4]">
                                                Bank Transfer
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: DETAILS -->
                            <div v-if="currentStep === 'details'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Set up transaction details</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Provide external banking references and optional description notes.</p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Reference No. (Optional)</label>
                                        <input v-model="paymentForm.reference_number" type="text" class="input-field" placeholder="e.g. TXN-10824">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Notes (Optional)</label>
                                        <textarea v-model="paymentForm.notes" rows="3" class="input-field" placeholder="Add optional reference details..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: FINISH -->
                            <div v-if="currentStep === 'finish'" class="space-y-6">
                                <div>
                                    <h2 class="text-lg font-bold text-[#201f1e] mb-1">Review and apply</h2>
                                    <p class="text-xs text-[#605e5c] leading-relaxed">Confirm the allocation summary is correct before committing details to logs.</p>
                                </div>

                                <div class="border border-[#e1dfdd] rounded-sm overflow-hidden select-none">
                                    <div class="bg-slate-50 px-4 py-2 border-b border-[#e1dfdd] text-[10px] font-bold text-[#605e5c] uppercase">Allocation Summary</div>
                                    <div class="p-4 space-y-3.5 text-xs bg-[#fafafa]">
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Amount Credit</span>
                                            <span class="font-extrabold text-emerald-600">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(paymentForm.amount).toFixed(0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Method</span>
                                            <span class="font-extrabold text-[#201f1e] uppercase">{{ paymentForm.payment_method.replace('_', ' ') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1 border-b border-[#f3f2f1]">
                                            <span class="font-bold text-[#605e5c]">Reference Code</span>
                                            <span class="font-semibold text-slate-600 font-mono">{{ paymentForm.reference_number || '---' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-1">
                                            <span class="font-bold text-[#605e5c]">Remaining Balance After Post</span>
                                            <span class="font-extrabold text-red-600">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number((salesOrder.total_amount - salesOrder.amount_paid) - paymentForm.amount).toFixed(0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stepper Footer Action Row -->
                        <div class="mt-8 pt-4 border-t border-[#e1dfdd] flex justify-between items-center select-none bg-white">
                            <button 
                                type="button" 
                                @click="isPaymentDrawerOpen = false" 
                                class="btn-secondary"
                            >
                                Cancel
                            </button>
                            
                            <div class="flex gap-2">
                                <!-- Back Button -->
                                <button 
                                    v-if="currentStep !== 'basics'"
                                    type="button" 
                                    @click="currentStep = currentStep === 'finish' ? 'details' : 'basics'"
                                    class="btn-secondary"
                                >
                                    Back
                                </button>
                                
                                <!-- Next Button -->
                                <button 
                                    v-if="currentStep !== 'finish'"
                                    type="button" 
                                    @click="currentStep = currentStep === 'basics' ? 'details' : 'finish'"
                                    class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-1.5 rounded-sm text-xs font-bold transition-all"
                                >
                                    Next
                                </button>
                                
                                <!-- Submit/Finish Button -->
                                <button 
                                    v-else
                                    type="submit" 
                                    :disabled="paymentForm.processing" 
                                    class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-5 py-1.5 rounded-sm text-xs font-extrabold shadow-sm transition-all flex items-center gap-1.5"
                                >
                                    <span v-if="paymentForm.processing">Applying...</span>
                                    <span v-else>Record payment</span>
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
