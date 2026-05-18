<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    issuances: Object,
    customers: Array,
    filters: Object,
});

const isCreating = ref(false);
const editingIssuance = ref(null);
const returningItem = ref(null);
const search = ref(props.filters.search || '');

const form = useForm({
    customer_id: '',
    items: [{ name: '', serial: '', accessories: '' }],
    expected_return_date: '',
    notes: '',
});

const returnForm = useForm({
    notes: '',
});

watch(search, (value) => {
    router.get(route('demo-issuances.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
});

const addItem = () => {
    form.items.push({ name: '', serial: '', accessories: '' });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submit = () => {
    if (editingIssuance.value) {
        form.put(route('demo-issuances.update', editingIssuance.value.id), {
            onSuccess: () => {
                editingIssuance.value = null;
                form.reset();
            },
        });
    } else {
        form.post(route('demo-issuances.store'), {
            onSuccess: () => {
                isCreating.value = false;
                form.reset();
            },
        });
    }
};

const editIssuance = (issuance) => {
    editingIssuance.value = issuance;
    form.customer_id = issuance.customer_id;
    form.items = JSON.parse(JSON.stringify(issuance.items));
    form.expected_return_date = issuance.expected_return_date;
    form.notes = issuance.notes;
};

const deleteIssuance = (id) => {
    if (confirm('Are you sure you want to delete this issuance?')) {
        router.delete(route('demo-issuances.destroy', id));
    }
};

const submitReturn = () => {
    returnForm.post(route('demo-issuances.return', returningItem.value.id), {
        onSuccess: () => {
            returningItem.value = null;
            returnForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Demo Issuances" />

    <AuthenticatedLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Demo Goods</h2>
                <p class="text-sm text-slate-500 font-medium">Manage units issued to customers for trial.</p>
            </div>
            <div class="flex gap-4">
                <TextInput v-model="search" type="text" placeholder="Search Demo #, Client, Serial..." class="w-64 text-sm" />
                <button @click="isCreating = true; editingIssuance = null; form.reset()" class="px-4 py-2 bg-slate-900 text-white rounded-md text-sm font-bold shadow hover:bg-slate-800">
                    Issue Demo Items
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500 font-black tracking-wider">
                        <tr>
                            <th class="px-6 py-4">ID / Customer</th>
                            <th class="px-6 py-4">Items</th>
                            <th class="px-6 py-4">Timeline</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="issuance in issuances.data" :key="issuance.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 align-top">
                                <div class="font-bold text-slate-900">{{ issuance.issuance_number }}</div>
                                <div class="text-slate-500">{{ issuance.customer.name }}</div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div v-for="(item, idx) in issuance.items" :key="idx" class="mb-2 last:mb-0 border-l-2 border-slate-200 pl-2">
                                    <div class="font-bold text-slate-900">{{ item.name }}</div>
                                    <div class="text-[10px] text-slate-500 uppercase font-bold" v-if="item.serial">SN: {{ item.serial }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="text-xs text-slate-500">Issued: {{ new Date(issuance.issued_at).toLocaleDateString() }}</div>
                                <div class="text-xs font-bold text-slate-700" v-if="issuance.expected_return_date">Due: {{ new Date(issuance.expected_return_date).toLocaleDateString() }}</div>
                            </td>
                            <td class="px-6 py-4 align-top text-center">
                                <StatusBadge :status="issuance.status" />
                            </td>
                            <td class="px-6 py-4 text-right align-top space-x-3">
                                <div class="flex flex-col items-end gap-2">
                                    <div class="flex gap-2">
                                        <a :href="route('demo-issuances.pdf', { demoIssuance: issuance.id, variant: 'a4' })" target="_blank" class="text-slate-500 hover:text-slate-900 font-bold text-[10px] uppercase border px-2 py-1 rounded">A4</a>
                                        <a :href="route('demo-issuances.pdf', { demoIssuance: issuance.id, variant: 'pos' })" target="_blank" class="text-slate-500 hover:text-slate-900 font-bold text-[10px] uppercase border px-2 py-1 rounded">POS</a>
                                    </div>
                                    <div class="flex gap-4 items-center">
                                        <button v-if="issuance.status === 'issued'" @click="returningItem = issuance" class="text-emerald-600 hover:text-emerald-900 font-bold text-xs uppercase">Return</button>
                                        <button @click="editIssuance(issuance)" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase">Edit</button>
                                        <button @click="deleteIssuance(issuance.id)" class="text-red-400 hover:text-red-600 font-bold text-xs uppercase">Del</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="issuances.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 font-medium italic">No matching records found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Issue/Edit Modal -->
        <Modal :show="isCreating || !!editingIssuance" @close="isCreating = false; editingIssuance = null; form.reset()" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-black text-slate-900 mb-6 uppercase tracking-wider">{{ editingIssuance ? 'Edit' : 'Issue' }} Demo Items</h2>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel value="Customer" />
                        <select v-model="form.customer_id" class="w-full border-slate-200 rounded-md text-sm focus:border-slate-500 focus:ring-slate-500 shadow-sm" required>
                            <option value="">Select a customer...</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.phone }})</option>
                        </select>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest">Items Detail</h3>
                            <button type="button" @click="addItem" class="text-[10px] font-black uppercase text-indigo-600 hover:text-indigo-800 underline">Add Another</button>
                        </div>
                        
                        <div v-for="(item, index) in form.items" :key="index" class="p-4 bg-slate-50 rounded-lg border border-slate-100 relative group">
                            <button v-if="form.items.length > 1" type="button" @click="removeItem(index)" class="absolute top-2 right-2 text-slate-400 hover:text-red-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <InputLabel value="Item Name" />
                                    <TextInput v-model="item.name" type="text" class="w-full text-xs" required />
                                </div>
                                <div>
                                    <InputLabel value="Serial Number" />
                                    <TextInput v-model="item.serial" type="text" class="w-full text-xs" />
                                </div>
                                <div>
                                    <InputLabel value="Accessories" />
                                    <TextInput v-model="item.accessories" type="text" class="w-full text-xs" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-t pt-6">
                        <div>
                            <InputLabel value="Expected Return Date" />
                            <TextInput v-model="form.expected_return_date" type="date" class="w-full" />
                        </div>
                        <div>
                            <InputLabel value="General Notes" />
                            <TextInput v-model="form.notes" type="text" class="w-full" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="isCreating = false; editingIssuance = null; form.reset()">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save Record</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Return Modal -->
        <Modal :show="!!returningItem" @close="returningItem = null">
            <div class="p-6">
                <h2 class="text-lg font-black text-slate-900 mb-6 uppercase tracking-wider">Confirm Return</h2>
                <div class="mb-4 text-sm text-slate-600 bg-slate-50 p-4 rounded border border-slate-100">
                    Marking items from <span class="font-bold">{{ returningItem?.customer?.name }}</span> as returned.
                </div>
                <form @submit.prevent="submitReturn" class="space-y-4">
                    <div>
                        <InputLabel value="Return Notes" />
                        <textarea v-model="returnForm.notes" class="w-full border-slate-200 rounded-md text-sm focus:border-slate-500 focus:ring-slate-500 shadow-sm" rows="3"></textarea>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="returningItem = null">Cancel</SecondaryButton>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-bold shadow hover:bg-emerald-700">Confirm Return</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
