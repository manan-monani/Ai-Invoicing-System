<script setup lang="ts">
import { PropType } from 'vue';
import { AlertTriangle, AlertCircle, Info, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: 'Are you sure?',
    },
    message: {
        type: String,
        default: 'This action cannot be undone.',
    },
    confirmText: {
        type: String,
        default: 'Confirm',
    },
    cancelText: {
        type: String,
        default: 'Cancel',
    },
    type: {
        type: String as PropType<'danger' | 'warning' | 'info'>,
        default: 'danger',
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirm']);

const colors = {
    danger: {
        iconBg: 'bg-[color:color-mix(in_srgb,var(--brand-danger),white_85%)]',
        iconText: 'text-[color:var(--brand-danger)]',
        button: 'bg-[color:var(--brand-danger)] hover:brightness-95 text-white shadow-sm',
        icon: AlertTriangle,
    },
    warning: {
        iconBg: 'bg-[color:color-mix(in_srgb,var(--brand-warning),white_85%)]',
        iconText: 'text-[color:var(--brand-warning)]',
        button: 'bg-[color:var(--brand-warning)] hover:brightness-95 text-white shadow-sm',
        icon: AlertCircle,
    },
    info: {
        iconBg: 'bg-[color:color-mix(in_srgb,var(--brand-info),white_85%)]',
        iconText: 'text-[color:var(--brand-info)]',
        button: 'bg-[color:var(--brand-info)] hover:brightness-95 text-white shadow-sm',
        icon: Info,
    },
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

        <div class="relative bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl overflow-hidden transform transition-all sm:max-w-lg w-full ring-1 ring-slate-900/5 dark:ring-white/10">
            <div class="p-6 text-center space-y-6">
                <!-- Icon -->
                <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center animate-bounce-short" :class="colors[type].iconBg + ' ' + colors[type].iconText">
                    <component :is="colors[type].icon" :size="32" />
                </div>

                <!-- Text -->
                <div>
                     <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ title }}</h3>
                     <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ message }}</p>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                     <button 
                        @click="$emit('close')" 
                        class="flex-1 px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-700 font-bold text-sm text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all"
                    >
                        {{ cancelText }}
                    </button>
                    <button 
                        @click="$emit('confirm')" 
                        :disabled="processing"
                        class="flex-1 px-6 py-3 rounded-xl font-bold text-sm shadow-xl transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                        :class="colors[type].button"
                    >
                        <Loader2 v-if="processing" class="animate-spin me-2" :size="16" />
                        {{ confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-bounce-short {
    animation: bounce 0.5s infinite alternate;
}
@keyframes bounce {
    from { transform: translateY(0); }
    to { transform: translateY(-5px); }
}
</style>
