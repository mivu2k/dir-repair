<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    job: Object,
    technicians: Array,
    parts: Array,
});

const diagnosisForm = useForm({
    findings: '',
    required_parts: '',
    required_labor: '',
    part_id: null,
});

const editForm = useForm({
    id: null,
    findings: '',
    required_parts: '',
    required_labor: '',
    part_id: null,
});

const isEditing = ref(false);

const assignForm = useForm({
    technician_id: props.job.assigned_technician_id || '',
});

const statusForm = useForm({
    status: props.job.status,
    note: '',
});

const selectedPartId = ref('');

const addPartToDiagnosis = (targetForm) => {
    const sPartId = targetForm === 'edit' ? selectedEditPartId.value : selectedPartId.value;
    if (!sPartId) return;
    const part = props.parts.find(p => p.id == sPartId);
    if (part) {
        const formObj = targetForm === 'edit' ? editForm : diagnosisForm;
        const prefix = formObj.required_parts ? ', ' : '';
        formObj.required_parts += `${prefix}${part.name} (SKU: ${part.sku || 'N/A'})`;
        formObj.part_id = part.id;
    }
    if (targetForm === 'edit') selectedEditPartId.value = '';
    else selectedPartId.value = '';
};

const selectedEditPartId = ref('');

const submitDiagnosis = () => {
    diagnosisForm.post(route('jobs.diagnose', props.job.job_number), {
        preserveScroll: true,
        onSuccess: () => diagnosisForm.reset(),
    });
};

const startEdit = (diag) => {
    editForm.id = diag.id;
    editForm.findings = diag.findings;
    editForm.required_parts = diag.required_parts;
    editForm.required_labor = diag.required_labor;
    editForm.part_id = diag.part_id;
    isEditing.value = true;
};

const updateDiagnosis = () => {
    editForm.patch(route('diagnoses.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
            editForm.reset();
        },
    });
};

const deleteDiagnosis = (id) => {
    if (confirm('Wipe this technical finding?')) {
        router.delete(route('diagnoses.destroy', id), {
            preserveScroll: true,
        });
    }
};

const deleteJob = () => {
    if (confirm('CAUTION: This will permanently retract this repair node from the operational matrix. Continue?')) {
        router.delete(route('jobs.destroy', props.job.id));
    }
};

const assignTech = () => {
    assignForm.post(route('jobs.assign', props.job.job_number), {
        preserveScroll: true,
    });
};

const updateStatus = () => {
    statusForm.post(route('jobs.status', props.job.job_number), {
        preserveScroll: true,
        onSuccess: () => statusForm.note = '',
    });
};

const role = computed(() => usePage().props.auth.user?.roles?.[0]?.name || usePage().props.auth.user?.role || 'staff');
const isAdmin = computed(() => role.value === 'admin');
const isManager = computed(() => role.value === 'manager');
const isTechnician = computed(() => role.value === 'technician');
const canModify = computed(() => isAdmin.value || isManager.value);
const canDelete = computed(() => isAdmin.value);
const canManageFinancials = computed(() => isAdmin.value || isManager.value);
</script>

