<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    salesOrders: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('sales-orders.index'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
});

const getStatusClass = (status) => {
    const map = {
        unpaid: 'bg-red-50 text-red-700 border-red-200',
        partial: 'bg-yellow-50 text-yellow-700 border-yellow-200',
        paid: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    };
    return map[status] || 'bg-zinc-100 text-zinc-700 border-zinc-200';
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
    <Head title="Operational Invoices" />

    <AuthenticatedLayout>
        <PageHeader 
            title="Operational Invoices" 
            subtitle="Final financial settlement management and payment tracking for completed entities."
        />

        <div class="space-y-8">
            <div class="card-base p-6 border-zinc-200">
                <div class="relative group max-w-md">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Search Invoice ID or Customer Profile..." 
                        class="input-field pl-12 group-hover:border-accent transition-all duration-300"
                    >
                    <svg class="w-5 h-5 text-zinc-400 absolute left-4 top-3 group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            
            <div class="card-base p-0 border-zinc-200 overflow-hidden shadow-soft">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] bg-zinc-50/50 border-b border-zinc-100">
                                <th class="px-8 py-5">Invoice Identifier</th>
                                <th class="px-8 py-5">Customer Profile</th>
                                <th class="px-8 py-5 text-right">Total Settlement</th>
                                <th class="px-8 py-5 text-right">Amortized Paid</th>
                                <th class="px-8 py-5">Payment Lifecycle</th>
                                <th class="px-8 py-5">Temporal Entry</th>
                                <th class="px-8 py-5 text-right">Control</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 bg-white">
                            <tr 
                                v-for="order in salesOrders.data" 
                                :key="order.id" 
                                class="group hover:bg-zinc-50/50 transition-all duration-300 cursor-pointer"
                                @click="router.visit(route('sales-orders.show', order.id))"
                            >
                                <td class="px-8 py-6">
                                    <span class="text-sm font-display font-black text-primary group-hover:text-accent transition-colors">
                                        {{ order.order_number }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-sm font-bold text-zinc-700 leading-none">{{ order.customer.name }}</span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <span class="text-sm font-display font-black text-primary">
                                        {{ formatCurrency(order.total_amount) }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right text-xs font-bold text-zinc-500">
                                    {{ formatCurrency(order.amount_paid) }}
                                </td>
                                <td class="px-8 py-6">
                                    <span :class="['px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border shadow-sm', getStatusClass(order.payment_status)]">
                                        {{ order.payment_status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-zinc-500 uppercase tracking-widest">
                                    {{ new Date(order.created_at).toLocaleDateString('en-GB') }}
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <Link 
                                        :href="route('sales-orders.show', order.id)" 
                                        @click.stop 
                                        class="p-2 bg-white border border-border rounded-xl text-zinc-400 hover:text-primary hover:border-primary hover:shadow-lg transition-all"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="px-8 py-6 bg-zinc-50/50 border-t border-zinc-100 flex flex-col md:flex-row justify-between items-center gap-6" v-if="salesOrders.links">
                    <div class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em]">
                        Displaying Intelligence Sector {{ salesOrders.from || 0 }} — {{ salesOrders.to || 0 }} of {{ salesOrders.total }} Financial Invoices
                    </div>
                    <div class="flex items-center gap-2">
                        <Link 
                            v-for="(link, i) in salesOrders.links" 
                            :key="i" 
                            :href="link.url || '#'" 
                            :class="[
                                'px-4 py-2 rounded-xl text-xs font-black transition-all duration-300 border uppercase tracking-widest', 
                                link.active ? 'bg-primary text-white border-primary shadow-lg shadow-black/10' : 'bg-white border-zinc-200 text-zinc-500 hover:bg-zinc-50 hover:text-primary',
                                !link.url && 'opacity-30 cursor-not-allowed grayscale'
                            ]" 
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
