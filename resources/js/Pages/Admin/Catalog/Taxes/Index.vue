<script setup lang="ts">
import Drawer from '@/Components/Common/Drawer.vue';
import Pagination from '@/Components/Common/Pagination.vue';
import SelectInput from '@/Components/Common/SelectInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { ArrowUpDown, Check, Pencil, Plus, Trash2, X } from 'lucide-vue-next';

type CategorySummary = {
    id: number;
    name: string;
    is_active: boolean;
};

type ItemSummary = {
    id: number;
    name: string;
    unit_price: string | number;
    is_active: boolean;
};

type Tax = {
    id: number;
    name: string;
    rate: string | number;
    type: 'percentage' | 'fixed';
    is_active: boolean;
    categories_count?: number | null;
    items_count?: number | null;
    created_at: string;
};

const props = defineProps<{
    taxes: {
        data: Tax[];
        links: { url: string | null; label: string; active: boolean }[];
        meta?: { current_page?: number; last_page?: number; per_page?: number; path?: string };
    };
    taxes_count?: number;
    tax_categories?: {
        data: CategorySummary[];
    } | null;
    tax_categories_tax?: {
        id: number;
        name: string;
    } | null;
    tax_items?: {
        data: ItemSummary[];
    } | null;
    tax_items_tax?: {
        id: number;
        name: string;
    } | null;
    filters?: {
        sort?: string;
        per_page?: number;
        tax_categories_id?: number | null;
        tax_items_id?: number | null;
        search?: string;
    };
}>();

const taxesList = computed<Tax[]>(() => props.taxes.data);
const page = usePage();
const isEmployee = computed(() => {
    const rawType = (page.props as any).auth?.user?.type;
    return rawType === 'employee' || rawType?.value === 'employee' || rawType?.name === 'employee';
});
const basePath = computed(() => (isEmployee.value ? '/admin/employee' : '/admin'));
const taxesBasePath = computed(() => `${basePath.value}/catalog/taxes`);
const sort = ref(props.filters?.sort || 'newest');
const search = ref(props.filters?.search ?? '');
const perPage = computed(() => Number(props.filters?.per_page ?? props.taxes.meta?.per_page ?? 10));
const isDrawerOpen = computed(() => showForm.value || Boolean(editingId.value));
const showCategoriesDrawer = ref(false);
const showItemsDrawer = ref(false);
const categoriesDrawerLoading = ref(false);
const itemsDrawerLoading = ref(false);
const taxCategories = computed<CategorySummary[]>(() => props.tax_categories?.data ?? []);
const taxItems = computed<ItemSummary[]>(() => props.tax_items?.data ?? []);
const taxCategoriesTax = computed(() => props.tax_categories_tax ?? null);
const taxItemsTax = computed(() => props.tax_items_tax ?? null);

const editingId = ref<number | null>(null);
const showForm = ref(false);
const confirmDeleteId = ref<number | null>(null);
const popoverDirections = ref<Record<number, 'top' | 'bottom'>>({});
const deletingId = ref<number | null>(null);

const form = useForm({
    name: '',
    rate: '',
    type: 'percentage',
    is_active: true,
});

const deleteForm = useForm({});

const validateForm = () => {
    form.clearErrors();
    let isValid = true;

    if (!form.name || !form.name.trim()) {
        form.setError('name', 'Tax name is required.');
        isValid = false;
    }

    const rateValue = Number(form.rate);
    if (form.rate === '' || Number.isNaN(rateValue)) {
        form.setError('rate', 'Rate is required.');
        isValid = false;
    } else if (rateValue < 0) {
        form.setError('rate', 'Rate must be zero or higher.');
        isValid = false;
    }

    if (!['percentage', 'fixed'].includes(form.type)) {
        form.setError('type', 'Please choose a valid type.');
        isValid = false;
    }

    return isValid;
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.type = 'percentage';
    form.is_active = true;
    editingId.value = null;
    showForm.value = false;
};

const startEdit = (tax: Tax) => {
    editingId.value = tax.id;
    showForm.value = true;
    confirmDeleteId.value = null;
    form.name = tax.name;
    form.rate = String(tax.rate);
    form.type = tax.type;
    form.is_active = tax.is_active;
};

