<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
    roles: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'technician',
});

const isModalOpen = ref(false);
const editingUser = ref(null);

const openModal = (user = null) => {
    editingUser.value = user;
    if (user) {
        form.name = user.name;
        form.email = user.email;
        form.role = user.roles[0]?.name || 'technician';
        form.password = '';
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const submit = () => {
    if (editingUser.value) {
        form.patch(route('admin.users.update', editingUser.value.id), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.users.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
};

const deleteUser = (user) => {
    if (confirm(`Terminate system access for ${user.name}?`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};
</script>

<template>
    <Head title="Access Control" />

    <AuthenticatedLayout>
        <div class="space-y-4 max-w-5xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Security Matrix</h2>
                    <span class="text-[10px] font-black text-slate-900 uppercase mt-1">Authorized Personnel Management</span>
                </div>
                <button @click="openModal()" class="btn-primary py-1">Authorize Specialist</button>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-soft">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Personnel Identity</th>
                            <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Digital Address</th>
                            <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Security Role</th>
                            <th class="px-3 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Ops</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-sm bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-400">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-900">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-[10px] font-bold text-slate-500 font-mono">{{ user.email }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="px-2 py-0.5 rounded-sm text-[8px] font-black uppercase tracking-widest border border-slate-200 bg-white text-slate-500">
                                    {{ user.roles[0]?.name || 'Unassigned' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openModal(user)" class="text-slate-400 hover:text-blue-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button @click="deleteUser(user)" class="text-slate-400 hover:text-red-500 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Authorization Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/20 backdrop-blur-sm">
            <div class="bg-white rounded-lg border border-slate-200 shadow-2xl w-full max-w-sm overflow-hidden animate-slide-up">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">{{ editingUser ? 'Modify Access' : 'New Authorization' }}</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-900">&times;</button>
                </div>
                <form @submit.prevent="submit" class="p-4 space-y-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Full Identity Name</label>
                        <input v-model="form.name" type="text" class="input-field py-1.5" required />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Digital Address</label>
                        <input v-model="form.email" type="email" class="input-field py-1.5" required />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Access Key {{ editingUser ? '(Optional)' : '' }}</label>
                        <input v-model="form.password" type="password" class="input-field py-1.5" :required="!editingUser" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Security Role</label>
                        <select v-model="form.role" class="input-field py-1.5 capitalize text-[9px] font-black uppercase">
                            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                        </select>
                    </div>
                    <div class="pt-2 flex gap-2">
                        <button type="button" @click="isModalOpen = false" class="flex-1 btn-secondary py-1.5">Abort</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 btn-primary py-1.5">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
