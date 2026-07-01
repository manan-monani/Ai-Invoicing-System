<script setup lang="ts">
import Drawer from '@/Components/Common/Drawer.vue';
import Pagination from '@/Components/Common/Pagination.vue';
import SelectInput from '@/Components/Common/SelectInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { ArrowUpDown, Check, Eye, Pencil, Plus, Trash2, X } from 'lucide-vue-next';

type TaxOption = {
    id: number;
    name: string;
    rate: string | number;
    type: 'percentage' | 'fixed';
};

type ItemSummary = {
    id: number;
    name: string;
    unit_price: string | number;
    is_active: boolean;
};

type Category = {
    id: number;
    name: string;
    tax_id: number | null;
    tax?: TaxOption | null;
    is_active: boolean;
    has_discount: boolean;
    discount_amount: string | number | null;
    discount_is_percentage: boolean;
    items_count?: number | null;
    created_at: string;
    updated_at: string;
};

const props = defineProps<{
    categories: {
        data: Category[];
        links: { url: string | null; label: string; active: boolean }[];
        meta?: { current_page?: number; last_page?: number; per_page?: number; path?: string };
    };
    categories_count?: number;
    taxes: {
        data: TaxOption[];
    };
    category_items?: {
        data: ItemSummary[];
    } | null;
    category_items_category?: {
        id: number;
        name: string;
    } | null;
    filters?: {
        sort?: string;
        per_page?: number;
        items_category_id?: number | null;
        search?: string;
    };
}>();

const categoriesList = computed<Category[]>(() => props.categories.data);
const taxOptions = computed<TaxOption[]>(() => props.taxes.data || []);
const page = usePage();
const isEmployee = computed(() => {
    const rawType = (page.props as any).auth?.user?.type;
    return rawType === 'employee' || rawType?.value === 'employee' || rawType?.name === 'employee';
});
const basePath = computed(() => (isEmployee.value ? '/admin/employee' : '/admin'));
const categoriesBasePath = computed(() => `${basePath.value}/catalog/categories`);
const lookupsBasePath = computed(() => `${basePath.value}/lookups`);
const sort = ref(props.filters?.sort || 'newest');
const search = ref(props.filters?.search ?? '');
const perPage = computed(() => Number(props.filters?.per_page ?? props.categories.meta?.per_page ?? 10));
const paginationQuery = computed(() => ({
    sort: sort.value,
    items_category_id: props.filters?.items_category_id ?? undefined,
    search: search.value || null,
}));
const isDrawerOpen = computed(() => showForm.value || Boolean(editingId.value) || Boolean(viewingId.value));
const isViewMode = computed(() => Boolean(viewingId.value));
const showItemsDrawer = ref(false);
const itemsDrawerLoading = ref(false);
const categoryItems = computed<ItemSummary[]>(() => props.category_items?.data ?? []);
const categoryItemsCategory = computed(() => props.category_items_category ?? null);
const viewCategoryItems = computed(() => {
    if (!isViewMode.value || !viewingId.value) {
        return [];
    }

    if (categoryItemsCategory.value?.id === viewingId.value) {
        return categoryItems.value;
    }

    const matching = props.categories.data.find((category) => category.id === viewingId.value);
    if (!matching) {
        return [];
    }

    return categoryItems.value;
});

const editingId = ref<number | null>(null);
const viewingId = ref<number | null>(null);
const showForm = ref(false);
const confirmDeleteId = ref<number | null>(null);
const popoverDirections = ref<Record<number, 'top' | 'bottom'>>({});
const deletingId = ref<number | null>(null);

const form = useForm({
    name: '',
    tax_id: '' as number | '' | null,
    is_active: true,
    has_discount: false,
    discount_amount: '' as number | '' | null,
    discount_is_percentage: false,
});

const taxSearchUrl = computed(() => `${lookupsBasePath.value}/taxes`);

const deleteForm = useForm({});

