<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Home, ChevronRight, Mail, Phone, MapPin, Facebook, Twitter, Instagram } from 'lucide-vue-next';

// Access business settings from props
const page = usePage();
const businessSettings = computed(() => (page.props.branding as any)?.business_settings || {});
</script>

<template>
    <Head :title="__('Contact Us')" />
    <PublicLayout>
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-sm mb-6 animate-fade-in">
            <Link href="/" class="flex items-center text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                <Home class="w-4 h-4 mr-1" />
                {{ __('Home') }}
            </Link>
            <ChevronRight class="w-4 h-4 text-gray-400" />
            <span class="text-gray-900 dark:text-white font-medium">{{ __('Contact Us') }}</span>
        </nav>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden animate-fade-in-up">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-gray-700 dark:to-gray-800 px-8 sm:px-12 py-10 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-start gap-4">
                    <div class="hidden sm:flex items-center justify-center w-16 h-16 bg-white dark:bg-gray-900 rounded-2xl shadow-md">
                        <Mail class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white mb-2">
                            {{ __('Contact Us') }}
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('We would love to hear from you. Please reach out to us using the contact details below.') }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Content Section -->
            <div class="px-8 sm:px-12 py-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Contact Information -->
                    <div class="space-y-8">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">
                                {{ __('Get in Touch') }}
                            </h3>
                            <div class="space-y-6">
                                <div v-if="businessSettings.contact_email" class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-primary-500 shadow-sm shrink-0">
                                        <Mail :size="24" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('Email Us') }}</p>
                                        <a :href="'mailto:' + businessSettings.contact_email" class="text-lg font-bold text-gray-900 dark:text-white hover:text-primary-600 transition-colors break-all">
                                            {{ businessSettings.contact_email }}
                                        </a>
                                    </div>
                                </div>

                                <div v-if="businessSettings.contact_phone" class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-primary-500 shadow-sm shrink-0">
                                        <Phone :size="24" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('Call Us') }}</p>
                                        <a :href="'tel:' + businessSettings.contact_phone" class="text-lg font-bold text-gray-900 dark:text-white hover:text-primary-600 transition-colors">
                                            {{ businessSettings.contact_phone }}
                                        </a>
                                    </div>
                                </div>

                                <div v-if="businessSettings.contact_address" class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-primary-500 shadow-sm shrink-0">
                                        <MapPin :size="24" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('Visit Us') }}</p>
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ businessSettings.contact_address }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div v-if="businessSettings.social_facebook || businessSettings.social_twitter || businessSettings.social_instagram">
                         <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">
                            {{ __('Social Presence') }}
                        </h3>
                        <div class="grid gap-4">
                            <a v-if="businessSettings.social_facebook" :href="businessSettings.social_facebook" target="_blank" class="flex items-center gap-4 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/10 text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/20 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <Facebook :size="20" />
                                </div>
                                <span class="font-bold text-lg">Facebook</span>
                            </a>
                            <a v-if="businessSettings.social_twitter" :href="businessSettings.social_twitter" target="_blank" class="flex items-center gap-4 p-4 rounded-xl bg-sky-50 dark:bg-sky-900/10 text-sky-700 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/20 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <Twitter :size="20" />
                                </div>
                                <span class="font-bold text-lg">Twitter</span>
                            </a>
                            <a v-if="businessSettings.social_instagram" :href="businessSettings.social_instagram" target="_blank" class="flex items-center gap-4 p-4 rounded-xl bg-pink-50 dark:bg-pink-900/10 text-pink-700 dark:text-pink-400 hover:bg-pink-100 dark:hover:bg-pink-900/20 transition-all group">
                                <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <Instagram :size="20" />
                                </div>
                                <span class="font-bold text-lg">Instagram</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
             <!-- Bottom info -->
            <div class="px-8 sm:px-12 py-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                <Link href="/" class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    <Home class="w-4 h-4" />
                    {{ __('Back to Home') }}
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInUp {
    from { 
        opacity: 0; 
        transform: translateY(20px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}
</style>
