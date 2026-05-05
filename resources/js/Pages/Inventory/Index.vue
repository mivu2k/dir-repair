<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    parts: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const isAddingPart = ref(false);
const editingPart = ref(null);

const role = computed(() => usePage().props.auth.user?.roles?.[0]?.name || usePage().props.auth.user?.role || 'staff');
const isAdmin = computed(() => role.value === 'admin');
const canDelete = computed(() => isAdmin.value);

const form = useForm({
    sku: '',
    name: '',
    brand: '',
    model: '',
    price: 0,
    stock_quantity: 0,
});

const submit = () => {
    if (editingPart.value) {
        form.put(route('parts.update', editingPart.value.id), {
            onSuccess: () => {
                isAddingPart.value = false;
                editingPart.value = null;
                form.reset();
            },
        });
    } else {
        form.post(route('parts.store'), {
            onSuccess: () => {
                isAddingPart.value = false;
                form.reset();
            },
        });
    }
};

const editPart = (part) => {
    editingPart.value = part;
    form.sku = part.sku || '';
    form.name = part.name;
    form.brand = part.brand;
    form.model = part.model;
    form.price = part.price;
    form.stock_quantity = part.stock_quantity;
    isAddingPart.value = true;
};

const deletePart = (id) => {
    if (confirm('Remove this part?')) {
        router.delete(route('parts.destroy', id));
    }
};

const handleSearch = () => {
    router.get(route('parts.index'), { search: search.value }, { preserveState: true, replace: true });
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
    <Head title="Inventory Matrix" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="relative flex-1 max-w-sm">
                    <input 
                        type="text" 
                        v-model="search" 
                        @input="handleSearch"
                        placeholder="Search SKU or name..." 
                        class="input-field pl-8 py-2 text-[11px]"
                    >
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button 
                    @click="isAddingPart = true; editingPart = null; form.reset()"
                    class="btn-primary py-2 px-6 text-[10px]"
                >
                    Add New Asset
                </button>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-3 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">SKU Identifier</th>
                            <th class="px-3 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Part Description</th>
                            <th class="px-3 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Brand/Model</th>
                            <th class="px-3 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Stock</th>
                            <th class="px-3 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Unit Price</th>
                            <th class="px-3 py-2.5 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Ops</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="part in parts" :key="part.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-3 py-2 text-[10px] font-black text-slate-900 font-mono tracking-tighter">{{ part.sku || '---' }}</td>
                            <td class="px-3 py-2 text-[11px] font-bold text-slate-700">{{ part.name }}</td>
                            <td class="px-3 py-2">
                                <span class="text-[10px] font-black text-slate-400 uppercase">{{ part.brand || 'GENERIC' }}</span>
                                <span v-if="part.model" class="text-[9px] font-mono text-slate-400 ml-1">/ {{ part.model }}</span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span :class="['px-1.5 py-0.5 rounded text-[10px] font-black font-mono', part.stock_quantity > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600']">
                                    {{ part.stock_quantity }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-[11px] text-right font-black text-slate-900 font-mono">{{ formatCurrency(part.price) }}</td>
                            <td class="px-3 py-2 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button @click="editPart(part)" class="text-blue-600 hover:text-blue-800 font-black text-[9px] uppercase tracking-widest">Modify</button>
                                    <button v-if="canDelete" @click="deletePart(part.id)" class="text-slate-300 hover:text-red-600 font-black text-[9px] uppercase tracking-widest">Wipe</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="parts.length === 0">
                            <td colspan="6" class="px-3 py-12 text-center text-[10px] font-black text-slate-300 uppercase tracking-widest">Zero inventory nodes detected</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Matrix Entry Modal -->
        <div v-if="isAddingPart" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg border border-slate-200 shadow-2xl w-full max-w-md animate-slide-up">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50 rounded-t-lg">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-900">{{ editingPart ? 'Modify Inventory Asset' : 'Register New Asset' }}</span>
                    <button @click="isAddingPart = false" class="text-slate-400 hover:text-slate-900 text-lg">&times;</button>
                </div>
                <form @submit.prevent="submit" class="p-5 space-y-4">
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Unique SKU / Part #</label>
                            <input type="text" v-model="form.sku" class="input-field py-2 font-mono text-[11px]" placeholder="e.g. CP-65W-USB-C">
                            <div v-if="form.errors.sku" class="text-[9px] text-red-500 font-bold mt-1 uppercase">{{ form.errors.sku }}</div>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Formal Description</label>
                            <input type="text" v-model="form.name" required class="input-field py-2" placeholder="e.g. Charger Pin Replacement">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Manufacturer</label>
                                <input type="text" v-model="form.brand" class="input-field py-2" placeholder="Apple, HP, etc">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Model Ref</label>
                                <input type="text" v-model="form.model" class="input-field py-2" placeholder="A1466, etc">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Unit Pricing (PKR)</label>
                                <input type="number" v-model="form.price" required class="input-field py-2 font-mono" placeholder="0.00">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Initial Stock</label>
                                <input type="number" v-model="form.stock_quantity" class="input-field py-2 font-mono" placeholder="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="isAddingPart = false" class="flex-1 btn-secondary py-2 text-[10px]">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 btn-primary py-2 text-[10px]">
                            {{ editingPart ? 'Commit Changes' : 'Initialize Asset' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
