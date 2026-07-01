<script setup lang="ts">
import Toast from '@/Components/Common/Toast.vue';
import { contact } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { dashboard as customerDashboard } from '@/routes/customer';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// ... (previous imports)

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const businessSettings = computed(() => (page.props.branding as any)?.business_settings || {});

const logoUrl = computed(() => {
    const logo = businessSettings.value.business_logo || businessSettings.value.logo_url;
    if (!logo) return null;
    return logo.startsWith('http') ? logo : '/storage/' + logo;
});

const dashboardUrl = computed(() => {
    if (!user.value) return '#';
    return user.value.type === 'admin' || user.value.type === 'super-admin' 
        ? adminDashboard.url() 
        : customerDashboard.url();
});

const isMobileMenuOpen = ref(false);

onMounted(() => {
    const direction = localStorage.getItem('direction') || 'ltr';
    document.documentElement.setAttribute('dir', direction);
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <Toast />
        
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-50 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo/Brand & Legal Links -->
                    <div class="flex items-center gap-8">
                        <Link href="/" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                            <img v-if="logoUrl" :src="logoUrl" class="h-10 w-auto object-contain" :alt="businessSettings.business_name || 'Logo'" />
                            <span class="text-xl font-bold text-gray-800 dark:text-white truncate max-w-[150px] md:max-w-none">
                                {{ businessSettings.business_name || 'Laravel Factory' }}
                            </span>
                        </Link>

                        <div class="hidden lg:flex items-center gap-6">
                            <Link :href="contact.url()" class="text-sm font-bold text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors">
                                {{ __('Contact Us') }}
                            </Link>
                        </div>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden lg:flex items-center gap-4">
                        <div class="h-6 w-px bg-gray-200 dark:bg-gray-700"></div>
                        
                        <template v-if="user">
                            <Link :href="dashboardUrl" class="text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                {{ __('Dashboard') }}
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="login.url()" class="text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                {{ __('Log in') }}
                            </Link>
                            <Link :href="register.url()" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-full font-medium transition-all shadow-lg shadow-primary-500/30 hover:shadow-primary-500/50">
                                {{ __('Get Started') }}
                            </Link>
                        </template>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex items-center lg:hidden gap-3">
                        <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400">
                            <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
                            <X v-else class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div v-show="isMobileMenuOpen" class="lg:hidden py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col gap-3">
                        <div class="space-y-2 mb-2">
                            <Link :href="contact.url()" class="block text-base font-bold text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 py-2">
                                {{ __('Contact Us') }}
                            </Link>
                        </div>

                        <div class="pt-2 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-3">
                            <template v-if="user">
                                <Link :href="dashboardUrl" class="text-center py-2 px-4 bg-primary-50 dark:bg-primary-900/10 text-primary-600 dark:text-primary-400 rounded-lg font-medium">
                                    {{ __('Dashboard') }}
                                </Link>
                            </template>
                            <template v-else>
                                <Link :href="login.url()" class="text-center text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 font-medium py-2">
                                    {{ __('Log in') }}
                                </Link>
                                <Link :href="register.url()" class="text-center bg-primary-600 hover:bg-primary-700 text-white px-5 py-3 rounded-lg font-bold transition-all shadow-lg shadow-primary-500/30">
                                    {{ __('Get Started') }}
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-grow">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400 text-center md:text-left">
                        &copy; {{ new Date().getFullYear() }} {{ businessSettings.business_name || 'Company Name' }}. {{ __('All rights reserved.') }}
                    </div>
                    <div class="flex flex-wrap justify-center gap-6">
                        <Link :href="contact.url()" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                            {{ __('Contact Us') }}
                        </Link>
                    </div>
                </div>
            </div>
        </footer>


    </div>
</template>
