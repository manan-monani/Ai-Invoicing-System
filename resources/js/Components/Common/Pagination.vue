<script setup lang="ts">
import SelectInput from '@/Components/Common/SelectInput.vue';
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginationLinks = PaginationLink[] | Record<string, string | null>;

type PaginationMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    path?: string;
};

const props = defineProps<{
    meta?: PaginationMeta;
    links?: PaginationLinks;
    perPageOptions?: number[];
    query?: Record<string, string | number | null | undefined>;
}>();

const perPageOptions = computed(() => props.perPageOptions ?? [10, 25, 50, 100]);
const perPageSelectOptions = computed(() => perPageOptions.value.map((size) => ({ value: size, label: String(size) })));

const meta = computed<PaginationMeta>(() => ({
    current_page: props.meta?.current_page ?? 1,
    last_page: props.meta?.last_page ?? 1,
    per_page: props.meta?.per_page ?? perPageOptions.value[0],
    path: props.meta?.path,
}));

const pageItems = computed<(number | string)[]>(() => {
    const current = meta.value.current_page;
    const last = meta.value.last_page;

    if (last <= 1) {
        return [1];
    }

    const pages = new Set<number>([1, last, current, current - 1, current + 1]);
    const filtered = Array.from(pages)
        .filter((page) => page >= 1 && page <= last)
        .sort((a, b) => a - b);

    const result: (number | string)[] = [];
    let prev: number | null = null;
    for (const page of filtered) {
        if (prev && page - prev > 1) {
            result.push('...');
        }
        result.push(page);
        prev = page;
    }

    return result;
});

const basePath = computed(() => props.meta?.path || window.location.pathname);

const goToPage = (page: number) => {
    router.get(
        basePath.value,
        {
            ...(props.query ?? {}),
            page,
            per_page: meta.value.per_page,
        },
        { preserveState: true, replace: true },
    );
};

const updatePerPage = (value: number) => {
    router.get(
        basePath.value,
        {
            ...(props.query ?? {}),
            page: 1,
            per_page: value,
        },
        { preserveState: true, replace: true },
    );
};
</script>

<template>
    <div class="flex flex-col gap-4 border-t border-slate-100 p-6 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800">
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:hover:bg-slate-800"
                :disabled="meta.current_page === 1"
                @click="goToPage(meta.current_page - 1)"
            >
                <ChevronLeft :size="16" />
            </button>
            <div class="flex items-center gap-2">
                <button
                    v-for="(page, index) in pageItems"
                    :key="`${page}-${index}`"
                    type="button"
                    class="h-9 min-w-[36px] rounded-xl border px-3 text-sm font-semibold transition-colors"
                    :class="
                        page === meta.current_page
                            ? 'border-brand-600 bg-brand-600 text-white'
                            : page === '...'
                              ? 'cursor-default border-transparent text-slate-400'
                              : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'
                    "
                    :disabled="page === '...' || page === meta.current_page"
                    @click="typeof page === 'number' && goToPage(page)"
                >
                    {{ page }}
                </button>
            </div>
            <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:hover:bg-slate-800"
                :disabled="meta.current_page === meta.last_page"
                @click="goToPage(meta.current_page + 1)"
            >
                <ChevronRight :size="16" />
            </button>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500 dark:text-slate-300">
            <div class="flex items-center gap-2">
                <span>{{ __('Rows') }}</span>
                <SelectInput
                    :model-value="meta.per_page"
                    :options="perPageSelectOptions"
                    :button-class="'h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-600 dark:text-slate-200'"
                    :list-class="'rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'"
                    :option-class="'text-slate-600 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
                    :wrapper-class="'w-auto'"
                    placement="top"
                    @update:model-value="updatePerPage(Number($event))"
                />
            </div>
            <span>{{ __('Page') }} {{ meta.current_page }} {{ __('of') }} {{ meta.last_page }}</span>
        </div>
    </div>
</template>
