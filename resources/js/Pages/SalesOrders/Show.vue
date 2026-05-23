<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    salesOrder: Object,
});

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

            <!-- Right Side Panel Area (Record Payment & stepper styles) -->
            <div class="space-y-6">
                <!-- Record Payment Form (M365 Panel style) -->
                <div class="bg-white border border-[#e1dfdd] rounded-sm p-6 space-y-4" v-if="salesOrder.payment_status !== 'paid'">
                    <div class="flex items-center gap-2 border-b border-[#f3f2f1] pb-2">
                        <span class="w-1.5 h-4 bg-[#dc2626] rounded-sm"></span>
                        <h3 class="font-bold text-sm text-[#201f1e]">Record Payment</h3>
                    </div>
                    
                    <form @submit.prevent="recordPayment" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Amount *</label>
                            <div class="relative flex items-center">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#a19f9d] font-bold text-xs">{{ $page.props.settings.currency_symbol || 'PKR' }}</span>
                                <input v-model="paymentForm.amount" type="number" step="1" min="1" :max="Math.round(salesOrder.total_amount - salesOrder.amount_paid)" class="input-field pl-12" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Payment Method</label>
                            <select v-model="paymentForm.payment_method" class="input-field">
                                <option value="cash">Cash</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Reference No. (Optional)</label>
                            <input v-model="paymentForm.reference_number" type="text" class="input-field" placeholder="e.g. TXN-10824">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#323130] mb-1.5">Notes (Optional)</label>
                            <textarea v-model="paymentForm.notes" rows="2" class="input-field" placeholder="Enter notes..."></textarea>
                        </div>
                        <button type="submit" :disabled="paymentForm.processing" class="w-full bg-[#0078d4] hover:bg-[#005a9e] text-white py-1.5 rounded-sm text-xs font-semibold shadow-sm transition-all active:scale-[0.98]">
                            Apply Payment
                        </button>
                    </form>
                </div>

                <!-- Fully Paid M365 styled card badge -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-sm p-6 text-center select-none" v-else>
                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="font-bold text-emerald-800 text-sm mb-0.5">Fully Paid</h3>
                    <p class="text-xs text-emerald-600">This invoice has been settled in full.</p>
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
    </AuthenticatedLayout>
</template>
