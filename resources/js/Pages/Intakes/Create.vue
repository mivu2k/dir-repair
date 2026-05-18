<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    customers: Array,
    symptoms: Object,
    accessories: Array,
    brands: Array,
    devices: Array,
});

const isNewCustomer = ref(false);

const form = useForm({
    customer_id: '',
    customer: {
        name: '',
        phone: '',
        email: '',
        organization: '',
    },
    payment_method: 'cash',
    notes: '',
    devices: [
        {
            brand: '',
            device_name: '',
            model: '',
            serial_number: '',
            condition_on_arrival: 'good',
            priority: 'normal',
            issue_description: '',
            symptoms: [],
            accessories: [],
        }
    ],
});

const addDevice = () => {
    form.devices.push({
        brand: '',
        device_name: '',
        model: '',
        serial_number: '',
        condition_on_arrival: 'good',
        priority: 'normal',
        issue_description: '',
        symptoms: [],
        accessories: [],
    });
};

const removeDevice = (index) => {
    if (form.devices.length > 1) {
        form.devices.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('intakes.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Operational Intake" />

    <AuthenticatedLayout>
        <div class="space-y-4 max-w-5xl mx-auto pb-12">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest">Register Operational Intake</h2>
                <button @click="submit" :disabled="form.processing" class="btn-primary">Commit Matrix</button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Entity Identification -->
                <div class="bg-white border border-slate-200 rounded-lg p-3 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Ownership Profile</h3>
                        <div class="flex gap-1 bg-slate-50 p-1 rounded">
                            <button type="button" @click="isNewCustomer = false" :class="['px-3 py-1 rounded text-[9px] font-black uppercase transition-all', !isNewCustomer ? 'bg-white text-slate-900 shadow-sm border border-slate-100' : 'text-slate-400']">Existing</button>
                            <button type="button" @click="isNewCustomer = true; form.customer_id = ''" :class="['px-3 py-1 rounded text-[9px] font-black uppercase transition-all', isNewCustomer ? 'bg-white text-slate-900 shadow-sm border border-slate-100' : 'text-slate-400']">New Node</button>
                        </div>
                    </div>

                    <div v-if="!isNewCustomer" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="md:col-span-2 space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Identify Registered Entity</label>
                            <select v-model="form.customer_id" class="input-field py-1.5" required>
                                <option value="">Select Customer...</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.phone }})</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Settlement Preference</label>
                            <select v-model="form.payment_method" class="input-field py-1.5 uppercase text-[9px] font-black">
                                <option value="cash">Cash</option>
                                <option value="credit">Credit</option>
                                <option value="bank_transfer">Transfer</option>
                                <option value="warranty">Warranty</option>
                            </select>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-3 animate-slide-up">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Name</label>
                            <input v-model="form.customer.name" type="text" class="input-field py-1.5" required>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Phone</label>
                            <input v-model="form.customer.phone" type="text" class="input-field py-1.5" required>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Organization</label>
                            <input v-model="form.customer.organization" type="text" class="input-field py-1.5">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Payment</label>
                            <select v-model="form.payment_method" class="input-field py-1.5 uppercase text-[9px] font-black">
                                <option value="cash">Cash</option>
                                <option value="credit">Credit</option>
                                <option value="bank_transfer">Transfer</option>
                                <option value="warranty">Warranty</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Hardware Matrix -->
                <div v-for="(device, index) in form.devices" :key="index" class="bg-white border border-slate-200 rounded-lg p-3 space-y-3 relative animate-slide-up">
                    <button 
                        v-if="form.devices.length > 1"
                        @click="removeDevice(index)"
                        type="button"
                        class="absolute top-2 right-2 text-slate-300 hover:text-red-500 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Hardware Entity {{ index + 1 }}</h3>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Brand / Manufacturer</label>
                            <input v-model="device.brand" type="text" list="brand-list" class="input-field py-1.5" required>
                            <datalist id="brand-list">
                                <option v-for="brand in brands" :key="brand.id" :value="brand.name" />
                            </datalist>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Device Identity</label>
                            <input v-model="device.device_name" type="text" list="device-list" class="input-field py-1.5" required>
                            <datalist id="device-list">
                                <option v-for="dev in devices" :key="dev.id" :value="dev.name" />
                            </datalist>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Model Ref</label>
                            <input v-model="device.model" type="text" class="input-field py-1.5">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Serial ID</label>
                            <input v-model="device.serial_number" type="text" class="input-field py-1.5 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 pt-3 border-t border-slate-50">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Operational Deficiencies</label>
                            <textarea v-model="device.issue_description" rows="2" class="input-field" required placeholder="Describe malfunctions..."></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Arrival Condition</label>
                                <select v-model="device.condition_on_arrival" class="input-field py-1.5 capitalize font-black text-[9px]">
                                    <option value="good">Good</option>
                                    <option value="fair">Fair</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="broken">Broken</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Priority Index</label>
                                <select v-model="device.priority" class="input-field py-1.5 capitalize font-black text-[9px]">
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-50">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Symptom Matrix</label>
                            <div class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                                <div v-for="(group, category) in symptoms" :key="category" class="space-y-1">
                                    <h4 class="text-[8px] font-black text-slate-300 uppercase tracking-[0.2em]">{{ category || 'General' }}</h4>
                                    <div class="flex flex-wrap gap-1">
                                        <label v-for="symptom in group" :key="symptom.id" :class="['px-2 py-1 border rounded text-[9px] font-bold cursor-pointer transition-all', device.symptoms.includes(symptom.id) ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-slate-100 text-slate-400 hover:border-slate-200']">
                                            <input type="checkbox" v-model="device.symptoms" :value="symptom.id" class="hidden">
                                            {{ symptom.name }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Peripherals Matrix</label>
                            <div class="flex flex-wrap gap-1">
                                <label v-for="acc in accessories" :key="acc.id" :class="['px-2 py-1 border rounded text-[9px] font-bold cursor-pointer transition-all', device.accessories.includes(acc.id) ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-slate-100 text-slate-400 hover:border-slate-200']">
                                    <input type="checkbox" v-model="device.accessories" :value="acc.id" class="hidden">
                                    {{ acc.name }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Global Actions -->
                <div class="flex items-center justify-between pt-2">
                    <button @click="addDevice" type="button" class="btn-secondary py-1.5 text-[9px] border-dashed">
                        + Append Hardware Entity
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="$window.history.back()" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-900">Abort</button>
                        <button type="submit" :disabled="form.processing" class="btn-primary py-1.5 px-6">Commit Lifecycle</button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
