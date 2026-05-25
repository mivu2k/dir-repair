<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    parts: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');

const user = computed(() => usePage().props.auth.user);
const role = computed(() => user.value?.roles?.[0]?.name || user.value?.role || 'staff');
const isAdmin = computed(() => role.value === 'admin');
const permissions = computed(() => user.value?.permissions || []);
const hasPermission = (permission) => isAdmin.value || permissions.value.includes(permission);
const canDelete = computed(() => hasPermission('delete parts'));

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
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-lg font-extrabold text-[#201f1e] mb-1">Inventory Matrix</h2>
                <p class="text-xs text-[#605e5c] font-semibold">Track and catalog system spare parts, components, and hardware inventory assets.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <div class="relative flex-1 md:flex-initial">
                    <input 
                        type="text" 
                        v-model="search" 
                        @input="handleSearch"
                        placeholder="Search SKU or name..." 
                        class="input-field w-full md:w-64"
                        style="padding-left: 2.25rem !important;"
                    />
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                </div>
                <Link 
                    v-if="hasPermission('create parts')"
                    :href="route('parts.create')"
                    class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-4 py-1.5 rounded-sm text-xs font-semibold shadow-sm transition-all text-center select-none"
                >
                    Add New Asset
                </Link>
            </div>
        </div>

        <!-- M365 Data Table Grid -->
        <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[600px] sm:min-w-full">
                    <thead class="bg-slate-50 border-b border-[#e1dfdd] text-[#605e5c] uppercase font-bold tracking-wide">
                        <tr>
                            <th class="px-4 py-3 select-none">SKU Identifier</th>
                            <th class="px-4 py-3 select-none">Part Description</th>
                            <th class="px-4 py-3 select-none">Brand/Model</th>
                            <th class="px-4 py-3 text-center select-none">Stock</th>
                            <th class="px-4 py-3 text-right select-none">Unit Price</th>
                            <th class="px-4 py-3 text-right select-none">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f3f2f1]">
                        <tr v-for="part in parts" :key="part.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-extrabold text-[#201f1e] font-mono tracking-tighter">{{ part.sku || '---' }}</td>
                            <td class="px-4 py-3 font-bold text-[#323130]">{{ part.name }}</td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-[#605e5c] uppercase">{{ part.brand || 'GENERIC' }}</span>
                                <span v-if="part.model" class="text-[10px] font-mono text-slate-400 ml-1">/ {{ part.model }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['px-1.5 py-0.5 rounded-sm text-[10px] font-bold font-mono border', 
                                    part.stock_quantity > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200']">
                                    {{ part.stock_quantity }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-[#201f1e] font-mono">{{ formatCurrency(part.price) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3 select-none">
                                    <Link :href="route('parts.edit', part.id)" class="text-[#0078d4] hover:text-[#005a9e] font-extrabold text-[10px] uppercase">Modify</Link>
                                    <button v-if="canDelete" @click="deletePart(part.id)" class="text-red-500 hover:text-red-700 font-extrabold text-[10px] uppercase">Wipe</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="parts.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-[#a19f9d] font-bold italic uppercase tracking-wider">Zero inventory nodes detected</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
