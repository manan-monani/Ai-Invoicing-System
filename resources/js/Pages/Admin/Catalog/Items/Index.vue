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

type CategoryOption = {
    id: number;
    name: string;
};

type Item = {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    unit: string;
    category_id: number;
    tax_id: number | null;
    unit_price: string | number;
    cost_price: string | number;
    has_discount: boolean;
    discount_amount: string | number | null;
    discount_is_percentage: boolean;
    is_active: boolean;
    category?: CategoryOption | null;
    tax?: TaxOption | null;
    created_at: string;
    updated_at: string;
};

const props = defineProps<{
    items: {
        data: Item[];
        links: { url: string | null; label: string; active: boolean }[];
        meta?: { current_page?: number; last_page?: number; per_page?: number; path?: string };
    };
    items_count?: number;
    categories: {
        data: CategoryOption[];
    };
    taxes: {
        data: TaxOption[];
    };
    filters?: {
        sort?: string;
        per_page?: number;
        search?: string;
    };
}>();

const itemsList = computed<Item[]>(() => props.items.data);
const categoryOptions = computed<CategoryOption[]>(() => props.categories.data || []);
const taxOptions = computed<TaxOption[]>(() => props.taxes.data || []);
const page = usePage();
const isEmployee = computed(() => {
    const rawType = (page.props as any).auth?.user?.type;
    return rawType === 'employee' || rawType?.value === 'employee' || rawType?.name === 'employee';
});
const basePath = computed(() => (isEmployee.value ? '/admin/employee' : '/admin'));
const itemsBasePath = computed(() => `${basePath.value}/catalog/items`);
const lookupsBasePath = computed(() => `${basePath.value}/lookups`);
const sort = ref(props.filters?.sort || 'newest');
const search = ref(props.filters?.search ?? '');
const perPage = computed(() => Number(props.filters?.per_page ?? props.items.meta?.per_page ?? 10));
const paginationQuery = computed(() => ({ sort: sort.value, search: search.value || null }));
const isDrawerOpen = computed(() => showForm.value || Boolean(editingId.value) || Boolean(viewingId.value));
const isViewMode = computed(() => Boolean(viewingId.value));

const editingId = ref<number | null>(null);
const viewingId = ref<number | null>(null);
const showForm = ref(false);
const confirmDeleteId = ref<number | null>(null);
const popoverDirections = ref<Record<number, 'top' | 'bottom'>>({});
const deletingId = ref<number | null>(null);

const form = useForm({
    name: '',
    sku: '',
    description: '',
    unit: 'pcs',
    category_id: '' as number | '',
    tax_id: '' as number | '' | null,
    unit_price: '',
    cost_price: '',
    has_discount: false,
    discount_amount: '' as number | '' | null,
    discount_is_percentage: false,
    is_active: true,
});

const categorySearchUrl = computed(() => `${lookupsBasePath.value}/categories`);
const taxSearchUrl = computed(() => `${lookupsBasePath.value}/taxes`);

const deleteForm = useForm({});

