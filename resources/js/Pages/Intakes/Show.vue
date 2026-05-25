<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    intake: Object,
});

const intakeForm = useForm({
    notes: props.intake.notes || '',
    payment_method: props.intake.payment_method || 'cash',
});

const statusForm = useForm({
    status: 'diagnosing',
    note: '',
});

const isEditing = ref(false);

const user = computed(() => usePage().props.auth.user);
const role = computed(() => user.value?.roles?.[0]?.name || user.value?.role || 'staff');
const isAdmin = computed(() => role.value === 'admin');
const isManager = computed(() => role.value === 'manager');
const isTechnician = computed(() => role.value === 'technician');

const permissions = computed(() => user.value?.permissions || []);
const hasPermission = (permission) => isAdmin.value || permissions.value.includes(permission);

const canModify = computed(() => hasPermission('edit intakes'));
const canDelete = computed(() => hasPermission('delete intakes'));

const updateIntake = () => {
    intakeForm.patch(route('intakes.update', props.intake.id), {
        preserveScroll: true,
        onSuccess: () => isEditing.value = false,
    });
};

const deleteIntake = () => {
    if (confirm('CAUTION: This will permanently purge this intake batch and ALL linked repair jobs from the matrix. Continue?')) {
        router.delete(route('intakes.destroy', props.intake.id));
    }
};

const updateBulkStatus = () => {
    statusForm.post(route('intakes.status.update', props.intake.id), {
        preserveScroll: true,
        onSuccess: () => statusForm.note = '',
    });
};
</script>

<template>
    <Head :title="'Intake ' + intake.intake_number" />

    <AuthenticatedLayout>
        <div class="space-y-4 max-w-5xl mx-auto">
            <!-- Header Ops -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-col">
                    <h1 class="text-lg font-black text-slate-900 font-mono tracking-tight">{{ intake.intake_number }}</h1>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Lifecycle Origin: {{ new Date(intake.received_at).toLocaleDateString() }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button v-if="canModify" @click="isEditing = !isEditing" class="btn-secondary py-1 text-[10px] font-black uppercase tracking-widest">Modify Batch</button>
                    <button v-if="canDelete" @click="deleteIntake" class="btn-secondary py-1 text-red-500 border-red-100 text-[10px] font-black uppercase tracking-widest">Wipe Batch</button>
                    <div class="relative group">
                        <button class="btn-primary py-1 px-4">Print Ops</button>
                        <div class="absolute right-0 mt-1 w-52 bg-white border border-slate-200 rounded shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                            <a :href="route('intakes.pdf', intake.id)" target="_blank" class="block px-3 py-2 text-[10px] font-black text-slate-600 hover:bg-slate-50 uppercase tracking-widest">Full Summary (A4)</a>
                            <a :href="route('intakes.stickers', intake.id)" target="_blank" class="block px-3 py-2 text-[10px] font-black text-slate-600 hover:bg-slate-50 uppercase tracking-widest border-t border-slate-100">QR Stickers</a>
                            <a :href="route('jobs.pos', { job: intake.id, type: 'intake_summary' })" target="_blank" class="block px-3 py-2 text-[10px] font-black text-slate-600 hover:bg-slate-50 uppercase tracking-widest border-t border-slate-100">POS Receipt (80mm)</a>
                        </div>
                    </div>
                    <Link :href="route('quotations.create', { intake_id: intake.id })" class="btn-secondary py-1 text-blue-600 border-blue-200 text-[10px] font-black uppercase tracking-widest">Merged Quote</Link>
                </div>
            </div>

            <!-- Edit Matrix (Conditional) -->
            <div v-if="isEditing" class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-lg p-4 animate-slide-down">
                <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-3">Update Intake Parameters</h3>
                <form @submit.prevent="updateIntake" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Settlement Logic</label>
                        <select v-model="intakeForm.payment_method" class="input-field py-1.5 text-[11px] font-bold">
                            <option value="cash">Cash</option>
                            <option value="credit">Credit</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="warranty">Warranty</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Batch Notes</label>
                        <input v-model="intakeForm.notes" type="text" class="input-field py-1.5 text-[11px]" placeholder="Collective notes...">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" :disabled="intakeForm.processing" class="btn-primary py-1.5 flex-1 text-[10px]">Commit Changes</button>
                        <button type="button" @click="isEditing = false" class="btn-secondary py-1.5 px-4 text-[10px]">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Ownership & Meta Matrix -->
            <div class="bg-white border border-slate-200 rounded-lg p-3 grid grid-cols-2 md:grid-cols-4 gap-4 shadow-sm">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Ownership Entity</p>
                    <p class="text-[11px] font-bold text-slate-900 mt-1">{{ intake.customer.name }}</p>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Reception Staff</p>
                    <p class="text-[11px] font-bold text-slate-900 mt-1 uppercase">{{ intake.received_by.name }}</p>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Settlement Logic</p>
                    <p class="text-[11px] font-bold text-slate-900 mt-1 uppercase">{{ intake.payment_method }}</p>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">System Timestamp</p>
                    <p class="text-[11px] font-bold text-slate-900 mt-1 font-mono tracking-tighter">{{ new Date(intake.received_at).toLocaleTimeString() }}</p>
                </div>
            </div>

            <!-- Bulk State Control -->
            <div v-if="canModify" class="bg-slate-900 rounded-lg p-4 border border-slate-800 shadow-lg">
                <h3 class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Batch Lifecycle Sync</h3>
                <form @submit.prevent="updateBulkStatus" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-slate-600 uppercase tracking-widest">Target State</label>
                        <select v-model="statusForm.status" class="w-full bg-slate-800 border-slate-700 rounded px-2.5 py-2 text-[11px] font-black text-white focus:ring-blue-500 capitalize outline-none">
                            <option v-for="s in ['received', 'diagnosing', 'waiting_approval', 'in_progress', 'completed', 'delivered', 'cancelled']" :key="s" :value="s">{{ s.replace('_', ' ') }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[8px] font-black text-slate-600 uppercase tracking-widest">Batch Status Note</label>
                        <input v-model="statusForm.note" type="text" class="w-full bg-slate-800 border-slate-700 rounded px-2.5 py-2 text-[11px] text-white placeholder:text-slate-600 outline-none" placeholder="Applies to all linked jobs...">
                    </div>
                    <button type="submit" :disabled="statusForm.processing" class="bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-[10px] font-black uppercase tracking-widest transition-all shadow-lg">Sync All Nodes</button>
                </form>
            </div>

            <!-- Linked Entities Pipeline -->
            <div class="space-y-3">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Linked Operational Units ({{ intake.repair_jobs.length }})</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="job in intake.repair_jobs" :key="job.id" class="group bg-white border border-slate-200 rounded-lg p-4 hover:border-blue-400 hover:shadow-md transition-all">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex flex-col">
                                <Link :href="route('jobs.show', job.job_number)" class="text-[12px] font-black text-blue-600 font-mono tracking-tight hover:underline group-hover:text-blue-700">{{ job.job_number }}</Link>
                                <span class="text-[11px] font-bold text-slate-900 mt-1 uppercase tracking-tight">{{ job.brand }} {{ job.device_name }}</span>
                            </div>
                            <StatusBadge :status="job.status" size="xs" />
                        </div>
                        <div class="bg-slate-50 p-2 rounded border border-slate-100">
                            <p class="text-[10px] text-slate-500 line-clamp-2 italic leading-relaxed">"{{ job.issue_description }}"</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="flex justify-center pt-6 border-t border-slate-100">
                <Link href="/intakes" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-slate-900 transition-colors">Return to Batch Index</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