const validateForm = () => {
    form.clearErrors();
    let isValid = true;

    if (!form.name || !form.name.trim()) {
        form.setError('name', 'Category name is required.');
        isValid = false;
    }

    if (form.tax_id !== '' && form.tax_id !== null) {
        const taxId = Number(form.tax_id);
        if (!taxOptions.value.some((tax) => tax.id === taxId)) {
            form.setError('tax_id', 'Please choose a valid tax.');
            isValid = false;
        }
    }

    if (form.has_discount) {
        const discountValue = Number(form.discount_amount);
        if (form.discount_amount !== '' && Number.isNaN(discountValue)) {
            form.setError('discount_amount', 'Please enter a valid discount.');
            isValid = false;
        } else if (discountValue < 0) {
            form.setError('discount_amount', 'Discount must be zero or higher.');
            isValid = false;
        }
    }

    return isValid;
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.tax_id = '';
    form.is_active = true;
    form.has_discount = false;
    form.discount_amount = '';
    form.discount_is_percentage = false;
    editingId.value = null;
    viewingId.value = null;
    showForm.value = false;
};

const startEdit = (category: Category) => {
    form.clearErrors();
    editingId.value = category.id;
    viewingId.value = null;
    showForm.value = true;
    confirmDeleteId.value = null;
    form.name = category.name;
    form.tax_id = category.tax_id ?? '';
    form.is_active = category.is_active;
    form.has_discount = category.has_discount;
    form.discount_amount = category.discount_amount ?? '';
    form.discount_is_percentage = category.discount_is_percentage;
};

