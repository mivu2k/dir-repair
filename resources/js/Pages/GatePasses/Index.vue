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
    passes: Object,
    filters: Object,
});

const isCreating = ref(false);
const editingPass = ref(null);
const search = ref(props.filters.search || '');

const form = useForm({
    type: 'inward',
    person_name: '',
    company_name: '',
    vehicle_number: '',
    items: [{ description: '', qty: 1, serial: '' }],
    notes: '',
});

watch(search, (value) => {
    router.get(route('gate-passes.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
});

const addItem = () => {
    form.items.push({ description: '', qty: 1, serial: '' });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submit = () => {
    if (editingPass.value) {
        form.put(route('gate-passes.update', editingPass.value.id), {
            onSuccess: () => {
                editingPass.value = null;
                form.reset();
            },
        });
    } else {
        form.post(route('gate-passes.store'), {
            onSuccess: () => {
                isCreating.value = false;
                form.reset();
            },
        });
    }
};

const editPass = (pass) => {
    editingPass.value = pass;
    form.type = pass.type;
    form.person_name = pass.person_name;
    form.company_name = pass.company_name;
    form.vehicle_number = pass.vehicle_number;
    form.items = Array.isArray(pass.items) ? JSON.parse(JSON.stringify(pass.items)) : [{ description: '', qty: 1, serial: '' }];
    form.notes = pass.notes;
};

const deletePass = (id) => {
    if (confirm('Are you sure you want to delete this gate pass?')) {
        router.delete(route('gate-passes.destroy', id));
    }
};
</script>

<template>
    <Head title="Gate Passes" />

    <AuthenticatedLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Gate Passes</h2>
                <p class="text-sm text-slate-500 font-medium">Log inward and outward movement of goods.</p>
            </div>
            <div class="flex gap-4">
                <TextInput v-model="search" type="text" placeholder="Search Pass #, Person, Items..." class="w-64 text-sm" />
                <button @click="isCreating = true; editingPass = null; form.reset()" class="px-4 py-2 bg-slate-900 text-white rounded-md text-sm font-bold shadow hover:bg-slate-800">
                    Create Gate Pass
                </button>
            </div>
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
                                <div v-for="(item, idx) in pass.items" :key="idx" class="text-xs font-bold text-slate-700">
                                    {{ item.qty }}x {{ item.description }}
                                    <span v-if="item.serial" class="text-[9px] text-slate-400 font-black block ml-4">S/N: {{ item.serial }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-500">{{ new Date(pass.created_at).toLocaleString() }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex gap-4 justify-end items-center">
                                    <div class="flex gap-2">
                                        <a :href="route('gate-passes.pdf', { gatePass: pass.id, variant: 'a4' })" target="_blank" class="text-slate-500 hover:text-slate-900 font-bold text-[10px] uppercase border px-2 py-1 rounded">A4</a>
                                        <a :href="route('gate-passes.pdf', { gatePass: pass.id, variant: 'pos' })" target="_blank" class="text-slate-500 hover:text-slate-900 font-bold text-[10px] uppercase border px-2 py-1 rounded">POS</a>
                                    </div>
                                    <button @click="editPass(pass)" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase">Edit</button>
                                    <button @click="deletePass(pass.id)" class="text-red-400 hover:text-red-600 font-bold text-xs uppercase">Del</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="passes.data.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500 font-medium italic">No matching records found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :show="isCreating || !!editingPass" @close="isCreating = false; editingPass = null; form.reset()" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-black text-slate-900 mb-6 uppercase tracking-wider">{{ editingPass ? 'Edit' : 'Create' }} Gate Pass</h2>
                <form @submit.prevent="submit" class="space-y-6">
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
                            <InputLabel value="Company/Vendor" />
                            <TextInput v-model="form.company_name" type="text" class="w-full" />
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Vehicle Number" />
                        <TextInput v-model="form.vehicle_number" type="text" class="w-full" />
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest">Items Detail</h3>
                            <button type="button" @click="addItem" class="text-[10px] font-black uppercase text-indigo-600 hover:text-indigo-800 underline">Add Item</button>
                        </div>
                        
                        <div v-for="(item, index) in form.items" :key="index" class="p-3 bg-slate-50 rounded-lg border border-slate-100 relative group">
                            <button v-if="form.items.length > 1" type="button" @click="removeItem(index)" class="absolute top-1 right-1 text-slate-400 hover:text-red-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <InputLabel value="Item Description" />
                                    <TextInput v-model="item.description" type="text" class="w-full text-xs" required />
                                </div>
                                <div class="flex-1">
                                    <InputLabel value="Serial Number" />
                                    <TextInput v-model="item.serial" type="text" class="w-full text-xs" />
                                </div>
                                <div class="w-16">
                                    <InputLabel value="Qty" />
                                    <TextInput v-model="item.qty" type="number" step="0.1" class="w-full text-xs" required />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <InputLabel value="General Notes" />
                        <TextInput v-model="form.notes" type="text" class="w-full" />
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="isCreating = false; editingPass = null; form.reset()">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save Pass</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
