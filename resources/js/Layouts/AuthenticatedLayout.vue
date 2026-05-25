<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const isSidebarOpen = ref(true);
const isMobile = ref(false);
const searchQuery = ref('');

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebarOnMobile = () => {
    if (isMobile.value) {
        isSidebarOpen.value = false;
    }
};

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        const currentRoute = route().current();
        let targetRoute = 'jobs.index'; // Default fallback
        
        if (currentRoute) {
            const path = currentRoute.toLowerCase();
            if (path.includes('intake')) {
                targetRoute = 'intakes.index';
            } else if (path.includes('job')) {
                targetRoute = 'jobs.index';
            } else if (path.includes('quotation')) {
                targetRoute = 'quotations.index';
            } else if (path.includes('customer')) {
                targetRoute = 'customers.index';
            } else if (path.includes('part') || path.includes('inventory')) {
                targetRoute = 'parts.index';
            } else if (path.includes('demo')) {
                targetRoute = 'demo-issuances.index';
            } else if (path.includes('gate-pass') || path.includes('gatepass')) {
                targetRoute = 'gate-passes.index';
            } else if (path.includes('sales-order') || path.includes('payment')) {
                targetRoute = 'sales-orders.index';
            } else if (path.includes('user')) {
                targetRoute = 'admin.users.index';
            }
        }
        
        router.visit(route(targetRoute, { search: searchQuery.value }));
    }
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

const permissions = computed(() => user?.permissions || []);
const hasPermission = (permission) => isAdmin.value || permissions.value.includes(permission);

const canAccessFinancials = computed(() => hasPermission('view financial data'));
const canAccessIntakes = computed(() => hasPermission('view intakes'));
const canAccessJobs = computed(() => hasPermission('view jobs'));
const canAccessInventory = computed(() => hasPermission('view parts'));
const canAccessCustomers = computed(() => hasPermission('view customers'));
const canAccessDemo = computed(() => hasPermission('view demo-issuances'));
const canAccessGatePass = computed(() => hasPermission('view gate-passes'));