const startView = (category: Category) => {
    form.clearErrors();
    viewingId.value = category.id;
    editingId.value = null;
    showForm.value = false;
    confirmDeleteId.value = null;
    showItemsDrawer.value = false;
    form.name = category.name;
    form.tax_id = category.tax_id ?? '';
    form.is_active = category.is_active;
    form.has_discount = category.has_discount;
    form.discount_amount = category.discount_amount ?? '';
    form.discount_is_percentage = category.discount_is_percentage;

    if (categoryItemsCategory.value?.id !== category.id) {
        itemsDrawerLoading.value = true;
        router.get(
            categoriesBasePath.value,
            {
                sort: sort.value,
                per_page: perPage.value,
                search: search.value || null,
                page: props.categories.meta?.current_page ?? 1,
                items_category_id: category.id,
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
    }
};

const normalizeTaxId = () => {
    form.tax_id = form.tax_id === '' || form.tax_id === null ? null : Number(form.tax_id);
};

const submit = () => {
    if (!validateForm()) {
        return;
    }

    normalizeTaxId();

    if (editingId.value) {
        form.put(`${categoriesBasePath.value}/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
            },
        });

        return;
    }

    form.post(categoriesBasePath.value, {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
        },
    });
};

const applySort = () => {
    router.get(
        categoriesBasePath.value,
        {
            sort: sort.value,
            per_page: perPage.value,
            search: search.value || null,
            page: 1,
            items_category_id: showItemsDrawer.value ? props.filters?.items_category_id ?? undefined : undefined,
        },
        { preserveState: true, replace: true }
    );
};

const toggleCreatedSort = () => {
    sort.value = sort.value === 'newest' ? 'oldest' : 'newest';
    applySort();
};

const toggleNameSort = () => {
    sort.value = sort.value === 'name_asc' ? 'name_desc' : 'name_asc';
    applySort();
};

const toggleItemsSort = () => {
    sort.value = sort.value === 'items_asc' ? 'items_desc' : 'items_asc';
    applySort();
};

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(search, (value) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        router.get(
            categoriesBasePath.value,
            {
                sort: sort.value,
                per_page: perPage.value,
                search: value || null,
                page: 1,
                items_category_id: showItemsDrawer.value ? props.filters?.items_category_id ?? undefined : undefined,
            },
            { preserveState: true, replace: true }
        );
    }, 300);
});

const openItemsDrawer = (category: Category) => {
    showForm.value = false;
    editingId.value = null;
    viewingId.value = null;
    confirmDeleteId.value = null;
    showItemsDrawer.value = true;
    itemsDrawerLoading.value = true;

    router.get(
        categoriesBasePath.value,
        {
            sort: sort.value,
            per_page: perPage.value,
            search: search.value || null,
            page: props.categories.meta?.current_page ?? 1,
            items_category_id: category.id,
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

const closeItemsDrawer = () => {
    showItemsDrawer.value = false;
    itemsDrawerLoading.value = false;
    router.get(
        categoriesBasePath.value,
        {
            sort: sort.value,
            per_page: perPage.value,
            search: search.value || null,
            page: props.categories.meta?.current_page ?? 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const requestDelete = (category: Category, event: MouseEvent) => {
    confirmDeleteId.value = category.id;
    popoverDirections.value[category.id] = 'bottom';

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
            popoverDirections.value[category.id] = 'top';
        }
    });
};

const cancelDelete = () => {
    confirmDeleteId.value = null;
};

const destroyCategory = (category: Category) => {
    deleteForm.delete(`${categoriesBasePath.value}/${category.id}`, {
        preserveScroll: true,
        onStart: () => {
            deletingId.value = category.id;
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

const formatDateTime = (value: string) => {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);
};

const formatTax = (tax: TaxOption | null | undefined) => {
    if (!tax) {
        return 'N/A';
    }

    const rateLabel = tax.type === 'percentage' ? `${tax.rate}%` : `$${tax.rate}`;

    return `${tax.name} (${rateLabel})`;
};
</script>

<template>
    <Head :title="__('Categories')" />
    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-6xl mx-auto w-full p-4 lg:p-0">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Categories') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Group items into clear, reusable categories.') }}</p>
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
                        {{ __('New Category') }}
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-visible">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        {{ __('All Categories') }}
                        <span class="text-slate-400">({{ props.categories_count ?? categoriesList.length }})</span>
                    </div>
                    <div class="w-full sm:w-auto">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="__('Search categories...')"
                            class="h-9 w-full sm:w-[240px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 text-sm text-slate-700 dark:text-slate-200 placeholder:text-slate-400"
                        />
                    </div>
                </div>
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 text-xs font-bold text-slate-500 uppercase tracking-widest grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_220px_120px_180px_140px] gap-3 items-center">
                    <div class="flex items-center gap-2">
                        <span>{{ __('Name') }}</span>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="toggleNameSort"
                            :aria-label="__('Toggle name sort order')"
                        >
                            <ArrowUpDown :size="14" />
                        </button>
                    </div>
                    <div class="flex items-center justify-center">
                        <span>{{ __('Tax') }}</span>
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <span>{{ __('Items') }}</span>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="toggleItemsSort"
                            :aria-label="__('Toggle items count order')"
                        >
                            <ArrowUpDown :size="14" />
                        </button>
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <span>{{ __('Created At') }}</span>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="toggleCreatedSort"
                            :aria-label="__('Toggle created date order')"
                        >
                            <ArrowUpDown :size="14" />
                        </button>
                    </div>
                    <span class="md:text-right">{{ __('Actions') }}</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    <div v-if="categoriesList.length === 0" class="p-6 text-sm text-slate-500">
                        {{ __('No categories have been created yet.') }}
                    </div>
                    <div
                        v-for="category in categoriesList"
                        :key="category.id"
                        class="p-3 md:p-4 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_220px_120px_180px_140px] gap-3 items-start md:items-center"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-base font-bold text-slate-900 dark:text-white capitalize">{{ category.name }}</span>
                            <span
                                class="inline-flex h-2 w-2 rounded-full"
                                :class="category.is_active ? 'bg-emerald-500' : 'bg-red-500'"
                                :aria-label="category.is_active ? __('Active') : __('Inactive')"
                            ></span>
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 flex flex-col items-center text-center">
                            {{ formatTax(category.tax ?? null) }}
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 flex items-center justify-center text-center">
                            <button
                                type="button"
                                class="font-semibold transition-colors"
                                :class="(category.items_count ?? 0) > 0 ? 'text-slate-700 dark:text-slate-200 hover:text-brand-600' : 'text-slate-400 cursor-default'"
                                :disabled="(category.items_count ?? 0) === 0"
                                @click="(category.items_count ?? 0) > 0 && openItemsDrawer(category)"
                            >
                                {{ category.items_count ?? 0 }}
                            </button>
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 flex items-center justify-center text-center">
                            {{ formatDate(category.created_at) }}
                        </div>

                        <div class="flex items-center gap-2 md:justify-end">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                                @click="startView(category)"
                                :aria-label="__('View')"
                            >
                                <Eye :size="14" />
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                                @click="startEdit(category)"
                                :aria-label="__('Edit')"
                            >
                                <Pencil :size="14" />
                            </button>
                            <div class="relative">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 text-red-600 hover:bg-red-50"
                                    @click="requestDelete(category, $event)"
                                    :aria-label="__('Delete')"
                                >
                                    <Trash2 :size="14" />
                                </button>
                                <div
                                    v-if="confirmDeleteId === category.id"
                                    class="absolute right-0 w-64 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl p-5 z-20"
                                    :class="popoverDirections[category.id] === 'top' ? 'bottom-full mb-3' : 'top-full mt-3'"
                                >
                                    <div class="flex flex-col items-center text-center gap-2">
                                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                                            <Trash2 :size="16" />
                                        </div>
                                        <p class="text-xl font-semibold text-slate-800 dark:text-slate-100">{{ __('Delete') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ __('Are you sure you want to delete') }} {{ category.name }}?
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
                                            @click="destroyCategory(category)"
                                        >
                                            <svg v-if="deletingId === category.id" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            <span>{{ __('Delete') }}</span>
                                        </button>
                                    </div>
                                </div>
                                <button
                                    v-if="confirmDeleteId === category.id"
                                    type="button"
                                    class="fixed inset-0 cursor-default z-10"
                                    @click="cancelDelete"
                                ></button>
                            </div>
                        </div>
                    </div>
                </div>
                <Pagination :meta="props.categories.meta" :links="props.categories.links" :query="paginationQuery" />
            </div>
        </div>

        <Drawer
            :open="isDrawerOpen"
            :title="editingId ? __('Edit Category') : isViewMode ? __('View Category') : __('New Category')"
            :subtitle="editingId ? __('Update the category details below.') : isViewMode ? __('Review the category details below.') : __('Enter the category details below.')"
            @close="resetForm"
            @update:open="(value) => !value && resetForm()"
        >
            <template v-if="isViewMode">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Name') }}:</p>
                        <div class="flex items-center gap-2 text-sm text-slate-800 dark:text-slate-100">
                            <span class="font-semibold capitalize">{{ form.name || '-' }}</span>
                            <span
                                class="inline-flex h-2 w-2 rounded-full"
                                :class="form.is_active ? 'bg-emerald-500' : 'bg-red-500'"
                                :aria-label="form.is_active ? __('Active') : __('Inactive')"
                            ></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Applied Tax') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ formatTax((taxOptions.find((tax) => Number(form.tax_id) === tax.id) ?? null)) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Discount') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ form.has_discount
                                ? (form.discount_amount !== null && form.discount_amount !== ''
                                    ? (form.discount_is_percentage ? `${form.discount_amount}%` : `$${form.discount_amount}`)
                                    : 'N/A')
                                : 'N/A' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Created At') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ formatDateTime(categoriesList.find((c) => c.id === viewingId)?.created_at || '') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Last Updated At') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ formatDateTime(categoriesList.find((c) => c.id === viewingId)?.updated_at || '') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-slate-500">{{ __('Items') }}:</p>
                        <div class="mt-2">
                            <div v-if="itemsDrawerLoading" class="text-sm text-slate-500">
                                {{ __('Loading items...') }}
                            </div>
                            <div v-else>
                                <div v-if="viewCategoryItems.length === 0" class="text-sm text-slate-500">
                                    {{ __('No items found for this category.') }}
                                </div>
                                <div v-else class="divide-y divide-slate-100 dark:divide-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800">
                                    <div
                                        v-for="item in viewCategoryItems"
                                        :key="item.id"
                                        class="p-3 flex items-center justify-between gap-3"
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
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                            {{ __('Category Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            :placeholder="__('Enter category name')"
                            class="w-full px-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all"
                            :style="{ '--tw-ring-color': 'var(--brand-primary)' }"
                        />
                        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 font-medium">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-5 w-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            />
                            {{ __('Status') }}
                        </label>
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                            {{ __('Tax (Optional)') }}
                        </label>
                        <SelectInput
                            v-model="form.tax_id"
                            :options="[
                                { value: '', label: __('No tax') },
                                ...taxOptions.map((tax) => ({
                                    value: tax.id,
                                    label: `${tax.name} (${tax.type === 'percentage' ? `${tax.rate}%` : `$${tax.rate}`})`,
                                })),
                            ]"
                            :fetch-url="taxSearchUrl"
                            :search-placeholder="__('Search taxes...')"
                            :button-class="'px-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all'"
                            :list-class="'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'"
                            :option-class="'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
                        />
                        <p v-if="form.errors.tax_id" class="text-xs text-red-500 mt-1">{{ form.errors.tax_id }}</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 font-medium">
                            <input
                                v-model="form.has_discount"
                                type="checkbox"
                                class="h-5 w-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            />
                            {{ __('Has Discount') }}
                        </label>
                    </div>

                    <div v-if="form.has_discount" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                                {{ __('Discount Amount') }}
                            </label>
                            <input
                                v-model="form.discount_amount"
                                type="number"
                                step="0.01"
                                min="0"
                                :placeholder="__('0.00')"
                                class="w-full px-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all appearance-none [-moz-appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                :style="{ '--tw-ring-color': 'var(--brand-primary)' }"
                            />
                            <p v-if="form.errors.discount_amount" class="text-xs text-red-500 mt-1">
                                {{ form.errors.discount_amount }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                                {{ __('Discount Type') }}
                            </label>
                            <SelectInput
                                v-model="form.discount_is_percentage"
                                :options="[
                                    { value: true, label: __('Percentage') },
                                    { value: false, label: __('Fixed Amount') },
                                ]"
                                :button-class="'px-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all'"
                                :list-class="'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'"
                                :option-class="'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
                            />
                        </div>
                    </div>
                </div>
            </template>

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
                        v-if="!isViewMode"
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
                        {{ editingId ? __('Save Category') : __('Add Category') }}
                    </button>
                </div>
            </template>
        </Drawer>

        <Drawer
            :open="showItemsDrawer"
            :title="categoryItemsCategory ? `${categoryItemsCategory.name} ${__('Items')}` : __('Items')"
            :subtitle="categoryItemsCategory ? __('Items in this category.') : __('Category items list.')"
            @close="closeItemsDrawer"
            @update:open="(value) => !value && closeItemsDrawer()"
        >
            <div class="space-y-4">
                <div v-if="itemsDrawerLoading" class="text-sm text-slate-500">
                    {{ __('Loading items...') }}
                </div>
                <div v-else>
                    <div v-if="categoryItems.length === 0" class="text-sm text-slate-500">
                        {{ __('No items found for this category.') }}
                    </div>
                    <div v-else class="divide-y divide-slate-100 dark:divide-slate-800 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <div
                            v-for="item in categoryItems"
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
