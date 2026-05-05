<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    initialJob: Object,
    initialIntake: Object,
    suggestedItems: Array,
    inventoryParts: Array,
});

const form = useForm({
    repair_job_id: props.initialJob?.id || '',
    intake_id: props.initialIntake?.id || '',
    date: new Date().toISOString().split('T')[0],
    valid_until: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    reference: '',
    subject: '',
    notes: 'Quotation valid for 30 days.',
    items: props.suggestedItems.length > 0 
        ? JSON.parse(JSON.stringify(props.suggestedItems)).map(item => ({ 
            item_type: item.item_type || 'part',
            description: item.description || '',
            quantity: item.quantity || 1,
            unit_price: item.unit_price || 0,
            discount: 0, 
            part_id: item.part_id || null, 
            repair_job_id: item.repair_job_id || props.initialJob?.id || '' 
        })) 
        : [{ item_type: 'part', description: '', quantity: 1, unit_price: 0, discount: 0, part_id: null, repair_job_id: props.initialJob?.id || '' }],
});

const addItem = () => {
    form.items.push({ item_type: 'part', description: '', quantity: 1, unit_price: 0, discount: 0, part_id: null, repair_job_id: props.initialJob?.id || '' });
};

const addPartFromInventory = (part) => {
    form.items.push({
        item_type: 'part',
        description: part.name,
        quantity: 1,
        unit_price: part.price,
        discount: 0,
        part_id: part.id,
        repair_job_id: props.initialJob?.id || '',
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.quantity * item.unit_price) - item.discount, 0);
});

const submit = () => {
    form.post(route('quotations.store'));
};

const showInventory = ref(false);

const getJobNumber = (jobId) => {
    if (!jobId) return 'N/A';
    const job = props.initialIntake?.repair_jobs?.find(j => j.id === jobId);
    return job?.job_number || props.initialJob?.job_number || 'Linked';
};
</script>

<template>
    <Head title="Build Quotation" />

    <AuthenticatedLayout>
        <div class="space-y-4 max-w-5xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Build Financial Scenario</h2>
                    <span class="text-[10px] font-black text-slate-900 uppercase mt-1">
                        {{ initialIntake ? `Merge Intake: ${initialIntake.intake_number}` : (initialJob ? `Repair Job: ${initialJob.job_number}` : 'General Quotation') }}
                    </span>
                </div>
                <button @click="submit" :disabled="form.processing" class="btn-primary py-1">Deploy Quotation</button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Header Controls -->
                <div class="bg-white border border-slate-200 rounded-lg p-3 grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Master Entity</label>
                        <div class="text-[11px] font-bold text-slate-900 bg-slate-50 px-2 py-1.5 rounded border border-slate-100 font-mono">
                            {{ initialJob?.job_number || initialIntake?.intake_number || 'General Matrix' }}
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Temporal Entry</label>
                        <input type="date" v-model="form.date" class="input-field py-1.5">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Validity Limit</label>
                        <input type="date" v-model="form.valid_until" class="input-field py-1.5">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Subject Reference</label>
                        <input type="text" v-model="form.subject" placeholder="e.g. Logic Board Batch" class="input-field py-1.5">
                    </div>
                </div>

                <!-- Product Matrix -->
                <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Product / Job ID</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest w-16 text-center">Qty</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 text-right">Unit Price</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest w-20 text-right">Disc.</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 text-right">Amount</th>
                                <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <input type="text" v-model="item.description" placeholder="Description..." class="bg-transparent border-none p-0 text-[11px] font-bold focus:ring-0 w-full">
                                            <span v-if="initialIntake" class="text-[9px] font-black text-blue-600 font-mono bg-blue-50 px-1 rounded">{{ getJobNumber(item.repair_job_id) }}</span>
                                        </div>
                                        <div class="flex gap-2">
                                            <select v-model="item.item_type" class="bg-transparent border-none p-0 text-[9px] font-black uppercase text-slate-400 focus:ring-0 cursor-pointer">
                                                <option value="part">Hardware</option>
                                                <option value="labor">Labor</option>
                                                <option value="misc">Miscellaneous</option>
                                            </select>
                                            <span v-if="item.part_id" class="text-[8px] font-black text-emerald-500 uppercase">Inventory Asset</span>
                                            
                                            <!-- Job Selector for Merged Quotes -->
                                            <select v-if="initialIntake" v-model="item.repair_job_id" class="bg-transparent border-none p-0 text-[9px] font-black uppercase text-slate-500 focus:ring-0 cursor-pointer">
                                                <option value="">Global/No Job</option>
                                                <option v-for="job in initialIntake.repair_jobs" :key="job.id" :value="job.id">{{ job.job_number }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" v-model="item.quantity" class="bg-transparent border-none p-0 text-[11px] text-center focus:ring-0 w-full" step="1">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" v-model="item.unit_price" class="bg-transparent border-none p-0 text-[11px] text-right focus:ring-0 w-full font-mono" step="1">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" v-model="item.discount" class="bg-transparent border-none p-0 text-[11px] text-right focus:ring-0 w-full font-mono" step="1">
                                </td>
                                <td class="px-3 py-2 text-right text-[11px] font-black text-slate-900 font-mono">
                                    {{ Number(item.quantity * item.unit_price - item.discount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" @click="removeItem(index)" class="text-slate-300 hover:text-red-500 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="px-3 py-2 bg-slate-50 flex items-center justify-between border-t border-slate-100">
                        <div class="flex gap-2">
                            <button type="button" @click="addItem" class="btn-secondary py-1 text-[9px]">Add Row</button>
                            <button type="button" @click="showInventory = !showInventory" class="btn-secondary py-1 text-[9px] border-blue-200 text-blue-600">Inventory Matrix</button>
                        </div>
                        <div class="text-[11px] font-black text-slate-900 font-mono">
                            Subtotal: {{ Number(subtotal).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                        </div>
                    </div>
                </div>

                <!-- Inventory Matrix -->
                <div v-if="showInventory" class="bg-slate-900 rounded-lg p-3 space-y-2 animate-slide-up">
                    <div class="flex justify-between items-center">
                        <h4 class="text-[9px] font-black text-white uppercase tracking-widest">Inventory Assets</h4>
                        <button @click="showInventory = false" class="text-slate-500 hover:text-white">&times;</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                        <button 
                            v-for="part in inventoryParts" 
                            :key="part.id"
                            type="button"
                            @click="addPartFromInventory(part); showInventory = false"
                            class="text-left p-2 bg-slate-800 border border-slate-700 rounded hover:border-blue-500 hover:bg-slate-700 transition-all group"
                        >
                            <div class="text-[10px] font-bold text-white group-hover:text-blue-400 truncate">{{ part.name }}</div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-[8px] text-slate-500 uppercase">{{ part.brand }}</span>
                                <span class="text-[10px] font-black text-emerald-500 font-mono">{{ Math.round(part.price) }}</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Footer Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Operational Notes</label>
                        <textarea v-model="form.notes" rows="2" class="input-field text-[11px]" placeholder="Terms and conditions..."></textarea>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col justify-center items-end">
                        <div class="flex items-center gap-6">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Accumulation</span>
                            <span class="text-2xl font-black text-slate-900 font-mono tracking-tighter">PKR {{ Number(subtotal).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
                        </div>
                        <div class="mt-2 text-[9px] font-black text-emerald-500 uppercase tracking-widest italic">
                            Validated for Deployment
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