const handleResize = () => {
    const wasMobile = isMobile.value;
    isMobile.value = window.innerWidth < 1024;
    
    // Only force sidebar state if we actually crossed the mobile/desktop boundary!
    if (isMobile.value !== wasMobile) {
        if (isMobile.value) {
            isSidebarOpen.value = false;
        } else {
            isSidebarOpen.value = true;
        }
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
    <div class="min-h-screen bg-[#fafafa]">
        <!-- Top M365 Crimson Sunset Header -->
        <header class="fixed top-0 left-0 right-0 h-12 bg-gradient-to-r from-[#800c0c] to-[#a31d1d] z-40 flex items-center justify-between px-3 text-white shadow-sm select-none">
            <div class="flex items-center gap-3">
                <!-- App Launcher Grid Icon Toggle -->
                <button @click="toggleSidebar" class="w-9 h-9 rounded hover:bg-white/10 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-xs tracking-wider uppercase border-r border-white/20 pr-3 hidden sm:inline-block">MEI Technical</span>
                    <span class="font-semibold text-xs tracking-wider uppercase border-r border-white/20 pr-3 inline-block sm:hidden">MEI</span>
                    <span class="text-[11px] font-medium text-white/95 hidden md:inline">Admin Center</span>
                </div>
            </div>

            <!-- Centered M365 Search Bar (Fully Functional & Mobile Responsive) -->
            <div class="flex-1 mx-2 sm:mx-4 max-w-[180px] sm:max-w-md md:max-w-lg">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        :placeholder="isMobile ? 'Search...' : 'Search users, jobs, brands or serial numbers...'" 
                        v-model="searchQuery"
                        @keyup.enter="handleSearch"
                        class="w-full bg-white text-slate-800 text-xs rounded-sm pl-8 pr-3 py-1.5 border border-transparent focus:border-white focus:ring-0 focus:outline-none transition-all placeholder-slate-400 h-8"
                    />
                </div>
            </div>

            <!-- Top Right Action Items & Profile Indicator -->
            <div class="flex items-center gap-4">
                <div class="hidden lg:flex items-center gap-1.5 text-[10px] font-semibold text-white/95">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>System Active</span>
                </div>
                <!-- Profile Initials MU style avatar -->
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-slate-900 border border-white/20 flex items-center justify-center font-bold text-xs uppercase text-white shadow-inner select-none cursor-pointer">
                        {{ user?.name?.substring(0,2) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Sidebar Navigation Drawer -->
        <aside 
            :class="[
                'fixed top-12 left-0 z-[60] h-[calc(100vh-48px)] transition-transform duration-200 border-r border-[#e1dfdd] bg-[#f3f2f1] text-[#323130] shadow-2xl select-none',
                isMobile ? 'w-full sm:w-80' : 'w-56',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <div class="h-full pt-2.5 pb-4 flex flex-col justify-between overflow-y-auto">
                <div class="space-y-1">
                    <!-- Sidebar Header (Consistent Navigation Row) -->
                    <div class="flex items-center justify-between px-4 pb-2 border-b border-[#e1dfdd] mb-2 select-none h-9">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Navigation</span>
                        <button v-if="isMobile" @click="isSidebarOpen = false" class="w-8 h-8 rounded flex items-center justify-center text-slate-500 hover:bg-[#e1dfdd] hover:text-[#dc2626] transition-colors focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Nav List -->
                    <nav class="space-y-0.5">
                        <Link :href="route('dashboard')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('dashboard') }">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Home
                        </Link>
                        
                        <div class="px-4 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-wider mt-4">Operational</div>
                        
                        <Link v-if="canAccessIntakes" :href="route('intakes.index')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('intakes.*') }">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
                            Intakes
                        </Link>
                        
                        <Link v-if="canAccessJobs" :href="route('jobs.index')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('jobs.*') }">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Repair Jobs
                        </Link>
                        
                        <Link v-if="canAccessFinancials" :href="route('quotations.index')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('quotations.*') }">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Quotations
                        </Link>
                        
                        <Link v-if="canAccessCustomers" :href="route('customers.index')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('customers.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Customers
                        </Link>
                        
                        <Link v-if="canAccessInventory" :href="route('parts.index')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('parts.*') }">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Inventory
                        </Link>
                        
                        <Link v-if="canAccessDemo" :href="route('demo-issuances.index')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('demo-issuances.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            Demo Goods
                        </Link>
                        
                        <Link v-if="canAccessGatePass" :href="route('gate-passes.index')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('gate-passes.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Gate Passes
                        </Link>
                        
                        <Link v-if="canAccessFinancials" :href="route('tracking.index')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('tracking.*') }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 002-2h-2a2 2 0 00-2 2"></path></svg>
                            Audit Hub
                        </Link>

                        <!-- Admin M365 Collapsible Categories -->
                        <template v-if="isAdmin">
                            <div class="px-4 py-1.5 text-[9px] font-black text-slate-400 uppercase tracking-wider mt-4">Administrative</div>
                            
                            <Link :href="route('admin.users.index')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('admin.users.*') }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                Users
                            </Link>
                            
                            <Link :href="route('admin.settings')" @click="closeSidebarOnMobile" class="m365-nav-link" :class="{ 'active': route().current('admin.settings*') }">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31 2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Settings
                            </Link>
                        </template>
                    </nav>
                </div>

                <!-- Sign Out in Sidebar Footer -->
                <div class="px-2">
                    <div class="mx-2 my-1.5 border-t border-[#e1dfdd]"></div>
                    <Link :href="route('logout')" method="post" as="button" class="w-full text-left flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-[#323130] hover:text-[#dc2626] hover:bg-[#edebe9] rounded transition-colors select-none">
                        <svg class="w-4 h-4 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Sign Out
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Overlay for Mobile -->
        <div 
            v-if="isMobile && isSidebarOpen" 
            @click="isSidebarOpen = false" 
            class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-[55] animate-fade-in transition-opacity duration-200"
        ></div>

        <!-- Main Content Area -->
        <main :class="['transition-all duration-200 pt-16 px-4 md:px-8 pb-8 min-h-[calc(100vh-48px)]', isSidebarOpen && !isMobile ? 'ml-56' : 'ml-0']">
            <!-- Breadcrumbs bar mirroring M365 top -->
            <div class="flex items-center gap-1.5 text-[11px] text-[#605e5c] font-semibold mb-3 select-none">
                <span>MEI System</span>
                <span>&gt;</span>
                <span class="text-[#323130] capitalize">{{ route().current()?.split('.')?.[0] || 'Dashboard' }}</span>
            </div>
            
            <!-- Modern Slot Wrapper -->
            <div class="w-full overflow-x-auto animate-slide-up">
                <slot />
            </div>
        </main>
    </div>
</template>

<style>
/* Microsoft 365 specific Nav Link overrides */
.m365-nav-link {
    @apply flex items-center gap-3 px-4 py-3 text-xs font-semibold text-[#323130] transition-colors border-l-4 border-transparent hover:bg-[#edebe9] cursor-pointer;
    position: relative;
    z-index: 1;
    min-height: 44px;
    -webkit-tap-highlight-color: rgba(0,0,0,0.08);
    touch-action: manipulation;
    user-select: none;
    -webkit-user-select: none;
}
.m365-nav-link.active {
    @apply bg-white text-[#201f1e] font-bold border-l-4 border-[#dc2626];
}
.m365-nav-link svg {
    @apply w-4 h-4 transition-colors flex-shrink-0;
}
.m365-nav-link.active svg {
    @apply text-[#dc2626];
}
</style>
