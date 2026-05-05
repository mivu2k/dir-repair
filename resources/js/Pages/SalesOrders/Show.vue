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
        // We'll call a new endpoint or loop in the controller? 
        // Actually, we can just send it to a special intake delivery route or handle it in the existing one.
        // For now, I'll update the controller to handle intake status updates.
        deliverJobForm.post(route('intakes.status.update', props.salesOrder.intake.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="salesOrder.order_number" />

    <AuthenticatedLayout>
        <PageHeader :title="salesOrder.order_number" subtitle="Sales Order Details">
            <template #actions>
                <div class="flex flex-wrap gap-2 items-center">
                    <a :href="route('sales-orders.pdf', salesOrder.id)" target="_blank" class="bg-white border border-zinc-200 text-zinc-900 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-zinc-50 transition-all flex items-center gap-2 shadow-sm active:scale-95">
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        A4 Invoice
                    </a>
                    <a v-if="salesOrder.repair_job" :href="route('jobs.pos', { job: salesOrder.repair_job.job_number, type: 'delivery' })" target="_blank" class="bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-emerald-700 transition-all flex items-center gap-2 shadow-lg shadow-emerald-100 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        POS Receipt
                    </a>
                    <a v-else-if="salesOrder.intake" :href="route('jobs.pos', { job: salesOrder.intake.id, type: 'intake_delivery' })" target="_blank" class="bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-emerald-700 transition-all flex items-center gap-2 shadow-lg shadow-emerald-100 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        POS Receipt
                    </a>
                    <span :class="['px-3 py-1.5 rounded-lg text-sm font-medium border capitalize', 
                        salesOrder.payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 
                        (salesOrder.payment_status === 'partial' ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : 'bg-red-50 text-red-700 border-red-200')]">
                        {{ salesOrder.payment_status }}
                    </span>
                    <button v-if="salesOrder.payment_status === 'paid'" @click="deliverJob" :disabled="deliverJobForm.processing" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-800 transition-colors">
                        Mark Delivered
                    </button>
                </div>
            </template>
        </PageHeader>

        <div class="p-8 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Summary -->
                <div class="bg-white border border-border rounded-xl shadow-sm p-8">
                    <div class="flex justify-between items-start mb-8 pb-8 border-b border-zinc-200">
                        <div>
                            <h2 class="text-2xl font-bold text-primary mb-1">INVOICE</h2>
                            <p class="text-muted font-mono">{{ salesOrder.order_number }}</p>
                            <p class="text-sm text-zinc-600 mt-4">Ref: <Link :href="route('quotations.show', salesOrder.quotation.id)" class="text-accent hover:underline">{{ salesOrder.quotation.quotation_number }}</Link></p>
                        </div>
                        <div class="text-right text-sm">
                            <p class="font-medium text-primary">{{ salesOrder.customer.name }}</p>
                            <p class="text-muted">{{ salesOrder.customer.phone }}</p>
                        </div>
                    </div>

                    <table class="w-full text-left text-sm mb-8">
                        <thead>
                            <tr class="border-b-2 border-primary text-primary">
                                <th class="py-2 w-3/5">Description</th>
                                <th class="py-2 text-right">Qty</th>
                                <th class="py-2 text-right">Unit Price</th>
                                <th class="py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="item in salesOrder.quotation.items" :key="item.id">
                                <td class="py-3">
                                    <p class="font-medium text-primary">{{ item.description }}</p>
                                    <p class="text-xs text-muted uppercase">{{ item.item_type }}</p>
                                </td>
                                <td class="py-3 text-right">{{ item.quantity }}</td>
                                <td class="py-3 text-right">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(item.unit_price).toFixed(0) }}</td>
                                <td class="py-3 text-right font-medium">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(item.total_price).toFixed(0) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-b-2 border-primary">
                                <td colspan="3" class="py-4 text-right text-muted">Subtotal</td>
                                <td class="py-4 text-right font-medium">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.total_amount).toFixed(0) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="py-4 text-right font-bold text-lg">Total Due</td>
                                <td class="py-4 text-right font-bold text-lg">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.total_amount).toFixed(0) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="py-2 text-right text-emerald-600">Amount Paid</td>
                                <td class="py-2 text-right text-emerald-600 font-medium">-{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.amount_paid).toFixed(0) }}</td>
                            </tr>
                            <tr class="bg-zinc-50 border-t border-zinc-200">
                                <td colspan="3" class="py-4 pr-4 text-right font-bold text-xl text-primary">Balance Remaining</td>
                                <td class="py-4 text-right font-bold text-xl text-primary">{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Number(salesOrder.total_amount - salesOrder.amount_paid).toFixed(0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Payment History -->
                <div class="bg-white border border-border rounded-xl shadow-sm p-6" v-if="salesOrder.payments.length > 0">
                    <h3 class="font-semibold text-primary mb-4">Payment History</h3>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-4 py-2 font-medium text-muted">Date</th>
                                <th class="px-4 py-2 font-medium text-muted">Method</th>
                                <th class="px-4 py-2 font-medium text-muted">Ref</th>
                                <th class="px-4 py-2 text-right font-medium text-muted">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="payment in salesOrder.payments" :key="payment.id">
                                <td class="px-4 py-3">{{ new Date(payment.created_at).toLocaleString() }}</td>
                                <td class="px-4 py-3 capitalize">{{ payment.payment_method.replace('_', ' ') }}</td>
                                <td class="px-4 py-3 text-muted">{{ payment.reference_number || '-' }}</td>
                                <td class="px-4 py-3 text-right font-medium text-emerald-600">+{{ $page.props.settings.currency_symbol || 'PKR' }} {{ Math.round(payment.amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Record Payment Form -->
                <div class="bg-white border border-border rounded-xl shadow-sm p-6" v-if="salesOrder.payment_status !== 'paid'">
                    <h3 class="font-semibold text-primary mb-4">Record Payment</h3>
                    <form @submit.prevent="recordPayment" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Amount</label>
                            <div class="relative flex items-center">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-muted font-bold text-xs">{{ $page.props.settings.currency_symbol || 'PKR' }}</span>
                                <input v-model="paymentForm.amount" type="number" step="1" min="1" :max="Math.round(salesOrder.total_amount - salesOrder.amount_paid)" class="w-full pl-12 text-sm border-border rounded-lg focus:ring-accent focus:border-accent" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Method</label>
                            <select v-model="paymentForm.payment_method" class="w-full text-sm border-border rounded-lg focus:ring-accent focus:border-accent">
                                <option value="cash">Cash</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Reference No. (Optional)</label>
                            <input v-model="paymentForm.reference_number" type="text" class="w-full text-sm border-border rounded-lg focus:ring-accent focus:border-accent">
                        </div>
                        <button type="submit" :disabled="paymentForm.processing" class="w-full bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-800 transition-colors">
                            Apply Payment
                        </button>
                    </form>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm p-6 text-center" v-else>
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="font-bold text-emerald-800 mb-1">Fully Paid</h3>
                    <p class="text-sm text-emerald-600">This invoice has been settled in full.</p>
                </div>

                <div v-if="salesOrder.repair_job" class="bg-white border border-border rounded-xl shadow-sm p-6">
                    <h3 class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Job Reference</h3>
                    <p class="font-mono text-primary font-medium mb-1"><Link :href="route('jobs.show', salesOrder.repair_job.job_number)" class="hover:underline">{{ salesOrder.repair_job.job_number }}</Link></p>
                    <p class="text-sm text-zinc-600">{{ salesOrder.repair_job.brand }} {{ salesOrder.repair_job.device_name }}</p>
                    <div class="mt-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium border capitalize bg-zinc-100 text-zinc-800 border-zinc-200">
                            Status: {{ salesOrder.repair_job.status }}
                        </span>
                    </div>
                </div>
                <div v-else-if="salesOrder.intake" class="bg-white border border-border rounded-xl shadow-sm p-6">
                    <h3 class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Intake Reference</h3>
                    <p class="font-mono text-primary font-medium mb-1"><Link :href="route('intakes.show', salesOrder.intake.id)" class="hover:underline">{{ salesOrder.intake.intake_number }}</Link></p>
                    <p class="text-sm text-zinc-600">{{ salesOrder.intake.repair_jobs.length }} Devices Included</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
