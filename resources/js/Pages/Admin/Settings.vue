<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
    symptoms: Array,
    accessories: Array,
    brands: Array,
    devices: Array,
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

const symptomForm = useForm({ name: '', category: '' });
const accessoryForm = useForm({ name: '' });
const brandForm = useForm({ name: '' });
const deviceForm = useForm({ name: '' });

const addSymptom = () => symptomForm.post(route('admin.settings.symptoms.store'), { preserveScroll: true, onSuccess: () => symptomForm.reset() });
const addAccessory = () => accessoryForm.post(route('admin.settings.accessories.store'), { preserveScroll: true, onSuccess: () => accessoryForm.reset() });
const addBrand = () => brandForm.post(route('admin.settings.brands.store'), { preserveScroll: true, onSuccess: () => brandForm.reset() });
const addDevice = () => deviceForm.post(route('admin.settings.devices.store'), { preserveScroll: true, onSuccess: () => deviceForm.reset() });

const deleteSymptom = (id) => { if (confirm('Delete symptom?')) router.delete(route('admin.settings.symptoms.destroy', id), { preserveScroll: true }); };
const deleteAccessory = (id) => { if (confirm('Delete accessory?')) router.delete(route('admin.settings.accessories.destroy', id), { preserveScroll: true }); };
const deleteBrand = (id) => { if (confirm('Delete brand?')) router.delete(route('admin.settings.brands.destroy', id), { preserveScroll: true }); };
const deleteDevice = (id) => { if (confirm('Delete device type?')) router.delete(route('admin.settings.devices.destroy', id), { preserveScroll: true }); };
</script>

