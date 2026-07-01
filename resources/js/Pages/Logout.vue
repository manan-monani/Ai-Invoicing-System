<script setup lang="ts">
import { logout } from '@/routes';
import { useForm } from '@inertiajs/vue3';

const form = useForm({});

const handleLogout = () => {
    form.post(logout.url());
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-100 dark:bg-slate-900">
        <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 text-center">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">Logging Out</h1>
            <p class="text-slate-600 dark:text-slate-400 mb-6">You are being logged out...</p>
            
            <button 
                @click="handleLogout"
                :disabled="form.processing"
                class="w-full bg-sky-600 hover:bg-sky-700 disabled:bg-slate-400 text-white font-bold py-3 px-4 rounded-lg transition-all"
            >
                {{ form.processing ? 'Logging out...' : 'Confirm Logout' }}
            </button>
        </div>
    </div>
</template>

<style scoped></style>
