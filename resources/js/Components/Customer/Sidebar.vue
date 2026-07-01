<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { LayoutDashboard, User, ChevronLeft, Zap, FileText } from 'lucide-vue-next';
import customer from '@/routes/customer';

const props = defineProps<{
    isCollapsed: boolean;
}>();

const emit = defineEmits(['toggleCollapse', 'update:activeSection']);

const page = usePage();

// Initialize active section based on current URL
const getInitialSection = () => {
    const url = page.url; 
    
    if (url.startsWith('/customer/profile')) {
        return 'profile';
    }

    if (url.startsWith('/customer/invoices')) {
        return 'dashboard';
    }

    if (url.startsWith('/customer/dashboard') || url === '/customer') {
        return 'dashboard';
    }
    // Default fallback
    return 'dashboard';
};

const activeSection = ref(getInitialSection()); 

const switchSection = (section: string) => {
    activeSection.value = section;
    emit('update:activeSection', section);
};

// Branding helper
const t = (key: string) => {
    if (key === 'tier_title') return page.props.branding?.customer?.sidebar?.tier_title || 'customer_tier_title';
    if (key === 'tier_subtitle') return page.props.branding?.customer?.sidebar?.tier_subtitle || 'customer_tier_subtitle';
    return page.props.branding?.customer?.sidebar?.[key] || key;
}
</script>

