<script setup lang="ts">
import { computed } from 'vue';
import { X } from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title?: string;
        subtitle?: string;
        maxWidth?: string;
    }>(),
    {
        title: '',
        subtitle: '',
        maxWidth: 'max-w-md',
    }
);

const emit = defineEmits<{
    (event: 'close'): void;
    (event: 'update:open', value: boolean): void;
}>();

const drawerClass = computed(() => `ml-auto h-full w-full ${props.maxWidth}`);

const close = () => {
    emit('update:open', false);
    emit('close');
};
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-[60] flex">
        <button type="button" class="absolute inset-0 bg-slate-900/50" @click="close" aria-label="Close drawer"></button>
        <transition name="slide-over">
            <div v-if="open" :class="drawerClass" class="bg-white dark:bg-slate-950 shadow-2xl border-l border-slate-200 dark:border-slate-800 relative z-[70] flex flex-col h-full">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between relative bg-white dark:bg-slate-950 flex-shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            {{ title }}
                        </h3>
                        <p v-if="subtitle" class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {{ subtitle }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 absolute top-4 right-4"
                        @click="close"
                        aria-label="Close drawer"
                    >
                        <X :size="18" />
                    </button>
                </div>

                <div class="p-6 space-y-5 overflow-y-auto flex-1">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-950 flex-shrink-0">
                    <slot name="footer" />
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.slide-over-enter-active,
.slide-over-leave-active {
    transition: transform 1.2s cubic-bezier(0.22, 1, 0.36, 1), opacity 1.2s cubic-bezier(0.22, 1, 0.36, 1);
}
.slide-over-enter-from,
.slide-over-leave-to {
    transform: translateX(110%);
    opacity: 0;
}
</style>
