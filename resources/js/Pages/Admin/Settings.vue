<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
    symptoms: Array,
    accessories: Array,
});

const logoPreview = ref(props.settings.company_logo ? `/storage/${props.settings.company_logo}` : null);

const form = useForm({
    company_name: props.settings.company_name || '',
    company_address: props.settings.company_address || '',
    company_phone: props.settings.company_phone || '',
    company_email: props.settings.company_email || '',
    currency_symbol: props.settings.currency_symbol || 'PKR',
    company_logo: null,
});

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.company_logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};

const symptomForm = useForm({
    name: '',
    category: '',
});

const accessoryForm = useForm({
    name: '',
});

const addSymptom = () => {
    symptomForm.post(route('admin.settings.symptoms.store'), {
        preserveScroll: true,
        onSuccess: () => symptomForm.reset(),
    });
};

const addAccessory = () => {
    accessoryForm.post(route('admin.settings.accessories.store'), {
        preserveScroll: true,
        onSuccess: () => accessoryForm.reset(),
    });
};

const deleteSymptom = (id) => {
    if (confirm('Delete this symptom?')) {
        router.delete(route('admin.settings.symptoms.destroy', id), { preserveScroll: true });
    }
};

const deleteAccessory = (id) => {
    if (confirm('Delete this accessory?')) {
        router.delete(route('admin.settings.accessories.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="System Core" />

    <AuthenticatedLayout>
        <div class="space-y-4 max-w-5xl mx-auto pb-12">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">System Core</h2>
                    <span class="text-[10px] font-black text-slate-900 uppercase mt-1">Operational Configuration</span>
                </div>
                <button @click="submit" :disabled="form.processing" class="btn-primary py-1">Commit Parameters</button>
            </div>

            <!-- Identity Matrix -->
            <div class="bg-white border border-slate-200 rounded-lg p-3 space-y-4">
                <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest border-b border-slate-50 pb-2">Organizational Identity</h3>
                <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Corporate Name</label>
                            <input v-model="form.company_name" type="text" class="input-field py-1.5" placeholder="Company Name">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Digital Contact</label>
                            <input v-model="form.company_email" type="email" class="input-field py-1.5" placeholder="info@company.com">
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Physical Headquarters</label>
                            <textarea v-model="form.company_address" rows="2" class="input-field" placeholder="Full Address..."></textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Phone Matrix</label>
                            <input v-model="form.company_phone" type="text" class="input-field py-1.5" placeholder="+92 ...">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Fiscal Currency</label>
                            <input v-model="form.currency_symbol" type="text" class="input-field py-1.5 font-mono" placeholder="PKR">
                        </div>
                    </div>

                    <div class="lg:col-span-4 border-l border-slate-100 pl-6 flex flex-col items-center justify-center space-y-3">
                        <div class="w-24 h-24 bg-slate-50 border border-slate-200 rounded flex items-center justify-center overflow-hidden">
                            <img v-if="logoPreview" :src="logoPreview" class="max-w-full max-h-full object-contain p-2" alt="Logo" />
                            <svg v-else class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <label class="btn-secondary py-1 text-[9px] cursor-pointer">
                            Identify Asset
                            <input type="file" @change="onFileChange" class="hidden" accept="image/*" />
                        </label>
                    </div>
                </form>
            </div>

            <!-- Master Data Matrix -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Symptoms -->
                <div class="bg-white border border-slate-200 rounded-lg flex flex-col overflow-hidden">
                    <div class="px-3 py-2 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Anomaly Master List</h3>
                        <span class="text-[9px] font-black text-slate-400">{{ symptoms.length }} ENTITIES</span>
                    </div>
                    <div class="p-3 space-y-2 max-h-[300px] overflow-y-auto custom-scrollbar flex-1">
                        <div v-for="symptom in symptoms" :key="symptom.id" class="flex items-center justify-between p-2 bg-slate-50/50 border border-slate-100 rounded hover:border-slate-300 transition-all group">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold text-slate-900 uppercase">{{ symptom.name }}</span>
                                <span v-if="symptom.category" class="text-[8px] font-black text-slate-400 bg-white border border-slate-100 px-1.5 py-0.5 rounded uppercase">{{ symptom.category }}</span>
                            </div>
                            <button @click="deleteSymptom(symptom.id)" class="text-slate-300 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-50 border-t border-slate-200">
                        <form @submit.prevent="addSymptom" class="flex gap-2">
                            <input v-model="symptomForm.name" type="text" placeholder="Identity..." class="flex-1 input-field py-1 text-[10px]" required>
                            <input v-model="symptomForm.category" type="text" placeholder="Sector..." class="w-1/4 input-field py-1 text-[10px]">
                            <button type="submit" :disabled="symptomForm.processing" class="btn-primary py-1 text-[9px]">Authorize</button>
                        </form>
                    </div>
                </div>

                <!-- Peripherals -->
                <div class="bg-white border border-slate-200 rounded-lg flex flex-col overflow-hidden">
                    <div class="px-3 py-2 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Peripheral Master List</h3>
                        <span class="text-[9px] font-black text-slate-400">{{ accessories.length }} ENTITIES</span>
                    </div>
                    <div class="p-3 space-y-2 max-h-[300px] overflow-y-auto custom-scrollbar flex-1">
                        <div v-for="accessory in accessories" :key="accessory.id" class="flex items-center justify-between p-2 bg-slate-50/50 border border-slate-100 rounded hover:border-slate-300 transition-all group">
                            <span class="text-[11px] font-bold text-slate-900 uppercase">{{ accessory.name }}</span>
                            <button @click="deleteAccessory(accessory.id)" class="text-slate-300 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-50 border-t border-slate-200">
                        <form @submit.prevent="addAccessory" class="flex gap-2">
                            <input v-model="accessoryForm.name" type="text" placeholder="Identity..." class="flex-1 input-field py-1 text-[10px]" required>
                            <button type="submit" :disabled="accessoryForm.processing" class="btn-primary py-1 text-[9px]">Authorize</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
