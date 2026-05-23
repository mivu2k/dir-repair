<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(route('customers.index'), { search: value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
});
</script>

<template>
    <Head title="Clients" />

    <AuthenticatedLayout>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-lg font-extrabold text-[#201f1e] mb-1">Client Registry</h2>
                <p class="text-xs text-[#605e5c] font-semibold">Manage and maintain active client profiles and communication records.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <div class="relative flex-1 md:flex-initial">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search clients..."
                        class="input-field w-full md:w-64"
                        style="padding-left: 2.25rem !important;"
                    />
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                </div>
                <Link
                    :href="route('customers.create')"
                    class="bg-[#0078d4] hover:bg-[#005a9e] text-white px-4 py-1.5 rounded-sm text-xs font-semibold shadow-sm transition-all text-center select-none"
                >
                    New Client
                </Link>
            </div>
        </div>

        <!-- M365 Data Grid Styled Table -->
        <div class="bg-white border border-[#e1dfdd] rounded-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[600px] sm:min-w-full">
                    <thead class="bg-slate-50 border-b border-[#e1dfdd] text-[#605e5c] uppercase font-bold tracking-wide">
                        <tr>
                            <th class="px-4 py-3 select-none">Client Name</th>
                            <th class="px-4 py-3 select-none">Phone</th>
                            <th class="px-4 py-3 select-none">Organization</th>
                            <th class="px-4 py-3 text-center select-none">Jobs</th>
                            <th class="px-4 py-3 text-right select-none">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f3f2f1]">
                        <tr
                            v-for="customer in customers.data"
                            :key="customer.id"
                            class="hover:bg-slate-50 transition-colors cursor-pointer"
                            @click="router.visit(route('customers.show', customer.id))"
                        >
                            <td class="px-4 py-3">
                                <div class="font-extrabold text-[#201f1e]">{{ customer.name }}</div>
                                <div v-if="customer.email" class="text-[10px] text-[#605e5c] font-semibold mt-0.5">{{ customer.email }}</div>
                            </td>
                            <td class="px-4 py-3 font-mono font-bold text-[#323130]">{{ customer.phone }}</td>
                            <td class="px-4 py-3 text-[#605e5c] font-semibold">{{ customer.organization || '---' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-sm text-[10px] font-bold border bg-slate-50 text-slate-600 border-slate-200">
                                    {{ customer.repair_jobs_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link
                                    :href="route('customers.show', customer.id)"
                                    @click.stop
                                    class="text-[#0078d4] hover:text-[#005a9e] font-extrabold text-[10px] uppercase"
                                >
                                    Open
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="customers.data.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-[#a19f9d] font-bold italic">No matching client records found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-2.5 border-t border-[#e1dfdd] flex justify-between items-center bg-slate-50" v-if="customers.links">
                <span class="text-[9px] font-bold text-[#605e5c] uppercase tracking-wider">{{ customers.total }} Total Clients</span>
                <div class="flex gap-1">
                    <Link
                        v-for="(link, i) in customers.links"
                        :key="i"
                        :href="link.url || '#'"
                        :class="[
                            'px-2 py-0.5 rounded-sm text-[9px] font-black transition-all border',
                            link.active ? 'bg-[#0078d4] text-white border-[#0078d4]' : 'bg-white border-[#e1dfdd] text-[#605e5c] hover:bg-slate-50',
                            !link.url && 'opacity-30 pointer-events-none'
                        ]"
                        v-html="link.label"
                    ></Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
