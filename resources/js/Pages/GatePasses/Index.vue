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
    passes: Object,
});

const isCreating = ref(false);

const form = useForm({
    type: 'inward',
    person_name: '',
    company_name: '',
    vehicle_number: '',
    items_description: '',
    notes: '',
});

const submit = () => {
    form.post(route('gate-passes.store'), {
        onSuccess: () => {
            isCreating.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Gate Passes" />

    <AuthenticatedLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Gate Passes</h2>
                <p class="text-sm text-slate-500 font-medium">Log inward and outward movement of goods.</p>
            </div>
            <button @click="isCreating = true" class="px-4 py-2 bg-slate-900 text-white rounded-md text-sm font-bold shadow hover:bg-slate-800">
                Create Gate Pass
            </button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-500 font-black tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Pass #</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Carrier / Person</th>
                            <th class="px-6 py-4">Items</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="pass in passes.data" :key="pass.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ pass.pass_number }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="pass.type === 'inward'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">Inward</span>
                                <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-orange-100 text-orange-800">Outward</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ pass.person_name }}</div>
                                <div class="text-xs text-slate-500">{{ pass.company_name || 'Individual' }} {{ pass.vehicle_number ? `(${pass.vehicle_number})` : '' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-700 max-w-xs truncate">{{ pass.items_description }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-500">{{ new Date(pass.created_at).toLocaleString() }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a :href="route('gate-passes.pdf', pass.id)" target="_blank" class="text-slate-600 hover:text-slate-900 font-bold text-xs uppercase tracking-wider">Print</a>
                            </td>
                        </tr>
                        <tr v-if="passes.data.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500 font-medium">No gate passes created yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="isCreating" @close="isCreating = false">
            <div class="p-6">
                <h2 class="text-lg font-black text-slate-900 mb-6 uppercase tracking-wider">Generate Gate Pass</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel value="Direction" />
                        <select v-model="form.type" class="w-full border-slate-200 rounded-md text-sm focus:border-slate-500 focus:ring-slate-500 shadow-sm" required>
                            <option value="inward">Inward (Receiving Goods)</option>
                            <option value="outward">Outward (Dispatching Goods)</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Person Name" />
                            <TextInput v-model="form.person_name" type="text" class="w-full" required />
                        </div>
                        <div>
                            <InputLabel value="Company/Vendor (Optional)" />
                            <TextInput v-model="form.company_name" type="text" class="w-full" />
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Vehicle Number (Optional)" />
                        <TextInput v-model="form.vehicle_number" type="text" class="w-full" />
                    </div>
                    <div>
                        <InputLabel value="Items Description" />
                        <textarea v-model="form.items_description" class="w-full border-slate-200 rounded-md text-sm focus:border-slate-500 focus:ring-slate-500 shadow-sm" rows="3" required placeholder="What exactly is moving?"></textarea>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="isCreating = false">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Generate Pass</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
