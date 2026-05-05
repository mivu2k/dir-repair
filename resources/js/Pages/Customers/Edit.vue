<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    customer: Object,
});

const form = useForm({
    name: props.customer.name || '',
    phone: props.customer.phone || '',
    email: props.customer.email || '',
    organization: props.customer.organization || '',
    address: props.customer.address || '',
    communication_preference: props.customer.communication_preference || 'phone',
    notes: props.customer.notes || '',
});

const submit = () => {
    form.put(route('customers.update', props.customer.id));
};
</script>

<template>
    <Head :title="`Edit ${customer.name}`" />

    <AuthenticatedLayout>
        <PageHeader :title="`Edit ${customer.name}`" subtitle="Update client information" />

        <div class="p-8 max-w-2xl mx-auto">
            <div class="bg-white border border-border rounded-xl shadow-sm overflow-hidden">
                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-primary border-b border-border pb-2">Client Details</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Full Name</label>
                            <input v-model="form.name" type="text" class="w-full border-border rounded-lg focus:ring-accent focus:border-accent" required>
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Phone Number</label>
                                <input v-model="form.phone" type="text" class="w-full border-border rounded-lg focus:ring-accent focus:border-accent" required>
                                <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1">Email Address</label>
                                <input v-model="form.email" type="email" class="w-full border-border rounded-lg focus:ring-accent focus:border-accent">
                                <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Organization / Company</label>
                            <input v-model="form.organization" type="text" class="w-full border-border rounded-lg focus:ring-accent focus:border-accent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Physical Address</label>
                            <textarea v-model="form.address" rows="2" class="w-full border-border rounded-lg focus:ring-accent focus:border-accent"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Communication Preference</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="radio" value="phone" v-model="form.communication_preference" class="text-accent focus:ring-accent"> Phone
                                </label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="radio" value="email" v-model="form.communication_preference" class="text-accent focus:ring-accent"> Email
                                </label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="radio" value="whatsapp" v-model="form.communication_preference" class="text-accent focus:ring-accent"> WhatsApp
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Additional Notes</label>
                            <textarea v-model="form.notes" rows="2" class="w-full border-border rounded-lg focus:ring-accent focus:border-accent"></textarea>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-border flex justify-end gap-4">
                        <button type="button" @click="$inertia.visit(route('customers.show', customer.id))" class="px-6 py-2 rounded-lg text-sm font-medium text-zinc-600 hover:bg-zinc-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="form.processing" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-zinc-800 transition-colors">
                            Update Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