<template>
    <Head title="System Core" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-5xl mx-auto pb-12">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">System Core</h2>
                    <span class="text-[10px] font-black text-slate-900 uppercase mt-1">Operational Configuration</span>
                </div>
                <button @click="submit" :disabled="form.processing" class="bg-slate-900 text-white px-6 py-2 rounded font-black text-[10px] uppercase tracking-widest shadow-xl hover:bg-slate-800 transition-all">Commit Parameters</button>
            </div>

            <!-- Identity Matrix -->
            <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm space-y-4">
                <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest border-b border-slate-50 pb-2">Organizational Identity</h3>
                <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Corporate Name</label>
                            <input v-model="form.company_name" type="text" class="w-full border-slate-200 rounded text-sm px-3 py-2" placeholder="Company Name">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Digital Contact</label>
                            <input v-model="form.company_email" type="email" class="w-full border-slate-200 rounded text-sm px-3 py-2" placeholder="info@company.com">
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Physical Headquarters</label>
                            <textarea v-model="form.company_address" rows="2" class="w-full border-slate-200 rounded text-sm px-3 py-2" placeholder="Full Address..."></textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Phone Matrix</label>
                            <input v-model="form.company_phone" type="text" class="w-full border-slate-200 rounded text-sm px-3 py-2" placeholder="+92 ...">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Fiscal Currency</label>
                            <input v-model="form.currency_symbol" type="text" class="w-full border-slate-200 rounded text-sm px-3 py-2 font-mono" placeholder="PKR">
                        </div>
                    </div>

                    <div class="lg:col-span-4 border-l border-slate-100 pl-8 flex flex-col items-center justify-center space-y-4">
                        <div class="w-32 h-32 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center overflow-hidden group relative">
                            <img v-if="logoPreview" :src="logoPreview" class="max-w-full max-h-full object-contain p-2" alt="Logo" />
                            <div v-else class="text-slate-300 text-center">
                                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-[8px] font-black uppercase">Upload Logo</span>
                            </div>
                        </div>
                        <label class="bg-slate-100 px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-wider cursor-pointer hover:bg-slate-200 transition-colors">
                            Change Identity Asset
                            <input type="file" @change="onFileChange" class="hidden" accept="image/*" />
                        </label>
                    </div>
                </form>
            </div>

            <!-- Master Data Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Brands / Manufacturers -->
                <div class="bg-white border border-slate-200 rounded-lg flex flex-col overflow-hidden shadow-sm">
                    <div class="px-3 py-2 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Brand Matrix</h3>
                        <span class="text-[9px] font-black text-slate-400">{{ brands.length }} ENTITIES</span>
                    </div>
                    <div class="p-3 space-y-2 max-h-[250px] overflow-y-auto custom-scrollbar flex-1">
                        <div v-for="brand in brands" :key="brand.id" class="flex items-center justify-between p-2 bg-slate-50/50 border border-slate-100 rounded hover:border-slate-300 group">
                            <span class="text-[11px] font-bold text-slate-900 uppercase">{{ brand.name }}</span>
                            <button @click="deleteBrand(brand.id)" class="text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-50 border-t border-slate-200">
                        <form @submit.prevent="addBrand" class="flex gap-2">
                            <input v-model="brandForm.name" type="text" placeholder="Brand Name..." class="flex-1 bg-white border border-slate-200 rounded px-3 py-1.5 text-[10px] font-bold uppercase" required>
                            <button type="submit" :disabled="brandForm.processing" class="bg-slate-900 text-white px-3 py-1.5 rounded font-black text-[9px] uppercase">Add</button>
                        </form>
                    </div>
                </div>

                <!-- Device Types -->
                <div class="bg-white border border-slate-200 rounded-lg flex flex-col overflow-hidden shadow-sm">
                    <div class="px-3 py-2 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Device Taxonomy</h3>
                        <span class="text-[9px] font-black text-slate-400">{{ devices.length }} ENTITIES</span>
                    </div>
                    <div class="p-3 space-y-2 max-h-[250px] overflow-y-auto custom-scrollbar flex-1">
                        <div v-for="device in devices" :key="device.id" class="flex items-center justify-between p-2 bg-slate-50/50 border border-slate-100 rounded hover:border-slate-300 group">
                            <span class="text-[11px] font-bold text-slate-900 uppercase">{{ device.name }}</span>
                            <button @click="deleteDevice(device.id)" class="text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-50 border-t border-slate-200">
                        <form @submit.prevent="addDevice" class="flex gap-2">
                            <input v-model="deviceForm.name" type="text" placeholder="Device Type..." class="flex-1 bg-white border border-slate-200 rounded px-3 py-1.5 text-[10px] font-bold uppercase" required>
                            <button type="submit" :disabled="deviceForm.processing" class="bg-slate-900 text-white px-3 py-1.5 rounded font-black text-[9px] uppercase">Add</button>
                        </form>
                    </div>
                </div>

                <!-- Symptoms -->
                <div class="bg-white border border-slate-200 rounded-lg flex flex-col overflow-hidden shadow-sm">
                    <div class="px-3 py-2 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Anomaly Master List</h3>
                        <span class="text-[9px] font-black text-slate-400">{{ symptoms.length }} ENTITIES</span>
                    </div>
                    <div class="p-3 space-y-2 max-h-[250px] overflow-y-auto custom-scrollbar flex-1">
                        <div v-for="symptom in symptoms" :key="symptom.id" class="flex items-center justify-between p-2 bg-slate-50/50 border border-slate-100 rounded hover:border-slate-300 group">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold text-slate-900 uppercase">{{ symptom.name }}</span>
                                <span v-if="symptom.category" class="text-[8px] font-black text-slate-400 bg-white border px-1.5 py-0.5 rounded uppercase">{{ symptom.category }}</span>
                            </div>
                            <button @click="deleteSymptom(symptom.id)" class="text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-50 border-t border-slate-200">
                        <form @submit.prevent="addSymptom" class="flex gap-2">
                            <input v-model="symptomForm.name" type="text" placeholder="Identity..." class="flex-1 bg-white border border-slate-200 rounded px-3 py-1.5 text-[10px] font-bold uppercase" required>
                            <input v-model="symptomForm.category" type="text" placeholder="Sector..." class="w-1/4 bg-white border border-slate-200 rounded px-3 py-1.5 text-[10px] font-bold uppercase">
                            <button type="submit" :disabled="symptomForm.processing" class="bg-slate-900 text-white px-3 py-1.5 rounded font-black text-[9px] uppercase">Add</button>
                        </form>
                    </div>
                </div>

                <!-- Peripherals -->
                <div class="bg-white border border-slate-200 rounded-lg flex flex-col overflow-hidden shadow-sm">
                    <div class="px-3 py-2 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Peripheral Master List</h3>
                        <span class="text-[9px] font-black text-slate-400">{{ accessories.length }} ENTITIES</span>
                    </div>
                    <div class="p-3 space-y-2 max-h-[250px] overflow-y-auto custom-scrollbar flex-1">
                        <div v-for="accessory in accessories" :key="accessory.id" class="flex items-center justify-between p-2 bg-slate-50/50 border border-slate-100 rounded hover:border-slate-300 group">
                            <span class="text-[11px] font-bold text-slate-900 uppercase">{{ accessory.name }}</span>
                            <button @click="deleteAccessory(accessory.id)" class="text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-50 border-t border-slate-200">
                        <form @submit.prevent="addAccessory" class="flex gap-2">
                            <input v-model="accessoryForm.name" type="text" placeholder="Identity..." class="flex-1 bg-white border border-slate-200 rounded px-3 py-1.5 text-[10px] font-bold uppercase" required>
                            <button type="submit" :disabled="accessoryForm.processing" class="bg-slate-900 text-white px-3 py-1.5 rounded font-black text-[9px] uppercase">Add</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
