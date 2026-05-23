<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    issuances: Object,
    customers: Array,
    filters: Object,
});

const returningItem = ref(null);
const search = ref(props.filters.search || '');

const returnForm = useForm({
    notes: '',
});

watch(search, (value) => {
    router.get(route('demo-issuances.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
});

const submitReturn = () => {
    returnForm.post(route('demo-issuances.return', returningItem.value.id), {
        onSuccess: () => {
            returningItem.value = null;
            returnForm.reset();
        },
    });
};

const canEdit = (issuance) => {
    const roles = props.auth?.user?.roles || [];
    if (roles.includes('admin') || roles.includes('manager')) return true;
    return issuance.status !== 'returned';
};

const canDelete = () => {
    const roles = props.auth?.user?.roles || [];
    return roles.includes('admin') || roles.includes('manager');
};

const deleteIssuance = (id) => {
    if (confirm('Are you sure you want to delete this issuance?')) {
        router.delete(route('demo-issuances.destroy', id));
    }
};
</script>

<template>
    <Head title="Demo Goods" />

    <AuthenticatedLayout>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
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
                        class="input-field w-full md:w-64"
                        style="padding-left: 2.25rem !important;"
                    />
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                </div>
                <Link 
                    :href="route('demo-issuances.create')" 
                    class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-4 py-1.5 rounded-sm text-xs font-semibold shadow-sm transition-all text-center select-none whitespace-nowrap"
                >
                    Issue Demo Items
                </Link>
            </div>
        </div>

        <!-- M365 Data Table Grid -->
        <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[700px] sm:min-w-full">
                    <thead class="bg-slate-50 border-b border-[#e1dfdd] text-[#605e5c] uppercase font-bold tracking-wide">
                        <tr>
                            <th class="px-4 py-3 select-none">ID / Customer</th>
                            <th class="px-4 py-3 select-none">Items Description</th>
                            <th class="px-4 py-3 select-none">Timeline Coordinates</th>
                            <th class="px-4 py-3 text-center select-none">Status</th>
                            <th class="px-4 py-3 text-right select-none">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f3f2f1]">
                        <tr v-for="issuance in issuances.data" :key="issuance.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-extrabold text-[#201f1e]">{{ issuance.issuance_number }}</div>
                                <div class="font-bold text-[#605e5c] mt-0.5">{{ issuance.customer.name }}</div>
                                <div v-if="issuance.department" class="text-[10px] text-slate-400 font-bold mt-1 uppercase">Dept: {{ issuance.department }}</div>
                                <div v-if="issuance.reference_letter" class="text-[10px] text-slate-400 font-bold mt-0.5 uppercase">Ref: {{ issuance.reference_letter }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div v-for="(item, idx) in issuance.items" :key="idx" class="mb-2 last:mb-0 border-l-2 border-[#dc2626] pl-2">
                                    <div class="font-bold text-[#323130]">{{ item.name }}</div>
                                    <div class="text-[9px] text-[#605e5c] uppercase font-bold mt-0.5" v-if="item.serial">SN: {{ item.serial }}</div>
                                    <div class="text-[9px] text-slate-400 font-medium" v-if="item.accessories">Accs: {{ item.accessories }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-slate-500 font-semibold">Issued: {{ new Date(issuance.issued_at).toLocaleDateString() }}</div>
                                <div class="text-[#323130] font-bold mt-0.5" v-if="issuance.expected_return_date">Due: {{ new Date(issuance.expected_return_date).toLocaleDateString() }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <StatusBadge :status="issuance.status" />
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-col items-end gap-2.5">
                                    <div class="flex gap-1.5 select-none">
                                        <a :href="route('demo-issuances.pdf', { demoIssuance: issuance.id, variant: 'a4' })" target="_blank" class="text-slate-500 hover:text-[#0078d4] font-bold text-[9px] uppercase border border-[#e1dfdd] px-2 py-0.5 bg-white hover:bg-slate-50 rounded-sm">A4 Invoice</a>
                                        <a :href="route('demo-issuances.pdf', { demoIssuance: issuance.id, variant: 'pos' })" target="_blank" class="text-slate-500 hover:text-[#0078d4] font-bold text-[9px] uppercase border border-[#e1dfdd] px-2 py-0.5 bg-white hover:bg-slate-50 rounded-sm">POS Slip</a>
                                    </div>
                                    <div class="flex gap-4 items-center">
                                        <button v-if="issuance.status === 'issued'" @click="returningItem = issuance" class="text-emerald-600 hover:text-emerald-800 font-extrabold text-[10px] uppercase">Mark Return</button>
                                        <Link v-if="canEdit(issuance)" :href="route('demo-issuances.edit', issuance.id)" class="text-[#0078d4] hover:text-[#005a9e] font-extrabold text-[10px] uppercase">Edit</Link>
                                        <button v-if="canDelete()" @click="deleteIssuance(issuance.id)" class="text-red-500 hover:text-red-700 font-extrabold text-[10px] uppercase">Delete</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="issuances.data.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-[#a19f9d] font-bold italic">No matching records found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Return Modal (Optimized without blur) -->
        <Modal :show="!!returningItem" @close="returningItem = null">
            <div class="p-6 select-none">
                <h2 class="text-base font-extrabold text-[#201f1e] mb-4 uppercase">Confirm Return</h2>
                <div class="mb-4 text-xs font-bold text-emerald-800 bg-emerald-50 p-4 rounded border border-emerald-200">
                    Marking trial items from <span class="font-extrabold underline">{{ returningItem?.customer?.name }}</span> as fully returned.
                </div>
                <form @submit.prevent="submitReturn" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#323130] mb-1.5">Return notes / remarks</label>
                        <textarea v-model="returnForm.notes" class="input-field" rows="3" placeholder="Enter return remarks..."></textarea>
                    </div>
                    <div class="mt-6 flex justify-end gap-2.5">
                        <button type="button" @click="returningItem = null" class="btn-secondary">Cancel</button>
                        <button type="submit" class="px-5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-sm text-xs font-bold shadow-sm transition-all">Confirm Return</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
