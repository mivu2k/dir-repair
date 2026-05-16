<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
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
});

const isCreating = ref(false);
const returningItem = ref(null);

const form = useForm({
    customer_id: '',
    item_name: '',
    serial_number: '',
    accessories_included: '',
    expected_return_date: '',
    notes: '',
});

const returnForm = useForm({
    notes: '',
});

const submit = () => {
    form.post(route('demo-issuances.store'), {
        onSuccess: () => {
            isCreating.value = false;
            form.reset();
        },
    });
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
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Demo Goods</h2>
                <p class="text-sm text-slate-500 font-medium">Manage items issued to customers for trial.</p>
            </div>
            <button @click="isCreating = true" class="px-4 py-2 bg-slate-900 text-white rounded-md text-sm font-bold shadow hover:bg-slate-800">
                Issue Demo Item
            </button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500 font-black tracking-wider">
                        <tr>
                            <th class="px-6 py-4">ID / Customer</th>
                            <th class="px-6 py-4">Item Details</th>
                            <th class="px-6 py-4">Timeline</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="issuance in issuances.data" :key="issuance.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ issuance.issuance_number }}</div>
                                <div class="text-slate-500">{{ issuance.customer.name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ issuance.item_name }}</div>
                                <div class="text-xs text-slate-500">SN: {{ issuance.serial_number || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-500">Issued: {{ new Date(issuance.issued_at).toLocaleDateString() }}</div>
                                <div class="text-xs font-bold text-slate-700" v-if="issuance.expected_return_date">Due: {{ new Date(issuance.expected_return_date).toLocaleDateString() }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <StatusBadge :status="issuance.status" />
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button v-if="issuance.status === 'issued'" @click="returningItem = issuance" class="text-emerald-600 hover:text-emerald-900 font-bold text-xs uppercase tracking-wider">Mark Returned</button>
                            </td>
                        </tr>
                        <tr v-if="issuances.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 font-medium">No demo items issued yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Issue Modal -->
        <Modal :show="isCreating" @close="isCreating = false">
            <div class="p-6">
                <h2 class="text-lg font-black text-slate-900 mb-6 uppercase tracking-wider">Issue Demo Item</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel value="Customer" />
                        <select v-model="form.customer_id" class="w-full border-slate-200 rounded-md text-sm focus:border-slate-500 focus:ring-slate-500 shadow-sm" required>
                            <option value="">Select a customer...</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.phone }})</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Item Name" />
                        <TextInput v-model="form.item_name" type="text" class="w-full" required placeholder="e.g. Dell XPS 15 Test Unit" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Serial Number" />
                            <TextInput v-model="form.serial_number" type="text" class="w-full" />
                        </div>
                        <div>
                            <InputLabel value="Expected Return Date" />
                            <TextInput v-model="form.expected_return_date" type="date" class="w-full" />
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Accessories Included" />
                        <TextInput v-model="form.accessories_included" type="text" class="w-full" placeholder="e.g. Charger, Mouse" />
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="isCreating = false">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Issue Item</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Return Modal -->
        <Modal :show="!!returningItem" @close="returningItem = null">
            <div class="p-6">
                <h2 class="text-lg font-black text-slate-900 mb-6 uppercase tracking-wider">Return Demo Item</h2>
                <div class="mb-4 text-sm text-slate-600 bg-slate-50 p-4 rounded border border-slate-100">
                    You are marking <span class="font-bold">{{ returningItem?.item_name }}</span> as returned from <span class="font-bold">{{ returningItem?.customer?.name }}</span>.
                </div>
                <form @submit.prevent="submitReturn" class="space-y-4">
                    <div>
                        <InputLabel value="Return Notes (Optional)" />
                        <textarea v-model="returnForm.notes" class="w-full border-slate-200 rounded-md text-sm focus:border-slate-500 focus:ring-slate-500 shadow-sm" rows="3" placeholder="Condition upon return?"></textarea>
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
