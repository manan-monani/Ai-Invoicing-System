<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    LayoutDashboard, Users, Briefcase, ShieldCheck, ChevronLeft,
    Activity, BarChart2, User, Palette, Zap, History, Scale, Settings, Package, Layers, Boxes, Receipt, FileText, CreditCard
} from 'lucide-vue-next';
import admin from '@/routes/admin';
import employee from '@/routes/employee';

// Icon mapping
const icons: Record<string, any> = {
    LayoutDashboard, Users, Briefcase, ShieldCheck, ChevronLeft, 
    Activity, BarChart2, User, Palette, Zap, History, Scale, Settings, Package, Layers, Boxes, Receipt, FileText, CreditCard
};

const props = defineProps<{
    isCollapsed: boolean;
}>();

const emit = defineEmits(['toggleCollapse', 'update:activeSection']);

const page = usePage();
const permissions = computed(() => (page.props as any).auth.available_permissions || {});
const isEmployee = computed(() => {
    const rawType = (page.props as any).auth?.user?.type;
    return rawType === 'employee' || rawType?.value === 'employee' || rawType?.name === 'employee';
});
const dashboardRoute = computed(() => (isEmployee.value ? employee.dashboard : admin.dashboard));
const profileUrl = computed(() => `${isEmployee.value ? '/admin/employee' : '/admin'}/profile`);

const can = (permission: string) => {
    const auth = (page.props as any).auth;
    return auth?.is_super_admin || auth?.permissions?.includes(permission);
};

// Initialize active section based on current URL
const getInitialSection = () => {
    const url = page.url.replace('/admin/employee', '/admin');
    // Check if URL matches any sub-module route
    // Since we don't have route name -> URL map here easily without traversing, 
    // we rely on specific checks or passed 'active' state if we verified it.
    // For now, keep the heuristic logic but iterate permissions if possible?
    // Let's stick to the simpler heuristic or a "findSectionByUrl" logic if robust.
    
    // Heuristic fallback matching the static logic's intent:
    if (url.startsWith('/admin/users') || url.startsWith('/admin/roles') || url.startsWith('/admin/profile')) return 'account_section';
    if (url.startsWith('/admin/business')) return 'system_section';
    if (url.startsWith('/admin/catalog') || url.startsWith('/admin/invoices') || url.startsWith('/admin/dashboard') || url === '/admin') return 'catalog_section';
    if (url.startsWith('/admin/activity-logs')) return 'system_section';
    
    return 'system_section'; // Default
};

const activeSectionKey = ref(getInitialSection());
const activeSection = computed(() => permissions.value[activeSectionKey.value]);

const switchSection = (key: string) => {
    activeSectionKey.value = key;
    emit('update:activeSection', key);
};

const normalizePath = (url: string) => {
    if (!url || url === '#') {
        return '';
    }

    let path = url;
    try {
        if (url.startsWith('http://') || url.startsWith('https://')) {
            path = new URL(url).pathname;
        }
    } catch {
        path = url;
    }

    return path.split('?')[0].split('#')[0].replace(/\/$/, '');
};

const isActiveModule = (moduleUrl: string) => {
    const currentPath = normalizePath(page.url);
    const targetPath = normalizePath(moduleUrl || '');

    if (!targetPath) {
        return false;
    }

    return currentPath === targetPath || currentPath.startsWith(`${targetPath}/`);
};

// Branding helper
const t = (key: string) => {
    return page.props.branding?.admin?.sidebar?.[key] || key;
}

// Helper to resolve route URL safely
// Note: In a real dynamic setup, we'd need route() helper available or pass URLs. 
// For now, we assume 'admin' route object is imported and we can map route names or use the ziggy route() global if available.
// Since we have `admin` imported from routes/admin, we can try to find it there or use the global route() function provided by Ziggy (Laravel).
// However, the Permissions.php file stores route NAMES (e.g. 'admin.dashboard').
// We need a way to resolve 'admin.dashboard' to a URL string. 
// If Ziggy is global: route('admin.dashboard').
// Let's assume global route() is available in the template, but in script we might need it.
</script>

