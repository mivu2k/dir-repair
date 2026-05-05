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
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="relative flex-1 max-w-sm">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Search clients..." 
                        class="input-field pl-8"
                    >
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <Link :href="route('customers.create')" class="btn-primary">New Client</Link>
            </div>
            
            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Name</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Organization</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">Jobs</th>
                            <th class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Ops</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr 
                            v-for="customer in customers.data" 
                            :key="customer.id" 
                            class="hover:bg-slate-50 transition-colors cursor-pointer"
                            @click="router.visit(route('customers.show', customer.id))"
                        >
                            <td class="px-3 py-2 text-[11px] font-bold text-slate-900">{{ customer.name }}</td>
                            <td class="px-3 py-2 text-[11px] text-slate-600 font-mono">{{ customer.phone }}</td>
                            <td class="px-3 py-2 text-[11px] text-slate-500">{{ customer.organization || '---' }}</td>
                            <td class="px-3 py-2 text-[11px] text-slate-500 font-bold">{{ customer.repair_jobs_count }}</td>
                            <td class="px-3 py-2 text-right">
                                <Link :href="route('customers.show', customer.id)" @click.stop class="text-blue-600 hover:underline font-bold text-[10px]">Open</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="px-3 py-2 border-t border-slate-100 flex justify-between items-center bg-slate-50" v-if="customers.links">
                    <span class="text-[9px] font-bold text-slate-400 uppercase">{{ customers.total }} Entities</span>
                    <div class="flex gap-1">
                        <Link 
                            v-for="(link, i) in customers.links" 
                            :key="i" 
                            :href="link.url || '#'" 
                            :class="[
                                'px-1.5 py-0.5 rounded text-[9px] font-black transition-all border', 
                                link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50',
                                !link.url && 'opacity-30'
                            ]" 
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