const validateForm = () => {
    form.clearErrors();
    let isValid = true;

    if (!form.name || !form.name.trim()) {
        form.setError('name', 'Item name is required.');
        isValid = false;
    }

    if (!form.category_id) {
        form.setError('category_id', 'Category is required.');
        isValid = false;
    }

    if (!form.unit) {
        form.setError('unit', 'Unit is required.');
        isValid = false;
    }

    const unitPriceValue = Number(form.unit_price);
    if (form.unit_price === '' || Number.isNaN(unitPriceValue)) {
        form.setError('unit_price', 'Unit price is required.');
        isValid = false;
    } else if (unitPriceValue < 0) {
        form.setError('unit_price', 'Unit price must be zero or higher.');
        isValid = false;
    }

    const costPriceValue = Number(form.cost_price);
    if (form.cost_price === '' || Number.isNaN(costPriceValue)) {
        form.setError('cost_price', 'Cost price is required.');
        isValid = false;
    } else if (costPriceValue < 0) {
        form.setError('cost_price', 'Cost price must be zero or higher.');
        isValid = false;
    }

    if (form.has_discount) {
        const discountValue = Number(form.discount_amount);
        if (form.discount_amount === '' || Number.isNaN(discountValue)) {
            form.setError('discount_amount', 'Discount amount is required.');
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
    form.category_id = '';
    form.tax_id = '';
    form.discount_amount = '';
    form.has_discount = false;
    form.discount_is_percentage = false;
    form.is_active = true;
    form.unit = 'pcs';
    editingId.value = null;
    viewingId.value = null;
    showForm.value = false;
};

const startEdit = (item: Item) => {
    form.clearErrors();
    editingId.value = item.id;
    viewingId.value = null;
    showForm.value = true;
    confirmDeleteId.value = null;
    form.name = item.name;
    form.sku = item.sku ?? '';
    form.description = item.description ?? '';
    form.unit = item.unit ?? 'pcs';
    form.category_id = item.category_id;
    form.tax_id = item.tax_id ?? '';
    form.unit_price = String(item.unit_price);
    form.cost_price = String(item.cost_price ?? '');
    form.has_discount = item.has_discount;
    form.discount_amount = item.discount_amount ?? '';
    form.discount_is_percentage = item.discount_is_percentage;
    form.is_active = item.is_active;
};

const startView = (item: Item) => {
    form.clearErrors();
    viewingId.value = item.id;
    editingId.value = null;
    showForm.value = false;
    confirmDeleteId.value = null;
    form.name = item.name;
    form.sku = item.sku ?? '';
    form.description = item.description ?? '';
    form.unit = item.unit ?? 'pcs';
    form.category_id = item.category_id;
    form.tax_id = item.tax_id ?? '';
    form.unit_price = String(item.unit_price);
    form.cost_price = String(item.cost_price ?? '');
    form.has_discount = item.has_discount;
    form.discount_amount = item.discount_amount ?? '';
    form.discount_is_percentage = item.discount_is_percentage;
    form.is_active = item.is_active;
};

const normalizePayload = () => {
    form.category_id = form.category_id === '' ? ('' as any) : Number(form.category_id);
    form.tax_id = form.tax_id === '' || form.tax_id === null ? null : Number(form.tax_id);
    form.discount_amount = form.discount_amount === '' || form.discount_amount === null ? null : Number(form.discount_amount);
    form.cost_price = form.cost_price === '' ? ('' as any) : Number(form.cost_price);
};

const submit = () => {
    if (!validateForm()) {
        return;
    }

    normalizePayload();

    if (editingId.value) {
        form.put(`${itemsBasePath.value}/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
            },
        });

        return;
    }

    form.post(itemsBasePath.value, {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
        },
    });
};

const applySort = () => {
    router.get(itemsBasePath.value, { sort: sort.value, per_page: perPage.value, search: search.value || null, page: 1 }, { preserveState: true, replace: true });
};

const toggleCreatedSort = () => {
    sort.value = sort.value === 'newest' ? 'oldest' : 'newest';
    applySort();
};

const toggleNameSort = () => {
    sort.value = sort.value === 'name_asc' ? 'name_desc' : 'name_asc';
    applySort();
};

const togglePriceSort = () => {
    sort.value = sort.value === 'price_asc' ? 'price_desc' : 'price_asc';
    applySort();
};

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(search, (value) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        router.get(
            itemsBasePath.value,
            { sort: sort.value, per_page: perPage.value, search: value || null, page: 1 },
            { preserveState: true, replace: true }
        );
    }, 300);
});

const requestDelete = (item: Item, event: MouseEvent) => {
    confirmDeleteId.value = item.id;
    popoverDirections.value[item.id] = 'bottom';

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
            popoverDirections.value[item.id] = 'top';
        }
    });
};

const cancelDelete = () => {
    confirmDeleteId.value = null;
};

const destroyItem = (item: Item) => {
    deleteForm.delete(`${itemsBasePath.value}/${item.id}`, {
        preserveScroll: true,
        onStart: () => {
            deletingId.value = item.id;
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

const formatDiscount = (item: Item) => {
    if (!item.has_discount || item.discount_amount === null) {
        return 'N/A';
    }

    return item.discount_is_percentage ? `${item.discount_amount}%` : `$${item.discount_amount}`;
};
</script>

<template>
    <Head :title="__('Items')" />
    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-6xl mx-auto w-full p-4 lg:p-0">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Items') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Manage catalog items and default pricing.') }}</p>
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
                        {{ __('New Item') }}
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-visible">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        {{ __('All Items') }}
                        <span class="text-slate-400">({{ props.items_count ?? itemsList.length }})</span>
                    </div>
                    <div class="w-full sm:w-auto">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="__('Search items...')"
                            class="h-9 w-full sm:w-[240px] rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 text-sm text-slate-700 dark:text-slate-200 placeholder:text-slate-400"
                        />
                    </div>
                </div>
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 text-xs font-bold text-slate-500 uppercase tracking-widest grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_200px_140px_180px_140px] gap-3 items-center">
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
                    <span class="text-center">{{ __('Category') }}</span>
                    <div class="flex items-center justify-center gap-2">
                        <span>{{ __('Unit Price') }}</span>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="togglePriceSort"
                            :aria-label="__('Toggle unit price order')"
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
                    <div v-if="itemsList.length === 0" class="p-6 text-sm text-slate-500">
                        {{ __('No items have been created yet.') }}
                    </div>
                    <div
                        v-for="item in itemsList"
                        :key="item.id"
                        class="p-3 md:p-4 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_200px_140px_180px_140px] gap-3 items-start md:items-center"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-base font-bold text-slate-900 dark:text-white capitalize">{{ item.name }}</span>
                            <span
                                class="inline-flex h-2 w-2 rounded-full"
                                :class="item.is_active ? 'bg-emerald-500' : 'bg-red-500'"
                                :aria-label="item.is_active ? __('Active') : __('Inactive')"
                            ></span>
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 text-center">
                            {{ item.category?.name || '—' }}
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 text-center">
                            ${{ item.unit_price }}
                        </div>

                        <div class="text-sm text-slate-500 dark:text-slate-400 text-center flex items-center justify-center">
                            {{ formatDate(item.created_at) }}
                        </div>

                        <div class="flex items-center gap-2 md:justify-end">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                                @click="startView(item)"
                                :aria-label="__('View')"
                            >
                                <Eye :size="14" />
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800"
                                @click="startEdit(item)"
                                :aria-label="__('Edit')"
                            >
                                <Pencil :size="14" />
                            </button>
                            <div class="relative">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 text-red-600 hover:bg-red-50"
                                    @click="requestDelete(item, $event)"
                                    :aria-label="__('Delete')"
                                >
                                    <Trash2 :size="14" />
                                </button>
                                <div
                                    v-if="confirmDeleteId === item.id"
                                    class="absolute right-0 w-64 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl p-5 z-20"
                                    :class="popoverDirections[item.id] === 'top' ? 'bottom-full mb-3' : 'top-full mt-3'"
                                >
                                    <div class="flex flex-col items-center text-center gap-2">
                                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                                            <Trash2 :size="16" />
                                        </div>
                                        <p class="text-xl font-semibold text-slate-800 dark:text-slate-100">{{ __('Delete') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ __('Are you sure you want to delete') }} {{ item.name }}?
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
                                            @click="destroyItem(item)"
                                        >
                                            <svg v-if="deletingId === item.id" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            <span>{{ __('Delete') }}</span>
                                        </button>
                                    </div>
                                </div>
                                <button
                                    v-if="confirmDeleteId === item.id"
                                    type="button"
                                    class="fixed inset-0 cursor-default z-10"
                                    @click="cancelDelete"
                                ></button>
                            </div>
                        </div>
                    </div>
                </div>
                <Pagination :meta="props.items.meta" :links="props.items.links" :query="paginationQuery" />
            </div>
        </div>

        <Drawer
            :open="isDrawerOpen"
            :title="editingId ? __('Edit Item') : isViewMode ? __('View Item') : __('New Item')"
            :subtitle="editingId ? __('Update the item details below.') : isViewMode ? __('Review the item details below.') : __('Enter the item details below.')"
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
                        <p class="text-sm font-semibold text-slate-500">{{ __('SKU') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ form.sku || '—' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Category') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ categoryOptions.find((category) => category.id === Number(form.category_id))?.name || '-' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Unit') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ form.unit || 'pcs' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Unit Price') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            ${{ form.unit_price || '0.00' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Cost Price') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            ${{ form.cost_price || '0.00' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Discount') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ form.has_discount && form.discount_amount !== null && form.discount_amount !== ''
                                ? (form.discount_is_percentage ? `${form.discount_amount}%` : `$${form.discount_amount}`)
                                : 'N/A' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Applied Tax') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ formatTax((taxOptions.find((tax) => tax.id === Number(form.tax_id)) ?? null)) }}
                        </p>
                    </div>

                    <div class="flex items-start gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Description') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200 break-words">
                            {{ form.description || '—' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Created At') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ formatDateTime(itemsList.find((item) => item.id === viewingId)?.created_at || '') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-slate-500">{{ __('Last Updated At') }}:</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ formatDateTime(itemsList.find((item) => item.id === viewingId)?.updated_at || '') }}
                        </p>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                            {{ __('Item Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            :placeholder="__('Enter item name')"
                            class="w-full px-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all"
                            :style="{ '--tw-ring-color': 'var(--brand-primary)' }"
                        />
                        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                            {{ __('SKU') }}
                        </label>
                        <input
                            v-model="form.sku"
                            type="text"
                            :placeholder="__('SKU-1001')"
                            class="w-full px-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all"
                            :style="{ '--tw-ring-color': 'var(--brand-primary)' }"
                        />
                        <p v-if="form.errors.sku" class="text-xs text-red-500 mt-1">{{ form.errors.sku }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                        {{ __('Description') }}
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        :placeholder="__('Describe the item...')"
                        class="w-full p-5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all"
                        :style="{ '--tw-ring-color': 'var(--brand-primary)' }"
                    ></textarea>
                    <p v-if="form.errors.description" class="text-xs text-red-500 mt-1">{{ form.errors.description }}</p>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                        {{ __('Category') }} <span class="text-red-500">*</span>
                    </label>
                    <SelectInput
                        v-model="form.category_id"
                        :placeholder="__('Select category')"
                        :options="categoryOptions.map((category) => ({ value: category.id, label: category.name }))"
                        :fetch-url="categorySearchUrl"
                        :search-placeholder="__('Search categories...')"
                        :button-class="'px-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all'"
                        :list-class="'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'"
                        :option-class="'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
                    />
                    <p v-if="form.errors.category_id" class="text-xs text-red-500 mt-1">{{ form.errors.category_id }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                            {{ __('Unit') }} <span class="text-red-500">*</span>
                        </label>
                        <SelectInput
                            v-model="form.unit"
                            :options="[
                                { value: 'pcs', label: __('Pcs') },
                                { value: 'hour', label: __('Hour') },
                                { value: 'kg', label: __('Kg') },
                            ]"
                            :button-class="'px-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all'"
                            :list-class="'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'"
                            :option-class="'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
                        />
                        <p v-if="form.errors.unit" class="text-xs text-red-500 mt-1">{{ form.errors.unit }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                            {{ __('Unit Price') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.unit_price"
                            type="number"
                            step="0.01"
                            min="0"
                            :placeholder="__('0.00')"
                            class="w-full px-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all appearance-none [-moz-appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                            :style="{ '--tw-ring-color': 'var(--brand-primary)' }"
                        />
                        <p v-if="form.errors.unit_price" class="text-xs text-red-500 mt-1">{{ form.errors.unit_price }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                            {{ __('Cost Price') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.cost_price"
                            type="number"
                            step="0.01"
                            min="0"
                            :placeholder="__('0.00')"
                            class="w-full px-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all appearance-none [-moz-appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                            :style="{ '--tw-ring-color': 'var(--brand-primary)' }"
                        />
                        <p v-if="form.errors.cost_price" class="text-xs text-red-500 mt-1">{{ form.errors.cost_price }}</p>
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 font-medium">
                    <input v-model="form.has_discount" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                    {{ __('Has Discount') }}
                </label>

                <div v-if="form.has_discount" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-400 tracking-wide ps-1 mb-2 block text-start">
                            {{ __('Discount Amount') }} <span class="text-red-500">*</span>
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
                        <p v-if="form.errors.discount_amount" class="text-xs text-red-500 mt-1">{{ form.errors.discount_amount }}</p>
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

                <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 font-medium">
                    <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                    {{ __('Status') }}
                </label>
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
                        {{ editingId ? __('Save Item') : __('Add Item') }}
                    </button>
                </div>
            </template>
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