<template>
    <aside 
        id="admin-sidebar" 
        class="fixed lg:sticky top-0 h-screen inset-y-0 start-0 flex z-50 bg-admin-sidebar dark:bg-admin-sidebar-dark border-e border-admin-sidebar-border dark:border-admin-sidebar-border-dark transition-all duration-300 ease-in-out"
        :class="{ 'w-[72px]': isCollapsed, 'w-[300px]': !isCollapsed }"
    >
        
        <!-- TIER 1: Command Rail -->
        <div class="w-[72px] bg-admin-sidebar-rail dark:bg-admin-sidebar-rail-dark flex flex-col items-center py-4 space-y-6 flex-shrink-0 z-20">
            <Link :href="dashboardRoute.url()" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center shadow-lg cursor-pointer hover:brightness-110 transition-colors overflow-hidden border border-slate-200 dark:border-slate-700">
                <img v-if="$page.props.branding.business_settings?.logo_url" :src="$page.props.branding.business_settings.logo_url.startsWith('http') ? $page.props.branding.business_settings.logo_url : '/storage/' + $page.props.branding.business_settings.logo_url" class="w-full h-full object-cover" />
                <Zap v-else :size="20" fill="currentColor" class="text-[var(--brand-primary)]" />
            </Link>

            <div class="flex-1 flex flex-col items-center space-y-2 w-full">
                <template v-for="(section, key) in permissions" :key="key">
                    <button 
                        v-if="Object.keys(section.sub_modules || {}).some(k => can(k))"
                        @click="switchSection(String(key))" 
                        class="admin-rail-btn w-full py-3 flex flex-col items-center transition-all group relative"
                        :class="activeSectionKey === key ? 'active-rail' : 'rail-icon hover:brightness-110'"
                    >
                        <component :is="icons[section.icon] || Zap" :size="20" class="mb-1" />
                        <span class="rail-label absolute left-[70px] bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-xl z-50 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                            {{ __(t(String(section.label))) }}
                        </span>
                    </button>
                </template>
            </div>

            <div class="w-full px-4 pt-4 border-t border-slate-800/50 flex flex-col items-center space-y-4">
                <button @click="emit('toggleCollapse')" class="hidden lg:flex w-10 h-10 rounded-xl rail-icon hover:bg-slate-800 hover:text-sky-400 transition-all items-center justify-center relative group">
                    <ChevronLeft :size="20" :class="{'rotate-180': isCollapsed}" />
                    <span class="rail-label absolute left-[70px] bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-xl z-50 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">{{ __(t('collapse')) }}</span>
                </button>
            </div>
        </div>

        <!-- TIER 2: Sub-menu -->
        <div id="tier-2-container" class="bg-admin-sidebar dark:bg-admin-sidebar-dark flex flex-col h-full overflow-hidden transition-all duration-300 ease-in-out" :class="isCollapsed ? 'w-0 opacity-0 pointer-events-none' : 'w-[228px] opacity-100'">
            <div class="p-5 border-b border-sidebar-border dark:border-sidebar-border-dark">
                <h2 class="text-slate-900 dark:text-white text-xl font-extrabold tracking-tight line-clamp-1" :title="$page.props.branding.business_settings?.business_name">{{ $page.props.branding.business_settings?.business_name || __(t('tier_title')) }}</h2>
                <p class="text-sm text-slate-500 mt-1.5 leading-snug">{{ $page.props.branding.business_settings?.tagline || __(t('tier_subtitle')) }}</p>
            </div>

            <div class="flex-1 p-4 space-y-1 overflow-y-auto">
                <div v-if="activeSection" class="animate-fade-in">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 px-4">
                        {{ __(t(activeSection.label)) }}
                    </div>
                    
                    <template v-for="(module, key) in activeSection.sub_modules" :key="key">
                        <Link 
                            v-if="can(String(key))" 
                            :href="module.url" 
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all whitespace-nowrap text-sm"  
                            :class="isActiveModule(module.url) ? 'bg-admin-active text-admin-active-text font-bold nav-link-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'"
                        >
                            <component :is="icons[module.icon] || Zap" :size="16" :class="{ 'opacity-70': !$page.url.startsWith(module.url) }" />
                            <span>{{ __(t(String(module.label))) }}</span>
                        </Link>
                    </template>

                    <!-- Special Case: My Profile (if not in permissions, user might still need it) -->
                    <!-- Usually Profile is common. If it's not in permissions, add it manually under Account section or generic? -->
                    <!-- For now, we only render what's in permissions. Profile is handled via User dropdown usually, but if we want it in sidebar: -->
                    <template v-if="activeSectionKey === 'account_section'">
                         <Link :href="profileUrl" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all whitespace-nowrap text-sm" :class="isActiveModule(profileUrl) ? 'bg-admin-active text-admin-active-text font-bold nav-link-active' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <User :size="16" class="opacity-70" />
                            <span class="text-sm">{{ __(t('my_profile')) }}</span>
                        </Link>
                    </template>
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