const submit = () => {
    if (!validateForm()) {
        return;
    }

    if (editingId.value) {
        form.put(`${taxesBasePath.value}/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
            },
        });

        return;
    }

    form.post(taxesBasePath.value, {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
        },
    });
};

const applySort = () => {
    router.get(taxesBasePath.value, { sort: sort.value, per_page: perPage.value, search: search.value || null, page: 1 }, { preserveState: true, replace: true });
};

const paginationQuery = computed(() => ({
    sort: sort.value,
    tax_categories_id: props.filters?.tax_categories_id ?? undefined,
    tax_items_id: props.filters?.tax_items_id ?? undefined,
    search: search.value || null,
}));

const toggleSort = () => {
    sort.value = sort.value === 'newest' ? 'oldest' : 'newest';
    applySort();
};

const toggleNameSort = () => {
    sort.value = sort.value === 'name_asc' ? 'name_desc' : 'name_asc';
    applySort();
};

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(search, (value) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        router.get(
            taxesBasePath.value,
            { sort: sort.value, per_page: perPage.value, search: value || null, page: 1 },
            { preserveState: true, replace: true }
        );
    }, 300);
});

const openCategoriesDrawer = (tax: Tax) => {
    showForm.value = false;
    editingId.value = null;
    confirmDeleteId.value = null;
    showItemsDrawer.value = false;
    showCategoriesDrawer.value = true;
    categoriesDrawerLoading.value = true;

    router.get(
        taxesBasePath.value,
        {
            sort: sort.value,
            per_page: perPage.value,
            search: search.value || null,
            page: props.taxes.meta?.current_page ?? 1,
            tax_categories_id: tax.id,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                categoriesDrawerLoading.value = false;
            },
        }
    );
};

const openItemsDrawer = (tax: Tax) => {
    showForm.value = false;
    editingId.value = null;
    confirmDeleteId.value = null;
    showCategoriesDrawer.value = false;
    showItemsDrawer.value = true;
    itemsDrawerLoading.value = true;

    router.get(
        taxesBasePath.value,
        {
            sort: sort.value,
            per_page: perPage.value,
            search: search.value || null,
            page: props.taxes.meta?.current_page ?? 1,
            tax_items_id: tax.id,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                itemsDrawerLoading.value = false;
            },
        }
    );
};

const closeCategoriesDrawer = () => {
    showCategoriesDrawer.value = false;
    categoriesDrawerLoading.value = false;
    router.get(
        taxesBasePath.value,
        {
            sort: sort.value,
            per_page: perPage.value,
            search: search.value || null,
            page: props.taxes.meta?.current_page ?? 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const closeItemsDrawer = () => {
    showItemsDrawer.value = false;
    itemsDrawerLoading.value = false;
    router.get(
        taxesBasePath.value,
        {
            sort: sort.value,
            per_page: perPage.value,
            search: search.value || null,
            page: props.taxes.meta?.current_page ?? 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const requestDelete = (tax: Tax, event: MouseEvent) => {
    confirmDeleteId.value = tax.id;
    popoverDirections.value[tax.id] = 'bottom';

    nextTick(() => {
        const target = event.currentTarget as HTMLElement | null;
        if (!target) {
            return;
        }

        const rect = target.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        const spaceAbove = rect.top;
        const estimatedHeight = 240;

        if (spaceBelow < estimatedHeight && spaceAbove > spaceBelow) {
            popoverDirections.value[tax.id] = 'top';
        }
    });
};

const cancelDelete = () => {
    confirmDeleteId.value = null;
};

const destroyTax = (tax: Tax) => {
    deleteForm.delete(`${taxesBasePath.value}/${tax.id}`, {
        preserveScroll: true,
        onStart: () => {
            deletingId.value = tax.id;
        },
        onFinish: () => {
            confirmDeleteId.value = null;
            deletingId.value = null;
        },
    });
};

const formatDate = (value: string) => {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(date);
};
</script>

<template>
    <Head :title="__('Taxes')" />
    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-6xl mx-auto w-full p-4 lg:p-0">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Taxes') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Create, update, and manage tax rates.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button
                        v-if="editingId || showForm"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 text-sm font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                        @click="resetForm"
                    >
                        <X :size="14" />
                        {{ __('Close') }}
                    </button>
                    <button
                        v-else
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-brand-600 text-white font-bold text-sm shadow-lg shadow-brand-600/20 hover:bg-brand-700 transition-all"
                        @click="showForm = true"
                    >
                        <Plus :size="16" />
                        {{ __('New Tax') }}
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-visible">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        {{ __('All Taxes') }}
                        <span class="text-slate-400">({{ props.taxes_count ?? taxesList.length }})</span>
                    </div>
                    <div class="w-full sm:w-auto">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="__('Search taxes...')"
                            class="h-9 w-full sm:w-[240px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 text-sm text-slate-700 dark:text-slate-200 placeholder:text-slate-400"
                        />
                    </div>
                </div>
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 text-xs font-bold text-slate-500 uppercase tracking-widest grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_140px_120px_120px_180px_140px] gap-3 items-center">
                    <div class="flex items-center gap-2">
                        <span>{{ __('Tax Name') }}</span>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="toggleNameSort"
                            :aria-label="__('Toggle name sort order')"
                        >
                            <ArrowUpDown :size="14" />
                        </button>
                    </div>
                    <span class="text-center">{{ __('Tax Rate') }}</span>
                    <span class="text-center">{{ __('Categories') }}</span>
                    <span class="text-center">{{ __('Items') }}</span>
                    <div class="flex items-center justify-center gap-2">
                        <span>{{ __('Created At') }}</span>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="toggleSort"
                            :aria-label="__('Toggle sort order')"
                        >
                            <ArrowUpDown :size="14" />
                        </button>
                    </div>
                    <span class="md:text-right">{{ __('Actions') }}</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    <div v-if="taxesList.length === 0" class="p-6 text-sm text-slate-500">
                        {{ __('No taxes have been created yet.') }}
                    </div>
                    <div
                        v-for="tax in taxesList"
                        :key="tax.id"
                        class="p-3 md:p-4 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_140px_120px_120px_180px_140px] gap-3 items-start md:items-center"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-slate-900 dark:text-white">{{ tax.name }}</span>
                                <span
                                    class="inline-flex h-2 w-2 rounded-full"
                                    :class="tax.is_active ? 'bg-emerald-500' : 'bg-red-500'"
                                    :aria-label="tax.is_active ? __('Active') : __('Inactive')"
                                ></span>
                            </div>
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 text-center">
                            {{ tax.type === 'percentage' ? `${tax.rate}%` : `$${tax.rate}` }}
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 text-center">
                            <button
                                type="button"
                                class="font-semibold transition-colors"
                                :class="(tax.categories_count ?? 0) > 0 ? 'text-slate-700 dark:text-slate-200 hover:text-brand-600' : 'text-slate-400 cursor-default'"
                                :disabled="(tax.categories_count ?? 0) === 0"
                                @click="(tax.categories_count ?? 0) > 0 && openCategoriesDrawer(tax)"
                            >
                                {{ tax.categories_count ?? 0 }}
                            </button>
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 text-center">
                            <button
                                type="button"
                                class="font-semibold transition-colors"
                                :class="(tax.items_count ?? 0) > 0 ? 'text-slate-700 dark:text-slate-200 hover:text-brand-600' : 'text-slate-400 cursor-default'"
                                :disabled="(tax.items_count ?? 0) === 0"
                                @click="(tax.items_count ?? 0) > 0 && openItemsDrawer(tax)"
                            >
                                {{ tax.items_count ?? 0 }}
                            </button>
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 text-center">
                            {{ formatDate(tax.created_at) }}
                        </div>

                        <div class="flex items-center gap-2 md:justify-end">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                                @click="startEdit(tax)"
                                :aria-label="__('Edit')"
                            >
                                <Pencil :size="14" />
                            </button>
                            <div class="relative">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 text-red-600 hover:bg-red-50"
                                    @click="requestDelete(tax, $event)"
                                    :aria-label="__('Delete')"
                                >
                                    <Trash2 :size="14" />
                                </button>
                                <div
                                    v-if="confirmDeleteId === tax.id"
                                    class="absolute right-0 w-64 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl p-5 z-20"
                                    :class="popoverDirections[tax.id] === 'top' ? 'bottom-full mb-3' : 'top-full mt-3'"
                                >
                                    <div class="flex flex-col items-center text-center gap-2">
                                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                                            <Trash2 :size="16" />
                                        </div>
                                        <p class="text-xl font-semibold text-slate-800 dark:text-slate-100">{{ __('Delete') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ __('Are you sure you want to delete') }} {{ tax.name }}?
                                        </p>
                                    </div>
                                    <div class="mt-4 flex items-center justify-center gap-4">
                                        <button
                                            type="button"
                                            class="h-8 text-sm font-bold text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-white"
                                            @click="cancelDelete"
                                        >
                                            {{ __('Cancel') }}
                                        </button>
                                        <button
                                            type="button"
                                            class="h-8 text-sm font-bold text-white bg-red-600 hover:bg-red-700 px-4 rounded-lg flex items-center gap-2"
                                            @click="destroyTax(tax)"
                                        >
                                            <svg v-if="deletingId === tax.id" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            <span>{{ __('Delete') }}</span>
                                        </button>
                                    </div>
                                </div>
                                <button
                                    v-if="confirmDeleteId === tax.id"
                                    type="button"
                                    class="fixed inset-0 cursor-default z-10"
                                    @click="cancelDelete"
                                ></button>
                            </div>
                        </div>
                    </div>
                </div>
                <Pagination :meta="props.taxes.meta" :links="props.taxes.links" :query="paginationQuery" />
            </div>
        </div>

        <Drawer
            :open="isDrawerOpen"
            :title="editingId ? __('Edit Tax') : __('New Tax')"
            :subtitle="editingId ? __('Update the tax details below.') : __('Enter the tax details below.')"
            @close="resetForm"
            @update:open="(value) => !value && resetForm()"
        >
            <div>
                <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                    {{ __('Tax Name') }} <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    :placeholder="__('Enter tax name')"
                    class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all"
                    :style="{ '--tw-ring-color': 'var(--brand-primary)' }"
                />
                <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                        {{ __('Rate') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.rate"
                        type="number"
                        step="0.01"
                        min="0"
                        :placeholder="__('0.00')"
                        class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all appearance-none [-moz-appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                        :style="{ '--tw-ring-color': 'var(--brand-primary)' }"
                    />
                    <p v-if="form.errors.rate" class="text-xs text-red-500 mt-1">{{ form.errors.rate }}</p>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                        {{ __('Type') }} <span class="text-red-500">*</span>
                    </label>
                    <SelectInput
                        v-model="form.type"
                        :options="[
                            { value: 'percentage', label: __('Percentage') },
                            { value: 'fixed', label: __('Fixed Amount') },
                        ]"
                        :button-class="'px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all'"
                        :list-class="'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'"
                        :option-class="'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
                    />
                    <p v-if="form.errors.type" class="text-xs text-red-500 mt-1">{{ form.errors.type }}</p>
                </div>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 font-medium">
                <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-5 w-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                />
                {{ __('Status') }}
            </label>

            <template #footer>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-800"
                        @click="resetForm"
                    >
                        {{ __('Close') }}
                    </button>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-brand-600 text-white font-bold text-sm shadow-lg shadow-brand-600/20 hover:bg-brand-700 transition-all disabled:opacity-60"
                    >
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <Check v-else-if="editingId" :size="16" />
                        <Plus v-else :size="16" />
                        {{ editingId ? __('Save Tax') : __('Add Tax') }}
                    </button>
                </div>
            </template>
        </Drawer>

        <Drawer
            :open="showCategoriesDrawer"
            :title="taxCategoriesTax ? `${taxCategoriesTax.name} ${__('Categories')}` : __('Categories')"
            :subtitle="taxCategoriesTax ? __('Categories using this tax.') : __('Categories list.')"
            @close="closeCategoriesDrawer"
            @update:open="(value) => !value && closeCategoriesDrawer()"
        >
            <div class="space-y-4">
                <div v-if="categoriesDrawerLoading" class="text-sm text-slate-500">
                    {{ __('Loading categories...') }}
                </div>
                <div v-else>
                    <div v-if="taxCategories.length === 0" class="text-sm text-slate-500">
                        {{ __('No categories found for this tax.') }}
                    </div>
                    <div v-else class="divide-y divide-slate-100 dark:divide-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <div
                            v-for="category in taxCategories"
                            :key="category.id"
                            class="p-4 flex items-center justify-between gap-3"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white capitalize">{{ category.name }}</span>
                                <span
                                    class="inline-flex h-2 w-2 rounded-full"
                                    :class="category.is_active ? 'bg-emerald-500' : 'bg-red-500'"
                                    :aria-label="category.is_active ? __('Active') : __('Inactive')"
                                ></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Drawer>

        <Drawer
            :open="showItemsDrawer"
            :title="taxItemsTax ? `${taxItemsTax.name} ${__('Items')}` : __('Items')"
            :subtitle="taxItemsTax ? __('Items using this tax.') : __('Items list.')"
            @close="closeItemsDrawer"
            @update:open="(value) => !value && closeItemsDrawer()"
        >
            <div class="space-y-4">
                <div v-if="itemsDrawerLoading" class="text-sm text-slate-500">
                    {{ __('Loading items...') }}
                </div>
                <div v-else>
                    <div v-if="taxItems.length === 0" class="text-sm text-slate-500">
                        {{ __('No items found for this tax.') }}
                    </div>
                    <div v-else class="divide-y divide-slate-100 dark:divide-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <div
                            v-for="item in taxItems"
                            :key="item.id"
                            class="p-4 flex items-center justify-between gap-3"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white capitalize">{{ item.name }}</span>
                                <span
                                    class="inline-flex h-2 w-2 rounded-full"
                                    :class="item.is_active ? 'bg-emerald-500' : 'bg-red-500'"
                                    :aria-label="item.is_active ? __('Active') : __('Inactive')"
                                ></span>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                ${{ item.unit_price }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Drawer>
    </AdminLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
