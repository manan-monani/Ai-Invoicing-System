<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Drawer from '@/Components/Common/Drawer.vue';
import Pagination from '@/Components/Common/Pagination.vue';
import SelectInput from '@/Components/Common/SelectInput.vue';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { ArrowUpDown, Check, Pencil, Plus, Trash2, X } from 'lucide-vue-next';
import { index, store, update, destroy } from '@/routes/admin/system/payment-methods';

type PaymentMethod = {
    id: number;
    name: string;
    is_default: boolean;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};

const props = defineProps<{
    payment_methods: {
        data: PaymentMethod[];
        links: { url: string | null; label: string; active: boolean }[];
        meta?: { current_page?: number; last_page?: number; per_page?: number; path?: string };
    };
    payment_methods_count?: number;
    filters?: {
        sort?: string;
        per_page?: number;
        search?: string;
    };
}>();

const paymentMethods = computed<PaymentMethod[]>(() => props.payment_methods.data);
const sort = ref(props.filters?.sort || 'newest');
const search = ref(props.filters?.search ?? '');
const perPage = computed(() => Number(props.filters?.per_page ?? props.payment_methods.meta?.per_page ?? 10));
const paginationQuery = computed(() => ({
    sort: sort.value,
    search: search.value || null,
}));

const showForm = ref(false);
const editingId = ref<number | null>(null);
const confirmDeleteId = ref<number | null>(null);
const deletingId = ref<number | null>(null);

const form = useForm({
    name: '',
    is_default: false,
    is_active: true,
});

const sortOptions = [
    { value: 'newest', label: 'Newest' },
    { value: 'oldest', label: 'Oldest' },
    { value: 'name_asc', label: 'Name (A-Z)' },
    { value: 'name_desc', label: 'Name (Z-A)' },
];

const validateForm = () => {
    form.clearErrors();
    let isValid = true;

    if (!form.name || !form.name.trim()) {
        form.setError('name', 'Payment method name is required.');
        isValid = false;
    }

    return isValid;
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.is_active = true;
    form.is_default = false;
    editingId.value = null;
    showForm.value = false;
};

const startCreate = () => {
    resetForm();
    showForm.value = true;
};

const startEdit = (paymentMethod: PaymentMethod) => {
    form.clearErrors();
    editingId.value = paymentMethod.id;
    showForm.value = true;
    confirmDeleteId.value = null;
    form.name = paymentMethod.name;
    form.is_default = paymentMethod.is_default;
    form.is_active = paymentMethod.is_active;
};

const submit = () => {
    if (!validateForm()) {
        return;
    }

    if (editingId.value) {
        form.put(update.url(editingId.value), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
            },
        });
        return;
    }

    form.post(store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
        },
    });
};

const confirmDelete = (paymentMethod: PaymentMethod) => {
    confirmDeleteId.value = paymentMethod.id;
};

const handleDelete = () => {
    if (!confirmDeleteId.value) {
        return;
    }

    deletingId.value = confirmDeleteId.value;
    router.delete(destroy.url(confirmDeleteId.value), {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
            confirmDeleteId.value = null;
        },
    });
};

const applyFilters = () => {
    router.get(
        index.url(),
        {
            sort: sort.value,
            per_page: perPage.value,
            search: search.value || null,
            page: 1,
        },
        { preserveState: true, replace: true }
    );
};

watch(sort, () => {
    applyFilters();
});

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(search, (value) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        router.get(
            index.url(),
            {
                sort: sort.value,
                per_page: perPage.value,
                search: value || null,
                page: 1,
            },
            { preserveState: true, replace: true }
        );
    }, 300);
});

watch(
    () => form.is_default,
    (value) => {
        if (value) {
            form.is_active = true;
        }
    }
);

const formatDate = (value: string) => {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(date);
};
</script>

