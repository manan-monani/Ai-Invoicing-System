<script setup lang="ts">
defineProps<{
    type?: 'button' | 'submit' | 'reset';
    variant?: 'primary' | 'secondary' | 'danger';
}>();
</script>

<template>
    <button
        :type="type || 'button'"
        class="px-4 py-2 rounded-md font-semibold text-white transition duration-150 ease-in-out shadow-sm"
        :class="{
            'bg-brand-600 hover:bg-brand-700': variant === 'primary' || !variant,
            'bg-slate-500 hover:bg-slate-600': variant === 'secondary',
            'bg-[color:var(--brand-danger)] hover:brightness-95': variant === 'danger',
        }"
    >
        <slot />
    </button>
</template>
