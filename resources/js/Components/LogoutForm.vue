<script setup lang="ts">
import { logout } from '@/routes';
import { useForm } from '@inertiajs/vue3';

const form = useForm({});

const handleLogout = () => {
    form.post(logout.url());
};
</script>

<template>
    <form @submit.prevent="handleLogout" style="display: none;">
        <button type="submit">Logout</button>
    </form>
</template>
