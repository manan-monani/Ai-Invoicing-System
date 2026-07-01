<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Common/Toast.vue';
import { onMounted } from 'vue';

onMounted(() => {
    const direction = localStorage.getItem('direction') || 'ltr';
    document.documentElement.setAttribute('dir', direction);
});
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
        <Toast />
        <div class="absolute top-4 right-4 flex items-center space-x-2">
        </div>
        <div>
            <Link href="/" class="flex justify-center mb-6">
                <img v-if="$page.props.branding?.business_settings?.logo_url" :src="$page.props.branding.business_settings.logo_url.startsWith('http') ? $page.props.branding.business_settings.logo_url : '/storage/' + $page.props.branding.business_settings.logo_url" class="h-20 w-auto object-contain" />
                <span v-else class="text-3xl font-bold text-gray-800 dark:text-white">{{ $page.props.branding?.business_settings?.business_name || 'Laravel Factory' }}</span>
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
            <slot />
        </div>
    </div>
</template>
