<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InvoiceForm from './Partials/InvoiceForm.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type CustomerOption = {
    id: number;
    name: string;
    email: string;
};

type TaxOption = {
    id: number;
    name: string;
    rate: string | number;
    type: 'percentage' | 'fixed';
};

type CategoryOption = {
    id: number;
    name: string;
    tax_id?: number | null;
    tax?: TaxOption | null;
};

type ItemOption = {
    id: number;
    name: string;
    description?: string | null;
    unit: string;
    unit_price: string | number;
    has_discount?: boolean;
    discount_amount?: string | number | null;
    discount_is_percentage?: boolean;
    tax_id?: number | null;
    tax?: TaxOption | null;
    category_id?: number | null;
    category?: CategoryOption | null;
};

type TaxSettings = {
    tax_enabled: boolean;
    tax_mode: 'none' | 'global' | 'item' | 'category' | string;
    default_tax_id: number | null;
};

const props = defineProps<{
    customers: CustomerOption[];
    items: { data: ItemOption[] };
    taxes: { data: TaxOption[] };
    tax_settings: TaxSettings;
}>();

const page = usePage();
const isEmployee = computed(() => {
    const rawType = (page.props as any).auth?.user?.type;
    return rawType === 'employee' || rawType?.value === 'employee' || rawType?.name === 'employee';
});
const basePath = computed(() => (isEmployee.value ? '/admin/employee' : '/admin'));
const invoicesBasePath = computed(() => `${basePath.value}/invoices`);
const lookupsBasePath = computed(() => `${basePath.value}/lookups`);

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    customer_mode: 'existing',
    customer_id: '' as number | '',
    customer_name: '',
    customer_email: '',
    issue_date: today,
    due_date: '',
    status: 'draft',
    notes: '',
    discount: '',
    line_items: [
        {
            item_id: null as number | null,
            name: '',
            description: '',
            qty: 1,
            unit_price: '',
            tax_id: null as number | null,
        },
    ],
});

const customerSearchUrl = computed(() => `${lookupsBasePath.value}/customers`);
const itemSearchUrl = computed(() => `${lookupsBasePath.value}/items`);

const validateForm = () => {
    form.clearErrors();
    let isValid = true;

    if (form.customer_mode === 'existing' && !form.customer_id) {
        form.setError('customer_id', 'Customer is required.');
        isValid = false;
    }
    if (form.customer_mode === 'new') {
        if (!form.customer_name || !form.customer_name.trim()) {
            form.setError('customer_name', 'Customer name is required.');
            isValid = false;
        }
        if (!form.customer_email || !form.customer_email.trim()) {
            form.setError('customer_email', 'Customer email is required.');
            isValid = false;
        }
    }

    if (!form.issue_date) {
        form.setError('issue_date', 'Issue date is required.');
        isValid = false;
    }

    if (!form.due_date) {
        form.setError('due_date', 'Due date is required.');
        isValid = false;
    }

    if (!form.line_items.length) {
        form.setError('line_items', 'At least one line item is required.');
        isValid = false;
    }

    form.line_items.forEach((line, index) => {
        const hasItem = Boolean(line.item_id);
        const hasName = Boolean(line.name && String(line.name).trim());
        if (!hasItem && !hasName) {
            form.setError(`line_items.${index}.item_id`, 'Item or name is required.');
            form.setError(`line_items.${index}.name`, 'Item or name is required.');
            isValid = false;
        }

        const qtyValue = Number(line.qty);
        if (line.qty === '' || Number.isNaN(qtyValue) || !Number.isInteger(qtyValue)) {
            form.setError(`line_items.${index}.qty`, 'Quantity must be a whole number.');
            isValid = false;
        } else if (qtyValue < 1) {
            form.setError(`line_items.${index}.qty`, 'Quantity must be at least 1.');
            isValid = false;
        }

        const unitPriceValue = Number(line.unit_price);
        if (line.unit_price === '' || Number.isNaN(unitPriceValue) || unitPriceValue < 0) {
            form.setError(`line_items.${index}.unit_price`, 'Unit price must be zero or higher.');
            isValid = false;
        }
    });

    return isValid;
};

const submit = () => {
    if (!validateForm()) return;

    form.post(invoicesBasePath.value, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Invoice" />

    <AdminLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-slate-500">
                        <Link :href="invoicesBasePath" class="hover:text-brand-600">{{ __('Invoices') }}</Link>
                        <span class="mx-2">/</span>
                        <span class="text-slate-700">{{ __('Create') }}</span>
                    </div>
                    <h1 class="text-xl font-extrabold text-slate-900">{{ __('Create Invoice') }}</h1>
                    <p class="text-sm text-slate-500">{{ __('Create a new invoice with line items and totals.') }}</p>
                </div>
            </div>

            <InvoiceForm
                :form="form"
                :customers="customers"
                :items="items"
                :taxes="taxes"
                :tax-settings="tax_settings"
                :customer-search-url="customerSearchUrl"
                :item-search-url="itemSearchUrl"
                :allow-customer-create="true"
                :submit-label="__('Create Invoice')"
                :processing-label="__('Saving...')"
                @submit="submit"
            />
        </div>
    </AdminLayout>
</template>
