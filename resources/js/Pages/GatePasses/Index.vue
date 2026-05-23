<script setup>
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    passes: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('gate-passes.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
});

const deletePass = (id) => {
    if (confirm('Are you sure you want to delete this gate pass?')) {
        router.delete(route('gate-passes.destroy', id));
    }
};
</script>

<template>
    <Head title="Gate Passes" />

    <AuthenticatedLayout>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-lg font-extrabold text-[#201f1e] mb-1">Gate Pass Registry</h2>
                <p class="text-xs text-[#605e5c] font-semibold font-medium">Log inward and outward movement of client goods and assets securely.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <div class="relative flex-1 md:flex-initial">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Search pass #, person, items..." 
                        class="input-field w-full md:w-64"
                        style="padding-left: 2.25rem !important;"
                    />
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                </div>
                <Link 
                    :href="route('gate-passes.create')" 
                    class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-4 py-1.5 rounded-sm text-xs font-semibold shadow-sm transition-all text-center select-none whitespace-nowrap"
                >
                    Create Gate Pass
                </Link>
            </div>
        </div>

        <!-- M365 Data Grid Styled Table -->
        <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[700px] sm:min-w-full">
                    <thead class="bg-slate-50 border-b border-[#e1dfdd] text-[#605e5c] uppercase font-bold tracking-wide">
                        <tr>
                            <th class="px-4 py-3 select-none">Pass Identifier</th>
                            <th class="px-4 py-3 select-none">Direction Type</th>
                            <th class="px-4 py-3 select-none">Carrier / Contact coordinates</th>
                            <th class="px-4 py-3 select-none">Items & Quantities</th>
                            <th class="px-4 py-3 select-none">Timestamp</th>
                            <th class="px-4 py-3 text-right select-none">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f3f2f1]">
                        <tr v-for="pass in passes.data" :key="pass.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-extrabold text-[#201f1e]">{{ pass.pass_number }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="pass.type === 'inward'" class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">Inward</span>
                                <span v-else class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200">Outward</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-[#201f1e]">{{ pass.person_name }}</div>
                                <div class="text-[10px] text-[#605e5c] mt-0.5">{{ pass.company_name || 'Individual carrier' }} {{ pass.vehicle_number ? `(${pass.vehicle_number})` : '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div v-for="(item, idx) in pass.items" :key="idx" class="text-xs font-bold text-slate-700 mb-1 last:mb-0">
                                    {{ item.qty }}x {{ item.description }}
                                    <span v-if="item.serial" class="text-[9.5px] text-slate-400 font-mono block ml-4">S/N: {{ item.serial }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-semibold text-[#605e5c]">
                                <div>{{ new Date(pass.created_at).toLocaleString() }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex gap-4 justify-end items-center">
                                    <div class="flex gap-1.5 select-none">
                                        <a :href="route('gate-passes.pdf', { gatePass: pass.id, variant: 'a4' })" target="_blank" class="text-slate-500 hover:text-[#0078d4] font-bold text-[9px] uppercase border border-[#e1dfdd] px-2 py-0.5 bg-white hover:bg-slate-50 rounded-sm">A4</a>
                                        <a :href="route('gate-passes.pdf', { gatePass: pass.id, variant: 'pos' })" target="_blank" class="text-slate-500 hover:text-[#0078d4] font-bold text-[9px] uppercase border border-[#e1dfdd] px-2 py-0.5 bg-white hover:bg-slate-50 rounded-sm">POS</a>
                                    </div>
                                    <Link :href="route('gate-passes.edit', pass.id)" class="text-[#0078d4] hover:text-[#005a9e] font-extrabold text-[10px] uppercase">Edit</Link>
                                    <button @click="deletePass(pass.id)" class="text-red-500 hover:text-red-700 font-extrabold text-[10px] uppercase">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="passes.data.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-[#a19f9d] font-bold italic">No matching records found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
