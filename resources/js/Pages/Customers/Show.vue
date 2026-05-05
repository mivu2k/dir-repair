<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
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
        <div class="space-y-4 max-w-5xl mx-auto">
            <!-- Functional Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-col">
                    <h1 class="text-lg font-black text-slate-900 tracking-tight">{{ customer.name }}</h1>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Entity ID: {{ customer.id.toString().padStart(4, '0') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('intakes.create', { customer_id: customer.id })" class="btn-primary py-1">New Intake</Link>
                    <Link :href="route('customers.edit', customer.id)" class="btn-secondary py-1">Modify Profile</Link>
                    <button @click="deleteCustomer" class="btn-secondary py-1 text-red-600 border-red-100">Delete</button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
                <!-- Info Column -->
                <div class="lg:col-span-4 space-y-4">
                    <div class="bg-white border border-slate-200 rounded-lg p-4 space-y-4">
                        <h3 class="text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">Profile Intel</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Phone / Comms</p>
                                <p class="text-[11px] font-bold text-slate-900 mt-1 font-mono">{{ customer.phone }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Electronic Mail</p>
                                <p class="text-[11px] font-bold text-slate-900 mt-1">{{ customer.email || '---' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Organization / Entity</p>
                                <p class="text-[11px] font-bold text-slate-900 mt-1">{{ customer.organization || '---' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Geographical Address</p>
                                <p class="text-[11px] font-bold text-slate-900 mt-1 leading-relaxed">{{ customer.address || '---' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Column -->
                <div class="lg:col-span-8 space-y-2">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Unit History Matrix ({{ jobs.total }})</h3>
                    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Job ID</th>
                                    <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Hardware Node</th>
                                    <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Workflow State</th>
                                    <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="job in jobs.data" :key="job.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2 text-[11px] font-bold text-slate-900 font-mono">
                                        <Link :href="route('jobs.show', job.job_number)" class="text-blue-600 hover:underline">{{ job.job_number }}</Link>
                                    </td>
                                    <td class="px-3 py-2 text-[11px] text-slate-600">{{ job.brand }} {{ job.device_name }}</td>
                                    <td class="px-3 py-2">
                                        <StatusBadge :status="job.status" size="xs" />
                                    </td>
                                    <td class="px-3 py-2 text-[11px] text-slate-400 text-right font-mono">{{ new Date(job.created_at).toLocaleDateString() }}</td>
                                </tr>
                                <tr v-if="jobs.data.length === 0">
                                    <td colspan="4" class="px-3 py-8 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Zero historical units detected</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
