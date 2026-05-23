<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    customer: Object,
    jobs: Object,
});

const deleteCustomer = () => {
    if (confirm('Erase this entity profile?')) {
        router.delete(route('customers.destroy', props.customer.id));
    }
};
</script>

<template>
    <Head :title="customer.name" />

    <AuthenticatedLayout>
        <PageHeader :title="customer.name" subtitle="Client Profile">
            <template #actions>
                <div class="flex flex-wrap items-center gap-2 select-none">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest border border-[#e1dfdd] px-2 py-1 rounded-sm bg-white">
                        ID: {{ customer.id.toString().padStart(4, '0') }}
                    </span>
                    <Link :href="route('intakes.create', { customer_id: customer.id })" class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-4 py-1.5 rounded-sm text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        New Intake
                    </Link>
                    <Link :href="route('customers.edit', customer.id)" class="btn-secondary">Modify Profile</Link>
                    <button @click="deleteCustomer" class="btn-secondary text-red-600 border-red-100">Delete</button>
                </div>
            </template>
        </PageHeader>

        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 mt-4">
            <!-- Info Column -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white border border-[#e1dfdd] rounded-sm p-6 space-y-5">
                    <div class="flex items-center gap-2 border-b border-[#f3f2f1] pb-2">
                        <span class="w-1.5 h-4 bg-[#0078d4] rounded-sm"></span>
                        <h3 class="font-bold text-sm text-[#201f1e]">Profile Information</h3>
                    </div>
                    <div class="space-y-4 text-xs">
                        <div class="py-2 border-b border-[#f3f2f1]">
                            <p class="text-[9px] font-black text-[#605e5c] uppercase tracking-widest mb-1">Phone / Comms</p>
                            <p class="font-bold text-[#201f1e] font-mono">{{ customer.phone }}</p>
                        </div>
                        <div class="py-2 border-b border-[#f3f2f1]">
                            <p class="text-[9px] font-black text-[#605e5c] uppercase tracking-widest mb-1">Electronic Mail</p>
                            <p class="font-semibold text-[#323130]">{{ customer.email || '---' }}</p>
                        </div>
                        <div class="py-2 border-b border-[#f3f2f1]">
                            <p class="text-[9px] font-black text-[#605e5c] uppercase tracking-widest mb-1">Organization / Entity</p>
                            <p class="font-bold text-[#201f1e]">{{ customer.organization || '---' }}</p>
                        </div>
                        <div class="py-2">
                            <p class="text-[9px] font-black text-[#605e5c] uppercase tracking-widest mb-1">Geographical Address</p>
                            <p class="font-semibold text-[#323130] leading-relaxed">{{ customer.address || '---' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Column -->
            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden">
                    <div class="flex items-center gap-2 px-6 py-4 border-b border-[#f3f2f1]">
                        <span class="w-1.5 h-4 bg-[#dc2626] rounded-sm"></span>
                        <h3 class="font-bold text-sm text-[#201f1e]">Unit History Matrix <span class="text-[#605e5c] font-semibold">({{ jobs.total }})</span></h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-[#e1dfdd] text-[#605e5c] uppercase font-bold tracking-wide">
                                <tr>
                                    <th class="px-4 py-3 select-none">Job ID</th>
                                    <th class="px-4 py-3 select-none">Hardware Node</th>
                                    <th class="px-4 py-3 select-none">Workflow State</th>
                                    <th class="px-4 py-3 text-right select-none">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f3f2f1]">
                                <tr v-for="job in jobs.data" :key="job.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 font-bold font-mono">
                                        <Link :href="route('jobs.show', job.job_number)" class="text-[#0078d4] hover:underline">{{ job.job_number }}</Link>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-[#323130]">{{ job.brand }} {{ job.device_name }}</td>
                                    <td class="px-4 py-3">
                                        <StatusBadge :status="job.status" size="xs" />
                                    </td>
                                    <td class="px-4 py-3 text-right text-[#605e5c] font-mono">{{ new Date(job.created_at).toLocaleDateString() }}</td>
                                </tr>
                                <tr v-if="jobs.data.length === 0">
                                    <td colspan="4" class="px-4 py-12 text-center text-[#a19f9d] font-bold italic">No historical job records found for this client.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
