<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const isSidebarOpen = ref(true);
const isMobile = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const user = usePage().props.auth.user;
const role = computed(() => user?.roles?.[0]?.name || user?.role || 'staff');
const isAdmin = computed(() => role.value === 'admin');
const isManager = computed(() => role.value === 'manager');
const isTechnician = computed(() => role.value === 'technician');
const isSupervisor = computed(() => role.value === 'supervisor');
const isSales = computed(() => role.value === 'sales');
const isAccountant = computed(() => role.value === 'accountant');
const isStore = computed(() => role.value === 'store');

const canAccessFinancials = computed(() => ['admin', 'manager', 'accountant', 'supervisor'].includes(role.value));
const canAccessIntakes = computed(() => ['admin', 'manager', 'sales', 'technician', 'supervisor'].includes(role.value));
const canAccessJobs = computed(() => ['admin', 'manager', 'store', 'technician', 'supervisor'].includes(role.value));
const canAccessInventory = computed(() => ['admin', 'manager', 'store', 'supervisor'].includes(role.value));
const canAccessCustomers = computed(() => ['admin', 'manager', 'sales', 'accountant', 'store', 'technician', 'supervisor'].includes(role.value));
const canAccessDemo = computed(() => ['admin', 'manager', 'sales', 'store', 'supervisor'].includes(role.value));
const canAccessGatePass = computed(() => ['admin', 'manager', 'store', 'supervisor'].includes(role.value));

const handleResize = () => {
    isMobile.value = window.innerWidth < 768;
    if (isMobile.value) {
        isSidebarOpen.value = false;
    } else {
        isSidebarOpen.value = true;
    }
};

onMounted(() => {
    handleResize();
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});
</script>

<template>
    <div class="min-h-screen bg-slate-50/50">
        <!-- Sidebar -->
        <aside 
            :class="[
                'fixed top-0 left-0 z-50 h-screen w-56 transition-transform duration-300 border-r border-slate-900 bg-slate-950 text-slate-200 shadow-xl shadow-slate-950/10',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <div class="h-full px-4 py-6 overflow-y-auto flex flex-col w-56 justify-between">
                <div>
                    <!-- Logo / Brand Section -->
                    <div class="mb-8 flex items-center gap-3 px-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-red-600 to-red-700 rounded-xl flex items-center justify-center shadow-lg shadow-red-600/30">
                            <span class="text-white text-xs font-black italic">M</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-black uppercase tracking-[0.25em] text-white">MEI</span>
                            <span class="text-[8px] font-bold text-red-500 uppercase tracking-widest leading-none">TECHNICAL</span>
                        </div>
                    </div>

                    <!-- Nav Links -->
                    <nav class="space-y-1.5">
                        <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest px-3 mb-2">Operational</div>
                        <Link :href="route('dashboard')" class="nav-link" :class="{ 'active': route().current('dashboard') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            Dashboard
                        </Link>
                        <Link v-if="canAccessIntakes" :href="route('intakes.index')" class="nav-link" :class="{ 'active': route().current('intakes.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Intakes
                        </Link>
                        <Link v-if="canAccessJobs" :href="route('jobs.index')" class="nav-link" :class="{ 'active': route().current('jobs.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Repair Jobs
                        </Link>
                        <Link v-if="canAccessFinancials" :href="route('quotations.index')" class="nav-link" :class="{ 'active': route().current('quotations.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Quotations
                        </Link>
                        <Link v-if="canAccessCustomers" :href="route('customers.index')" class="nav-link" :class="{ 'active': route().current('customers.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Customers
                        </Link>
                        <Link v-if="canAccessInventory" :href="route('parts.index')" class="nav-link" :class="{ 'active': route().current('parts.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Inventory
                        </Link>
                        <Link v-if="canAccessDemo" :href="route('demo-issuances.index')" class="nav-link" :class="{ 'active': route().current('demo-issuances.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            Demo Goods
                        </Link>
                        <Link v-if="canAccessGatePass" :href="route('gate-passes.index')" class="nav-link" :class="{ 'active': route().current('gate-passes.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Gate Passes
                        </Link>
                        <Link v-if="canAccessFinancials" :href="route('tracking.index')" class="nav-link" :class="{ 'active': route().current('tracking.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 002-2h-2a2 2 0 00-2 2"></path></svg>
                            Audit Hub
                        </Link>

                        <!-- Admin Section -->
                        <template v-if="isAdmin">
                            <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest px-3 mb-2 mt-6">Administrative</div>
                            <Link :href="route('admin.users.index')" class="nav-link" :class="{ 'active': route().current('admin.users.*') }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                Users
                            </Link>
                            <Link :href="route('admin.settings')" class="nav-link" :class="{ 'active': route().current('admin.settings*') }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31(2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Settings
                            </Link>
                        </template>
                    </nav>
                </div>

                <!-- Profile Footer Section inside Sidebar -->
                <div class="pt-6 border-t border-slate-800/80">
                    <div class="flex items-center gap-3 px-2 py-1">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-r from-red-600 to-red-700 flex items-center justify-center shadow-md shadow-red-600/35 text-white font-black text-xs uppercase">
                            {{ user?.name?.substring(0,2) }}
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-black text-white truncate max-w-[110px]">{{ user?.name }}</span>
                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest leading-none mt-0.5">{{ user?.roles?.[0]?.name || user?.role || 'Staff' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Overlay for Mobile -->
        <div 
            v-if="isMobile && isSidebarOpen" 
            @click="isSidebarOpen = false" 
            class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-40 animate-fade-in"
        ></div>

        <!-- Header -->
        <header :class="['fixed top-0 right-0 h-14 bg-white/85 backdrop-blur-md border-b border-slate-100 transition-all duration-300 z-30 flex items-center px-4 md:px-6 justify-between shadow-sm', isSidebarOpen && !isMobile ? 'left-56' : 'left-0']">
            <button @click="toggleSidebar" class="p-1.5 hover:bg-slate-50 rounded-xl text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
            <div class="flex items-center gap-4 md:gap-6">
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">MEI SYSTEM</span>
                    <span class="text-[8px] font-bold text-emerald-500 uppercase tracking-wider flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
                        Active Session
                    </span>
                </div>
                <Link :href="route('logout')" method="post" as="button" class="text-[10px] font-black text-slate-400 hover:text-red-600 uppercase tracking-widest transition-colors border-l border-slate-200 pl-4 h-5 flex items-center">Sign Out</Link>
            </div>
        </header>

        <!-- Main Content -->
        <main :class="['transition-all duration-300 pt-20 px-4 md:px-6 pb-8', isSidebarOpen && !isMobile ? 'ml-56' : 'ml-0']">
            <div class="max-w-7xl mx-auto animate-slide-up">
                <!-- Wrap Vue Slot inside responsive scroll box in case internal tables overflow -->
                <div class="w-full overflow-x-auto">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.nav-link {
    @apply flex items-center gap-3 px-3.5 py-2.5 text-[11px] font-black text-slate-400 uppercase tracking-widest transition-all duration-300 rounded-xl hover:text-white hover:bg-slate-900 hover:translate-x-1;
}
.nav-link.active {
    @apply text-white bg-gradient-to-r from-red-600 to-red-700 shadow-md shadow-red-600/30 translate-x-1;
}
.nav-link svg {
    @apply w-4 h-4 text-slate-500 transition-colors;
}
.nav-link.active svg {
    @apply text-white;
}
.nav-link:hover svg {
    @apply text-white;
}
</style>
