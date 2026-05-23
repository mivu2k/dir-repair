<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
    roles: Array,
    permissions: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'technician',
});

const isModalOpen = ref(false);
const editingUser = ref(null);

const activeTab = ref('users'); // 'users' or 'permissions'

// Reactive mapping for Roles & Permissions Matrix
const matrixForm = ref({});
props.roles.forEach(role => {
    matrixForm.value[role.id] = {
        name: role.name,
        permissions: role.permissions ? role.permissions.map(p => p.name) : []
    };
});

const savingRoleIds = ref([]);

const saveRolePermissions = (roleId) => {
    savingRoleIds.value.push(roleId);
    router.post(route('admin.roles.permissions.update', roleId), {
        permissions: matrixForm.value[roleId].permissions
    }, {
        preserveScroll: true,
        onSuccess: () => {
            savingRoleIds.value = savingRoleIds.value.filter(id => id !== roleId);
        },
        onError: () => {
            savingRoleIds.value = savingRoleIds.value.filter(id => id !== roleId);
        }
    });
};

const permissionCategories = [
    {
        name: 'Customers Module Access (CRUD)',
        items: [
            { name: 'view customers', label: 'View Customer Entities' },
            { name: 'create customers', label: 'Create New Customers' },
            { name: 'edit customers', label: 'Edit Customer Configurations' },
            { name: 'delete customers', label: 'Delete Customer Records' }
        ]
    },
    {
        name: 'Collective Intakes Access (CRUD)',
        items: [
            { name: 'view intakes', label: 'View Collective Intakes' },
            { name: 'create intakes', label: 'Create Collective Intakes' },
            { name: 'edit intakes', label: 'Edit Intakes / Equipment Lists' },
            { name: 'delete intakes', label: 'Wipe Collective Intakes' }
        ]
    },
    {
        name: 'Repair Jobs Module Access (CRUD)',
        items: [
            { name: 'view jobs', label: 'View Repair Job Nodes' },
            { name: 'create jobs', label: 'Create Repair Jobs' },
            { name: 'edit jobs', label: 'Edit Job Operations Details' },
            { name: 'delete jobs', label: 'Retract / Wipe Repair Jobs' }
        ]
    },
    {
        name: 'Inventory & Spare Parts Access (CRUD)',
        items: [
            { name: 'view parts', label: 'View Warehouse Parts Stock' },
            { name: 'create parts', label: 'Onboard Warehouse Parts' },
            { name: 'edit parts', label: 'Modify Part / SKU Metrics' },
            { name: 'delete parts', label: 'Delete Warehouse Inventory Assets' }
        ]
    },
    {
        name: 'Quotations Module Access (CRUD)',
        items: [
            { name: 'view quotations', label: 'View Estimates & Quotations' },
            { name: 'create quotations', label: 'Build Integrated Quotations' },
            { name: 'edit quotations', label: 'Edit Quotes / Matrix Rows' },
            { name: 'delete quotations', label: 'Delete Quotation Files' }
        ]
    },
    {
        name: 'Demo Goods & Trials Access (CRUD)',
        items: [
            { name: 'view demo-issuances', label: 'View Demo Trial Ledger' },
            { name: 'create demo-issuances', label: 'Issue Equipment Demos' },
            { name: 'edit demo-issuances', label: 'Edit Trial Parameters' },
            { name: 'delete demo-issuances', label: 'Delete Demo Handouts' }
        ]
    },
    {
        name: 'Gate Passes Module Access (CRUD)',
        items: [
            { name: 'view gate-passes', label: 'View Gate Clearance Log' },
            { name: 'create gate-passes', label: 'Create Cargo Gate Passes' },
            { name: 'edit gate-passes', label: 'Edit Gate Clearance Passes' },
            { name: 'delete gate-passes', label: 'Delete Gate Passes' }
        ]
    },
    {
        name: 'Invoices & Sales Orders Access (CRUD)',
        items: [
            { name: 'view sales-orders', label: 'View Customer Invoices' },
            { name: 'create sales-orders', label: 'Create Integrated Invoices' },
            { name: 'edit sales-orders', label: 'Modify Invoices / Add Payments' },
            { name: 'delete sales-orders', label: 'Delete Invoices' }
        ]
    },
    {
        name: 'System Personnel / Users Access (CRUD)',
        items: [
            { name: 'view users', label: 'View Specialist Personnel List' },
            { name: 'create users', label: 'Register System Personnel' },
            { name: 'edit users', label: 'Modify Personnel Roles' },
            { name: 'delete users', label: 'Revoke Security Personnel Access' }
        ]
    },
    {
        name: 'Specialist Workflow Nodes & Restrictions',
        items: [
            { name: 'update job status', label: 'Modify Repair Status Timeline' },
            { name: 'add diagnosis', label: 'Commit Specialist Diagnostics Logs' },
            { name: 'view financial data', label: 'View Pricing & Analytical Calculations' },
            { name: 'approve quotation', label: 'Approve / Finalize Quotation Lists' },
            { name: 'manage users', label: 'Global Access Control Matrix' },
            { name: 'view reports', label: 'View Management Operations Reports' },
            { name: 'change own jobs only', label: 'Scope Limit: Access Own Assigned Servicings Only' }
        ]
    }
];

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
        <div class="space-y-4 max-w-6xl mx-auto select-none">
            <!-- Header section -->
            <div class="flex items-center justify-between pb-2">
                <div class="flex flex-col">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Security Matrix</h2>
                    <span class="text-[10px] font-black text-slate-900 uppercase mt-1">Authorized Access Settings</span>
                </div>
            </div>

            <!-- Tabbed Navigation mirroring M365 styling -->
            <div class="flex border-b border-slate-200 mb-4 select-none">
                <button 
                    @click="activeTab = 'users'"
                    :class="[
                        'px-4 py-2 text-[10px] font-black uppercase tracking-wider transition-all border-b-2 -mb-[2px]',
                        activeTab === 'users' ? 'border-[#0078d4] text-[#0078d4]' : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300'
                    ]"
                >
                    Authorized Personnel List
                </button>
                <button 
                    @click="activeTab = 'permissions'"
                    :class="[
                        'px-4 py-2 text-[10px] font-black uppercase tracking-wider transition-all border-b-2 -mb-[2px]',
                        activeTab === 'permissions' ? 'border-[#0078d4] text-[#0078d4]' : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300'
                    ]"
                >
                    Security Role Permission Matrix
                </button>
            </div>

            <!-- TAB 1: User list management -->
            <div v-show="activeTab === 'users'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="text-[10px] font-bold text-slate-500">Personnel authorized to access components and assets inside the operational matrix</div>
                    <button @click="openModal()" class="btn-primary py-1 text-[10px] font-bold px-4">+ Authorize Specialist</button>
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
                                    <span class="px-2 py-0.5 rounded-sm text-[8px] font-black uppercase tracking-widest border border-slate-200 bg-white text-[#0078d4]">
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

            <!-- TAB 2: Roles and Permissions Matrix -->
            <div v-show="activeTab === 'permissions'" class="space-y-4 animate-slide-up">
                <div class="flex items-center justify-between">
                    <div class="text-[10px] font-bold text-slate-500">Fine-grain capability settings mapped to system operations roles</div>
                </div>

                <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-soft">
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left text-xs min-w-[900px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest min-w-[320px]">Capabilities / Permissions</th>
                                    <th v-for="role in roles" :key="role.id" class="px-4 py-3 text-[9px] font-black text-slate-700 uppercase tracking-widest text-center border-l border-slate-100 w-36">
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span class="font-extrabold capitalize text-slate-800 text-[10px]">{{ role.name }}</span>
                                            <button 
                                                v-if="role.name !== 'admin'"
                                                type="button" 
                                                @click="saveRolePermissions(role.id)"
                                                :disabled="savingRoleIds.includes(role.id)"
                                                class="px-2 py-0.5 text-[8px] font-black uppercase text-[#0078d4] hover:text-[#005a9e] bg-slate-100 border border-slate-200 rounded-sm cursor-pointer select-none disabled:opacity-50"
                                            >
                                                {{ savingRoleIds.includes(role.id) ? 'Saving...' : 'Save' }}
                                            </button>
                                            <span 
                                                v-else 
                                                class="px-2 py-0.5 text-[8px] font-black uppercase text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-sm select-none"
                                            >
                                                Full Root
                                            </span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="category in permissionCategories" :key="category.name">
                                    <!-- Category Group Header -->
                                    <tr class="bg-slate-50/50 border-b border-slate-100">
                                        <td :colspan="roles.length + 1" class="px-4 py-2 text-[9px] font-black uppercase text-[#0078d4] tracking-widest">
                                            {{ category.name }}
                                        </td>
                                    </tr>
                                    <tr v-for="perm in category.items" :key="perm.name" class="hover:bg-slate-50/50 border-b border-slate-100">
                                        <td class="px-4 py-2.5">
                                            <div class="font-bold text-slate-700 text-[11px]">{{ perm.label }}</div>
                                            <div class="text-[9px] text-slate-400 font-mono font-bold">{{ perm.name }}</div>
                                        </td>
                                        <td v-for="role in roles" :key="role.id" class="px-4 py-2.5 text-center border-l border-slate-100">
                                            <!-- Admin column - locked to checked/disabled -->
                                            <input 
                                                v-if="role.name === 'admin'"
                                                type="checkbox" 
                                                checked
                                                disabled
                                                class="rounded-sm text-slate-400 border-slate-200 bg-slate-100 opacity-60 cursor-not-allowed"
                                            >
                                            <!-- Other roles - fully interactive checkboxes -->
                                            <input 
                                                v-else
                                                type="checkbox" 
                                                :value="perm.name" 
                                                v-model="matrixForm[role.id].permissions"
                                                class="rounded-sm text-[#0078d4] border-slate-300 focus:ring-[#0078d4] cursor-pointer"
                                            >
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
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

<style scoped>
.animate-slide-up {
    animation: slideUp 0.25s cubic-bezier(0.1, 0.9, 0.2, 1);
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
