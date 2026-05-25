<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    quotation: Object,
});

const statusForm = useForm({
    status: props.quotation.status,
});

const invoiceForm = useForm({
    quotation_id: props.quotation.id,
});

const generateInvoice = () => {
    invoiceForm.post(route('sales-orders.store'), {
        preserveScroll: true,
    });
};

const updateStatus = (newStatus) => {
    statusForm.status = newStatus;
    statusForm.post(route('quotations.status', props.quotation.id), {
        preserveScroll: true,
    });
};

const deleteQuotation = () => {
    if (confirm('Erase this financial scenario?')) {
        router.delete(route('quotations.destroy', props.quotation.id));
    }
};

const user = computed(() => usePage().props.auth.user);
const role = computed(() => user.value?.roles?.[0]?.name || user.value?.role || 'staff');
const isAdmin = computed(() => role.value === 'admin');
const permissions = computed(() => user.value?.permissions || []);
const hasPermission = (permission) => isAdmin.value || permissions.value.includes(permission);

const canDelete = computed(() => hasPermission('delete quotations'));
const canEdit = computed(() => hasPermission('edit quotations'));
const canApprove = computed(() => hasPermission('approve quotation'));
const canGenerateInvoice = computed(() => hasPermission('create sales-orders'));
</script>

<template>
    <Head :title="quotation.quotation_number" />

    <AuthenticatedLayout>
        <div class="space-y-4 max-w-5xl mx-auto">
            <!-- Functional Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex flex-col">
                        <h1 class="text-lg font-black text-slate-900 font-mono tracking-tight leading-none">{{ quotation.quotation_number }}</h1>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Financial Entity</span>
                    </div>
                    <StatusBadge :status="quotation.status" size="xs" />
                </div>
                <div class="flex items-center gap-2">
                    <button v-if="canDelete" @click="deleteQuotation" class="btn-secondary py-1 text-red-600 border-red-100">Delete</button>
                    <div class="relative group">
                        <button class="btn-primary py-1">Print Ops</button>
                        <div class="absolute right-0 mt-1 w-44 bg-white border border-slate-200 rounded shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                            <a :href="route('quotations.pdf', quotation.id)" target="_blank" class="block px-3 py-2 text-[10px] font-black text-slate-600 hover:bg-slate-50 uppercase tracking-widest">A4 PDF Print</a>
                            <a :href="route('jobs.pos', { job: quotation.id, type: 'quotation' })" target="_blank" class="block px-3 py-2 text-[10px] font-black text-slate-600 hover:bg-slate-50 uppercase tracking-widest border-t border-slate-100">POS Thermal</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Matrix -->
            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Description</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest w-16 text-center">Qty</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 text-right">Unit Price</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest w-20 text-right">Disc.</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in quotation.items" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-bold text-slate-900">{{ item.description }}</span>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[8px] font-black text-slate-400 uppercase">{{ item.item_type }}</span>
                                            <span v-if="item.repair_job" class="text-[8px] font-black text-blue-500 font-mono">{{ item.repair_job.job_number }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-center text-[11px] font-medium text-slate-600">{{ Number(item.quantity).toFixed(0) }}</td>
                                <td class="px-3 py-2 text-right text-[11px] font-mono text-slate-600">{{ Number(item.unit_price).toLocaleString() }}</td>
                                <td class="px-3 py-2 text-right text-[11px] font-mono text-slate-400">{{ Number(item.discount || 0).toLocaleString() }}</td>
                                <td class="px-3 py-2 text-right text-[11px] font-black text-slate-900 font-mono">{{ Number(item.line_total).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                        Matrix Density: {{ quotation.items.length }} Items
                    </div>
                    <div class="text-[11px] font-black text-slate-900 font-mono">
                        Subtotal: {{ Number(quotation.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                    </div>
                </div>
            </div>

            <!-- Contextual Meta -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white border border-slate-200 rounded-lg p-4 space-y-3">
                    <div v-if="quotation.subject">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Subject Reference</label>
                        <p class="text-[11px] font-bold text-slate-900 mt-1">{{ quotation.subject }}</p>
                    </div>
                    <div v-if="quotation.notes" class="pt-2 border-t border-slate-50">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Operational Notes</label>
                        <p class="text-[11px] text-slate-600 mt-1 whitespace-pre-wrap leading-relaxed">"{{ quotation.notes }}"</p>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col justify-center">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <span>Gross Accumulation</span>
                            <span class="font-mono">{{ Number(quotation.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <span>Operational Tax</span>
                            <span class="font-mono">0.00</span>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Net Payable (PKR)</span>
                            <span class="text-xl font-black text-slate-900 font-mono tracking-tighter">{{ Number(quotation.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Workflow Bar -->
            <div class="bg-white border border-slate-200 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 shadow-sm">
                <div class="flex gap-2">
                    <button :disabled="!canEdit" @click="updateStatus('pending')" :class="['btn-secondary py-1.5 disabled:opacity-50 disabled:cursor-not-allowed', quotation.status === 'pending' ? 'bg-slate-900 text-white' : '']">Set Pending</button>
                    <button :disabled="!canApprove" @click="updateStatus('approved')" :class="['btn-secondary py-1.5 border-emerald-200 text-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed', quotation.status === 'approved' ? 'bg-emerald-600 text-white' : '']">Approve Scenario</button>
                    <button :disabled="!canEdit" @click="updateStatus('sent')" :class="['btn-secondary py-1.5 border-blue-200 text-blue-600 disabled:opacity-50 disabled:cursor-not-allowed', quotation.status === 'sent' ? 'bg-blue-600 text-white' : '']">Mark Sent</button>
                </div>
                <div class="flex gap-2">
                    <button v-if="quotation.status === 'approved' && canGenerateInvoice" @click="generateInvoice" :disabled="invoiceForm.processing" class="btn-primary py-1.5 px-6">Generate Invoice</button>
                    <Link :href="route('quotations.index')" class="btn-secondary py-1.5">Close Matrix</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
