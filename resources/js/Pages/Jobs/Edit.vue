<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    job: Object,
    symptoms: Object,
    accessories: Array,
});

const form = useForm({
    device_name: props.job.device_name || '',
    brand: props.job.brand || '',
    model: props.job.model || '',
    serial_number: props.job.serial_number || '',
    condition_on_arrival: props.job.condition_on_arrival || 'good',
    priority: props.job.priority || 'normal',
    issue_description: props.job.issue_description || '',
    symptoms: props.job.symptoms.map(s => s.id) || [],
    accessories: props.job.accessories.map(a => a.id) || [],
});

const submit = () => {
    form.put(route('jobs.update', props.job.job_number));
};

const deleteJob = () => {
    if (confirm('CAUTION: This will permanently retract this repair node from the operational matrix. Continue?')) {
        form.delete(route('jobs.destroy', props.job.job_number));
    }
};
</script>

<template>
    <Head :title="`Edit Job ${job.job_number}`" />

    <AuthenticatedLayout>
        <PageHeader :title="`Edit ${job.job_number}`" subtitle="Update job details">
            <template #actions>
                <div class="flex gap-2">
                    <button type="button" @click="deleteJob" class="px-4 py-2 rounded-lg text-sm font-black text-red-500 hover:bg-red-50 uppercase tracking-widest border border-red-100">
                        Wipe Node
                    </button>
                    <button type="button" @click="submit" :disabled="form.processing" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-black uppercase tracking-widest hover:bg-zinc-800 transition-all">
                        Save Changes
                    </button>
                </div>
            </template>
        </PageHeader>

        <form @submit.prevent="submit" class="p-8 max-w-4xl mx-auto space-y-6">
            <div class="bg-white border border-border rounded-xl shadow-sm overflow-hidden p-6 space-y-6">
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-black text-zinc-400 uppercase tracking-widest mb-1">Brand Entity</label>
                        <input v-model="form.brand" type="text" class="w-full text-sm border-border rounded-lg focus:ring-accent focus:border-accent" required placeholder="e.g. Apple, Samsung">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-black text-zinc-400 uppercase tracking-widest mb-1">Hardware Type</label>
                        <input v-model="form.device_name" type="text" class="w-full text-sm border-border rounded-lg focus:ring-accent focus:border-accent" required placeholder="e.g. iPhone 13, Galaxy S21">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-black text-zinc-400 uppercase tracking-widest mb-1">Model ID</label>
                        <input v-model="form.model" type="text" class="w-full text-sm border-border rounded-lg focus:ring-accent focus:border-accent" placeholder="e.g. A2633">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-black text-zinc-400 uppercase tracking-widest mb-1">Serial / IMEI Node</label>
                        <input v-model="form.serial_number" type="text" class="w-full text-sm border-border rounded-lg focus:ring-accent focus:border-accent" placeholder="Unique identifier">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-border">
                    <div>
                        <label class="block text-xs font-black text-zinc-400 uppercase tracking-widest mb-1">Physical Condition</label>
                        <select v-model="form.condition_on_arrival" class="w-full text-sm border-border rounded-lg focus:ring-accent focus:border-accent">
                            <option value="good">Good (No visible damage)</option>
                            <option value="fair">Fair (Normal wear)</option>
                            <option value="damaged">Damaged (Cracked, dented)</option>
                            <option value="broken">Broken (Not turning on/Severe)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-zinc-400 uppercase tracking-widest mb-1">Priority Execution</label>
                        <select v-model="form.priority" class="w-full text-sm border-border rounded-lg focus:ring-accent focus:border-accent">
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 border-t border-border">
                    <label class="block text-xs font-black text-zinc-400 uppercase tracking-widest mb-2">Primary Symptom Analysis</label>
                    <textarea v-model="form.issue_description" rows="3" class="w-full text-sm border-border rounded-lg focus:ring-accent focus:border-accent font-medium" required placeholder="Describe the problem reported by the customer..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-border">
                    <div>
                        <label class="block text-xs font-black text-zinc-400 uppercase tracking-widest mb-3">Symptom Matrix</label>
                        <div class="space-y-4">
                            <div v-for="(group, category) in symptoms" :key="category">
                                <h4 class="text-[9px] font-black text-muted uppercase tracking-wider mb-2">{{ category }}</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <label v-for="symptom in group" :key="symptom.id" class="flex items-center gap-2 text-[11px] font-bold text-zinc-700">
                                        <input type="checkbox" :value="symptom.id" v-model="form.symptoms" class="text-accent focus:ring-accent rounded border-zinc-300">
                                        {{ symptom.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-zinc-400 uppercase tracking-widest mb-3">Accessories Integrated</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="acc in accessories" :key="acc.id" class="flex items-center gap-2 text-[11px] font-bold text-zinc-700">
                                <input type="checkbox" :value="acc.id" v-model="form.accessories" class="text-accent focus:ring-accent rounded border-zinc-300">
                                {{ acc.name }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-border flex justify-end gap-4">
                    <Link :href="route('jobs.show', job.job_number)" class="px-6 py-2 rounded-lg text-sm font-black text-zinc-400 uppercase tracking-widest hover:bg-zinc-50 transition-all">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-black uppercase tracking-widest hover:bg-zinc-800 transition-all">
                        Commit Matrix
                    </button>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