<template>
    <Head :title="`Job ${job.job_number}`" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <!-- Functional Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex flex-col">
                        <h1 class="text-lg font-black text-slate-900 font-mono tracking-tight leading-none">{{ job.job_number }}</h1>
                        <span v-if="job.intake" class="text-[10px] font-black text-blue-600 uppercase tracking-widest mt-1">Collective Intake: {{ job.intake.intake_number }}</span>
                    </div>
                    <StatusBadge :status="job.status" size="xs" />
                </div>
                <div class="flex items-center gap-2">
                    <Link v-if="canModify" :href="route('jobs.edit', job.job_number)" class="btn-secondary py-1">Modify Params</Link>
                    <button v-if="canDelete" @click="deleteJob" class="btn-secondary py-1 border-red-100 text-red-500 hover:bg-red-50">Retract Node</button>
                    <div class="relative group">
                        <button class="btn-primary py-1 px-4">Print Ops</button>
                        <div class="absolute right-0 mt-1 w-44 bg-white border border-slate-200 rounded shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 overflow-hidden">
                            <a :href="route('jobs.pdf', { job: job.job_number, variant: 'intake' })" target="_blank" class="block px-3 py-2 text-[10px] font-black text-slate-600 hover:bg-slate-50 uppercase tracking-widest">Intake Card</a>
                            <a :href="route('jobs.pdf', { job: job.job_number, variant: 'workorder' })" target="_blank" class="block px-3 py-2 text-[10px] font-black text-slate-600 hover:bg-slate-50 uppercase tracking-widest">Work Order</a>
                            <a :href="route('jobs.sticker', job.job_number)" target="_blank" class="block px-3 py-2 text-[10px] font-black text-slate-600 hover:bg-slate-50 uppercase tracking-widest border-t border-slate-100">Thermal Label</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Core Matrix -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
                <!-- Details Analysis -->
                <div class="lg:col-span-8 space-y-4">
                    <div class="bg-white border border-slate-200 rounded-lg p-4 space-y-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Customer Entity</p>
                                <p class="text-[11px] font-bold text-slate-900 mt-1">{{ job.customer.name }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Hardware Node</p>
                                <p class="text-[11px] font-bold text-slate-900 mt-1">{{ job.brand }} {{ job.device_name }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Model / Serial</p>
                                <p class="text-[11px] font-bold text-slate-900 mt-1 font-mono tracking-tighter">{{ job.model || '---' }} / {{ job.serial_number || '---' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Priority Class</p>
                                <p class="text-[11px] font-black mt-1 uppercase" :class="job.priority === 'urgent' ? 'text-red-600' : 'text-slate-600'">{{ job.priority }}</p>
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Primary Symptom Analysis</p>
                            <p class="text-[11px] text-slate-600 mt-2 bg-slate-50 p-3 rounded border border-slate-100 italic leading-relaxed">"{{ job.issue_description }}"</p>
                        </div>
                    </div>

                    <!-- Specialized Findings (CRUD) -->
                    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                        <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                            <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">Technician Intelligence Logs</h3>
                            <Link v-if="job.diagnoses.length > 0 && canManageFinancials" :href="route('quotations.create', { job_number: job.job_number })" class="btn-primary py-1 px-4 text-[9px]">Build Quotation</Link>
                        </div>
                        <div class="p-4 space-y-4">
                            <div v-for="diag in job.diagnoses" :key="diag.id" class="group p-4 border border-slate-100 rounded-lg bg-slate-50/50 hover:bg-white hover:border-slate-200 transition-all">
                                <div class="flex justify-between items-center mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                        <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">{{ diag.technician?.name || 'Technical Staff' }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[9px] font-bold text-slate-400 font-mono">{{ new Date(diag.created_at).toLocaleDateString() }}</span>
                                        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="startEdit(diag)" class="text-blue-600 hover:text-blue-800 text-[9px] font-black uppercase">Edit</button>
                                            <button @click="deleteDiagnosis(diag.id)" class="text-red-400 hover:text-red-600 text-[9px] font-black uppercase">Wipe</button>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-600 mb-4 leading-relaxed font-medium">{{ diag.findings }}</p>
                                <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-3">
                                    <div v-if="diag.required_parts">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Hardware Matrix</p>
                                        <p class="text-[10px] font-bold text-slate-700">{{ diag.required_parts }}</p>
                                    </div>
                                    <div v-if="diag.required_labor">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Labor Nodes</p>
                                        <p class="text-[10px] font-bold text-slate-700">{{ diag.required_labor }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="job.diagnoses.length === 0" class="py-8 text-center text-[10px] font-black text-slate-300 uppercase tracking-widest">
                                No technical logs registered for this node
                            </div>

                            <!-- Integrated Diagnosis Creation Form -->
                            <form @submit.prevent="submitDiagnosis" class="mt-6 pt-6 border-t-2 border-dashed border-slate-100 space-y-4">
                                <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Append New Findings</h4>
                                <div class="space-y-1">
                                    <textarea v-model="diagnosisForm.findings" rows="2" class="input-field text-[11px]" placeholder="Enter specialist observations and diagnostic results..." required></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Inventory Integration</label>
                                        <div class="flex gap-1">
                                            <select v-model="selectedPartId" class="input-field py-1.5 text-[10px]">
                                                <option value="">Select Physical Asset...</option>
                                                <option v-for="p in parts" :key="p.id" :value="p.id">{{ p.name }} ({{ p.quantity }} available)</option>
                                            </select>
                                            <button type="button" @click="addPartToDiagnosis('create')" class="btn-secondary py-1 text-[10px] font-black">+</button>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Formal Parts Required</label>
                                        <input v-model="diagnosisForm.required_parts" type="text" class="input-field py-1.5 text-[11px]" placeholder="List required hardware identifiers...">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Labor Nodes / Specialized Service</label>
                                        <input v-model="diagnosisForm.required_labor" type="text" class="input-field py-1.5 text-[11px]" placeholder="Service description...">
                                    </div>
                                    <div class="flex items-end justify-end">
                                        <button type="submit" :disabled="diagnosisForm.processing" class="btn-primary py-2 px-8 text-[10px] shadow-sm">Commit Technical Log</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Workflow Sidebar -->
                <div class="lg:col-span-4 space-y-4">
                    <!-- Lifecycle State -->
                    <div class="bg-slate-900 text-white rounded-lg p-4 shadow-lg border border-slate-800">
                        <h3 class="text-[9px] font-black uppercase tracking-[0.2em] mb-4 text-slate-500">Operational State</h3>
                        <form @submit.prevent="updateStatus" class="space-y-3">
                            <select v-model="statusForm.status" class="w-full bg-slate-800 border-slate-700 rounded px-2.5 py-2 text-[11px] font-black text-white focus:ring-blue-500 capitalize outline-none transition-all">
                                <option v-for="s in ['received', 'diagnosing', 'waiting_approval', 'in_progress', 'completed', 'delivered', 'cancelled']" :key="s" :value="s">{{ s.replace('_', ' ') }}</option>
                            </select>
                            <textarea v-model="statusForm.note" rows="2" class="w-full bg-slate-800 border-slate-700 rounded px-2.5 py-2 text-[11px] text-white placeholder:text-slate-600 outline-none transition-all" placeholder="Internal state notes..."></textarea>
                            <button type="submit" :disabled="statusForm.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-[10px] font-black uppercase tracking-widest transition-all shadow-lg active:scale-95">Update Lifecycle</button>
                        </form>
                    </div>

                    <!-- Specialist Node -->
                    <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
                        <h3 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4">Assigned Specialist</h3>
                        <form @submit.prevent="assignTech" class="flex gap-2">
                            <select v-model="assignForm.technician_id" class="input-field py-1.5 text-[11px] font-bold">
                                <option value="">Global Unassigned</option>
                                <option v-for="t in technicians" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                            <button type="submit" :disabled="assignForm.processing" class="btn-secondary py-1.5 px-4 text-[10px]">Set</button>
                        </form>
                    </div>

                    <!-- Operational History -->
                    <div class="bg-white border border-slate-200 rounded-lg p-4 space-y-4 shadow-sm">
                        <h3 class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Traceability Matrix</h3>
                        <div class="space-y-4 border-l-2 border-slate-50 ml-1 pl-4">
                            <div v-for="history in job.status_histories" :key="history.id" class="relative">
                                <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 bg-white border-2 border-slate-200 rounded-full"></div>
                                <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest leading-none">{{ history.to_status.replace('_', ' ') }}</p>
                                <p class="text-[9px] text-slate-400 font-mono mt-1.5">{{ new Date(history.created_at).toLocaleString() }}</p>
                                <p v-if="history.note" class="text-[10px] text-slate-500 mt-2 italic leading-snug border-l-2 border-slate-100 pl-2">"{{ history.note }}"</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Finding Modal -->
        <div v-if="isEditing" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg border border-slate-200 shadow-2xl w-full max-w-lg animate-slide-up">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50 rounded-t-lg">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-900">Modify Specialist Finding</span>
                    <button @click="isEditing = false" class="text-slate-400 hover:text-slate-900 text-lg">&times;</button>
                </div>
                <form @submit.prevent="updateDiagnosis" class="p-5 space-y-4">
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Observations</label>
                            <textarea v-model="editForm.findings" rows="3" class="input-field text-[11px]" required></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Inventory Assets</label>
                                <div class="flex gap-1">
                                    <select v-model="selectedEditPartId" class="input-field py-1.5 text-[10px]">
                                        <option value="">Select Asset...</option>
                                        <option v-for="p in parts" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                    <button type="button" @click="addPartToDiagnosis('edit')" class="btn-secondary py-1 text-[10px] font-black">+</button>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Required Hardware</label>
                                <input v-model="editForm.required_parts" type="text" class="input-field py-1.5 text-[11px]">
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Labor / Service Node</label>
                            <input v-model="editForm.required_labor" type="text" class="input-field py-1.5 text-[11px]">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="isEditing = false" class="flex-1 btn-secondary text-[10px]">Abort</button>
                        <button type="submit" :disabled="editForm.processing" class="flex-1 btn-primary text-[10px]">Commit Modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