<template>
    <Head title="Payment Methods" />

    <AdminLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="flex flex-col gap-2">
                <h1 class="text-xl font-extrabold text-slate-900">{{ __('Payment Methods') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Manage payment options used for invoice payments.') }}</p>
            </div>

            <div class="bg-white border border-slate-200/70 rounded-[2rem] shadow-sm">
                <div class="p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                            <ArrowUpDown class="w-4 h-4" />
                            {{ __('Sort') }}
                        </div>
                        <SelectInput
                            v-model="sort"
                            :options="sortOptions"
                            :button-class="'h-9 px-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600'"
                            :list-class="'rounded-xl border border-slate-200 bg-white'"
                            :option-class="'text-slate-600 hover:bg-slate-50'"
                            :wrapper-class="'w-auto'"
                        />
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="__('Search payment methods...')"
                            class="h-9 w-full sm:w-[240px] rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400"
                        />
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-bold shadow-sm hover:bg-brand-700"
                            @click="startCreate"
                        >
                            <Plus class="w-4 h-4" />
                            {{ __('Add Method') }}
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-widest grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_120px_120px_160px_120px] gap-3 items-center">
                    <div class="flex items-center gap-2">
                        <span>{{ __('Name') }}</span>
                    </div>
                    <div class="flex items-center justify-center">
                        <span>{{ __('Default') }}</span>
                    </div>
                    <div class="flex items-center justify-center">
                        <span>{{ __('Status') }}</span>
                    </div>
                    <div class="flex items-center justify-center">
                        <span>{{ __('Created') }}</span>
                    </div>
                    <span class="md:text-right">{{ __('Actions') }}</span>
                </div>

                <div class="divide-y divide-slate-100">
                    <div v-if="paymentMethods.length === 0" class="p-6 text-sm text-slate-500">
                        {{ __('No payment methods have been created yet.') }}
                    </div>
                    <div
                        v-for="method in paymentMethods"
                        :key="method.id"
                        class="p-4 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_120px_120px_160px_120px] gap-3 items-start md:items-center"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-base font-bold text-slate-900">{{ method.name }}</span>
                        </div>
                        <div class="flex items-center justify-center">
                            <span
                                v-if="method.is_default"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600"
                            >
                                <Check class="w-3 h-3" />
                                {{ __('Default') }}
                            </span>
                            <span v-else class="text-xs text-slate-400">-</span>
                        </div>
                        <div class="flex items-center justify-center">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-semibold"
                                :class="method.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'"
                            >
                                {{ method.is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </div>
                        <div class="text-sm text-slate-500 flex items-center justify-center text-center">
                            {{ formatDate(method.created_at) }}
                        </div>
                        <div class="flex items-center gap-2 md:justify-end">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50"
                                @click="startEdit(method)"
                                :aria-label="__('Edit')"
                            >
                                <Pencil :size="14" />
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 text-rose-500 hover:bg-rose-50"
                                @click="confirmDelete(method)"
                                :aria-label="__('Delete')"
                            >
                                <Trash2 :size="14" />
                            </button>
                        </div>
                    </div>
                </div>

                <Pagination :meta="props.payment_methods.meta" :links="props.payment_methods.links" :query="paginationQuery" />
            </div>
        </div>

        <Drawer
            :open="showForm"
            max-width="max-w-md"
            :title="editingId ? __('Edit Payment Method') : __('New Payment Method')"
            :subtitle="editingId ? __('Update payment method details.') : __('Create a new payment option.')"
            @close="resetForm"
            @update:open="(value: boolean) => !value && resetForm()"
        >
            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <label class="text-sm font-bold text-slate-700" for="payment_method_name">{{ __('Name') }}</label>
                    <input
                        id="payment_method_name"
                        v-model="form.name"
                        type="text"
                        class="mt-2 w-full px-3 py-2 text-sm border border-slate-200 rounded-xl"
                        :placeholder="__('Payment method name')"
                    />
                    <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="flex flex-col gap-4">
                    <label class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-slate-700">{{ __('Default Method') }}</div>
                            <p class="text-xs text-slate-500">{{ __('Use this as the default payment method.') }}</p>
                        </div>
                        <input v-model="form.is_default" type="checkbox" class="h-5 w-5 rounded border-slate-300" />
                    </label>
                    <label class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-slate-700">{{ __('Status') }}</div>
                            <p class="text-xs text-slate-500">{{ __('Toggle availability for new payments.') }}</p>
                        </div>
                        <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded border-slate-300" :disabled="form.is_default" />
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" class="px-4 py-2 text-sm font-bold rounded-xl border border-slate-200 text-slate-500" @click="resetForm">
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-bold rounded-xl bg-brand-600 text-white flex items-center gap-2"
                        :disabled="form.processing"
                    >
                        <Plus v-if="!editingId" class="w-4 h-4" />
                        <Check v-else class="w-4 h-4" />
                        {{ form.processing ? __('Saving...') : editingId ? __('Save Changes') : __('Create Method') }}
                    </button>
                </div>
            </form>
        </Drawer>

        <ConfirmationModal
            :show="confirmDeleteId !== null"
            title="Delete payment method"
            message="This will remove the payment method and select another default if needed."
            confirm-text="Delete"
            cancel-text="Cancel"
            :processing="Boolean(deletingId)"
            type="danger"
            @close="confirmDeleteId = null"
            @confirm="handleDelete"
        />
    </AdminLayout>
</template>