<template>
    <aside 
        id="customer-sidebar" 
        class="fixed lg:sticky top-0 h-screen inset-y-0 start-0 flex z-50 bg-admin-sidebar dark:bg-admin-sidebar-dark border-e border-admin-sidebar-border dark:border-admin-sidebar-border-dark transition-all duration-300 ease-in-out"
        :class="{ 'w-[72px]': isCollapsed, 'w-[300px]': !isCollapsed }"
    >
        
        <!-- TIER 1: Command Rail -->
        <div class="w-[72px] bg-admin-sidebar-rail dark:bg-admin-sidebar-rail-dark flex flex-col items-center py-4 space-y-6 flex-shrink-0 z-20">
            <Link :href="customer.dashboard.url()" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center shadow-lg cursor-pointer hover:brightness-110 transition-colors overflow-hidden border border-slate-200 dark:border-slate-700">
                <img v-if="$page.props.branding.business_settings?.logo_url" :src="$page.props.branding.business_settings.logo_url.startsWith('http') ? $page.props.branding.business_settings.logo_url : '/storage/' + $page.props.branding.business_settings.logo_url" class="w-full h-full object-cover" />
                <Zap v-else :size="20" fill="currentColor" class="text-[var(--brand-primary)]" />
            </Link>

            <div class="flex-1 flex flex-col items-center space-y-2 w-full">
                <!-- Dashboard -->
                <button 
                    @click="switchSection('dashboard')" 
                    class="admin-rail-btn w-full py-3 flex flex-col items-center transition-all group relative"
                    :class="activeSection === 'dashboard' ? 'active-rail' : 'rail-icon hover:brightness-110'"
                >
                    <LayoutDashboard :size="20" class="mb-1" />
                    <span class="rail-label absolute left-[70px] bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-xl z-50 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">{{ __(t('dashboard')) }}</span>
                </button>


                <!-- Profile -->
                <button 
                    @click="switchSection('profile')" 
                    class="admin-rail-btn w-full py-3 flex flex-col items-center transition-all group relative"
                    :class="activeSection === 'profile' ? 'active-rail' : 'rail-icon hover:brightness-110'"
                >
                    <User :size="20" class="mb-1" />
                   <span class="rail-label absolute left-[70px] bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-xl z-50 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">{{ __(t('profile')) }}</span>
                </button>

            </div>

            <div class="w-full px-4 pt-4 border-t border-slate-800/50 flex flex-col items-center space-y-4">
                <button @click="emit('toggleCollapse')" class="hidden lg:flex w-10 h-10 rounded-xl rail-icon hover:bg-slate-800 hover:text-sky-400 transition-all items-center justify-center relative group">
                    <ChevronLeft :size="20" :class="{'rotate-180': isCollapsed}" />
                    <span class="rail-label absolute left-[70px] bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-xl z-50 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">{{ __(t('collapse')) }}</span>
                </button>
            </div>
        </div>

        <!-- TIER 2: Sub-menu -->
        <div id="tier-2-container" class="bg-admin-sidebar dark:bg-admin-sidebar-dark flex flex-col h-full overflow-hidden transition-all duration-300 ease-in-out flex-shrink-0" :class="isCollapsed ? 'w-0 opacity-0 pointer-events-none' : 'w-[228px] opacity-100'">
            <div class="p-5 border-b border-sidebar-border dark:border-sidebar-border-dark">
                <h2 class="text-slate-900 dark:text-white font-extrabold text-xl tracking-tight line-clamp-1" :title="$page.props.branding.business_settings?.business_name">{{ $page.props.branding.business_settings?.business_name || __(t('tier_title')) }}</h2>
                <p class="text-xs text-slate-500 font-semibold mt-1.5 leading-snug">{{ $page.props.branding.business_settings?.tagline || __(t('tier_subtitle')) }}</p>
            </div>

            <div class="flex-1 p-4 space-y-1 overflow-y-auto">
                <!-- Dashboard Section -->
                <div v-if="activeSection === 'dashboard'" class="animate-fade-in">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 px-4">{{ __(t('main_menu')) }}</div>
                    <Link :href="customer.dashboard.url()" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all whitespace-nowrap text-sm" :class="$page.url === customer.dashboard.url() ? 'bg-admin-active text-admin-active-text font-bold nav-link-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'">
                        <LayoutDashboard :size="16" />
                        <span>{{ __(t('dashboard')) }}</span>
                    </Link>
                    <Link :href="customer.invoices.index.url()" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all whitespace-nowrap text-sm" :class="$page.url.startsWith(customer.invoices.index.definition.url) ? 'bg-admin-active text-admin-active-text font-bold nav-link-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'">
                        <FileText :size="16" />
                        <span>{{ __('Invoices') }}</span>
                    </Link>
                </div>

                <!-- Profile Section -->
                <div v-if="activeSection === 'profile'" class="animate-fade-in space-y-1">
                    <div class="px-4 mb-3">
                         <h2 class="text-slate-900 dark:text-white font-extrabold text-sm tracking-tight">{{ __(t('account_settings')) }}</h2>
                         <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">{{ __(t('manage_profile')) }}</p>
                    </div>

                    <Link :href="customer.profile.edit.url()" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all whitespace-nowrap text-sm" :class="$page.url.startsWith(customer.profile.edit.definition.url) ? 'bg-admin-active text-admin-active-text font-bold nav-link-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'">
                        <User :size="16" class="opacity-70" />
                        <span class="text-sm">{{ __(t('my_profile')) }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.active-rail {
    background-color: var(--admin-active-item-bg);
    color: var(--admin-active-item-text) !important;
    border-right: 4px solid var(--admin-active-item-border);
}
:global(.dark) .active-rail {
    background-color: var(--admin-active-item-bg-dark);
    color: var(--admin-active-item-text-dark) !important;
    border-right: 4px solid var(--admin-active-item-border-dark);
}

[dir="rtl"] .active-rail {
    border-right: none;
    border-left: 4px solid var(--admin-active-item-border);
}
[dir="rtl"] :global(.dark) .active-rail {
    border-left: 4px solid var(--admin-active-item-border-dark);
}

.nav-link-active {
    background-color: var(--admin-active-item-bg);
    color: var(--admin-active-item-text);
    font-weight: 700;
}
:global(.dark) .nav-link-active {
    background-color: var(--admin-active-item-bg-dark);
    color: var(--admin-active-item-text-dark);
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.rail-icon {
    color: var(--admin-sidebar-icon);
}
:global(.dark) .rail-icon {
    color: var(--admin-sidebar-icon-dark);
}
</style>
