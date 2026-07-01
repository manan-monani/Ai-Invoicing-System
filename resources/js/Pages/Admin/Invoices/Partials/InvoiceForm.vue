<script setup lang="ts">
import SelectInput from '@/Components/Common/SelectInput.vue';
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

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

type SelectOption = {
    value: string | number | null;
    label: string;
    disabled?: boolean;
    meta?: Record<string, unknown> | null;
};

type TaxSettings = {
    tax_enabled: boolean;
    tax_mode: 'none' | 'global' | 'item' | 'category' | string;
    default_tax_id: number | null;
};

const props = defineProps<{
    form: any;
    customers: CustomerOption[];
    items: { data: ItemOption[] };
    taxes: { data: TaxOption[] };
    taxSettings: TaxSettings;
    submitLabel: string;
    processingLabel: string;
    balance?: number | null;
    allowCustomerCreate?: boolean;
    customerSearchUrl?: string;
    itemSearchUrl?: string;
    selectedCustomerLabel?: string;
}>();

const emit = defineEmits(['submit']);

const itemOptions = computed<ItemOption[]>(() => props.items?.data || []);
const taxOptions = computed<TaxOption[]>(() => props.taxes?.data || []);
const customerOptions = computed<SelectOption[]>(() =>
    props.customers.map((customer) => ({
        value: customer.id,
        label: `${customer.name} (${customer.email})`,
        meta: customer,
    })),
);
const itemSelectOptions = computed<SelectOption[]>(() =>
    itemOptions.value.map((item) => ({
        value: item.id,
        label: item.name,
        meta: item,
    })),
);

const taxMap = computed(() => new Map(taxOptions.value.map((tax) => [tax.id, tax])));
const isNewCustomer = computed(() => Boolean(props.allowCustomerCreate) && props.form.customer_mode === 'new');
const isAddingLineItem = ref(false);
const discountMode = ref<'amount' | 'percent'>('amount');
const discountInput = ref(String(props.form.discount ?? ''));

const addLineItem = async () => {
    if (isAddingLineItem.value) {
        return;
    }
    isAddingLineItem.value = true;
    props.form.line_items.push({
        item_id: null,
        name: '',
        description: '',
        discount_label: '',
        has_discount: false,
        discount_amount: null,
        discount_is_percentage: false,
        qty: 1,
        unit_price: '',
        tax_id: null,
    });
    await nextTick();
    isAddingLineItem.value = false;
};

const removeLineItem = (index: number) => {
    if (props.form.line_items.length <= 1) return;
    props.form.line_items.splice(index, 1);
};

const resolveLineTaxId = (item?: ItemOption | null) => {
    if (!props.taxSettings.tax_enabled) return null;
    if (props.taxSettings.tax_mode === 'item') return item?.tax_id ?? null;
    if (props.taxSettings.tax_mode === 'category') return item?.category?.tax_id ?? null;
    return null;
};

const resolveLineDiscountData = (line: {
    item_id?: number | string | null;
    has_discount?: boolean;
    discount_amount?: string | number | null;
    discount_is_percentage?: boolean;
}) => {
    const itemId = line.item_id ? Number(line.item_id) : null;
    if (itemId) {
        const item = itemOptions.value.find((option) => option.id === itemId);
        if (item) {
            return {
                has_discount: Boolean(item.has_discount),
                discount_amount: item.discount_amount ?? null,
                discount_is_percentage: Boolean(item.discount_is_percentage),
            };
        }
    }

    return {
        has_discount: Boolean(line.has_discount),
        discount_amount: line.discount_amount ?? null,
        discount_is_percentage: Boolean(line.discount_is_percentage),
    };
};

const formatDiscountLabel = (item?: ItemOption | null) => {
    if (!item?.has_discount || item.discount_amount === null || item.discount_amount === undefined) {
        return '—';
    }
    const amount = asNumber(item.discount_amount);
    if (amount <= 0) {
        return '—';
    }
    return item.discount_is_percentage ? `${amount}%` : `$${amount.toFixed(2)}`;
};

const resolveDiscountedUnitPrice = (item?: ItemOption | null, basePrice?: number) => {
    if (!item) return '';
    const baseValue = basePrice !== undefined ? basePrice : asNumber(item.unit_price);
    if (!item.has_discount || item.discount_amount === null || item.discount_amount === undefined) {
        return baseValue.toFixed(2);
    }
    const discountValue = asNumber(item.discount_amount);
    if (discountValue <= 0) {
        return baseValue.toFixed(2);
    }
    let discounted = basePrice;
    if (item.discount_is_percentage) {
        const rate = Math.min(Math.max(discountValue, 0), 100);
        discounted = baseValue * (1 - rate / 100);
    } else {
        discounted = baseValue - discountValue;
    }
    return Math.max(discounted, 0).toFixed(2);
};

const handleItemSelect = (index: number, option?: SelectOption | null) => {
    const line = props.form.line_items[index];
    const item = option?.meta as ItemOption | undefined;
    if (!item) return;
    const duplicateIndex = props.form.line_items.findIndex(
        (existing: any, existingIndex: number) => existingIndex !== index && Number(existing.item_id) === item.id,
    );
    if (duplicateIndex !== -1) {
        if (typeof props.form.setError === 'function') {
            props.form.setError(`line_items.${index}.item_id`, 'This item is already added.');
        }
        line.item_id = null;
        line.name = '';
        return;
    }

    line.name = item.name;
    if (!line.description || !String(line.description).trim()) {
        line.description = item.description ?? '';
    }
    line.discount_label = formatDiscountLabel(item);
    line.has_discount = Boolean(item.has_discount);
    line.discount_amount = item.discount_amount ?? null;
    line.discount_is_percentage = Boolean(item.discount_is_percentage);
    line.unit_price = String(item.unit_price ?? '');
    line.tax_id = resolveLineTaxId(item);
    clearLineItemErrors(index, ['item_id', 'name', 'unit_price']);
};

const handleItemCustom = (index: number, label: string) => {
    const line = props.form.line_items[index];
    line.item_id = null;
    line.name = label;
    line.discount_label = '—';
    line.has_discount = false;
    line.discount_amount = null;
    line.discount_is_percentage = false;
    line.unit_price = '';
    line.tax_id = null;
    clearLineItemErrors(index, ['item_id', 'name']);
};

const useExistingCustomer = () => {
    props.form.customer_mode = 'existing';
    props.form.customer_email = '';
    props.form.customer_name = '';
    clearFormErrors(['customer_email', 'customer_name']);
};

const useNewCustomer = () => {
    props.form.customer_mode = 'new';
    props.form.customer_id = '';
    clearFormErrors(['customer_id']);
};

const asNumber = (value: unknown) => {
    const parsed = Number(value);
    return Number.isNaN(parsed) ? 0 : parsed;
};

const lineTotal = (line: any) => {
    const qty = asNumber(line.qty);
    const baseUnitPrice = asNumber(line.unit_price);
    const discount = resolveLineDiscountData(line);

    if (!discount.has_discount || discount.discount_amount === null || discount.discount_amount === undefined) {
        return qty * baseUnitPrice;
    }

    const discountValue = asNumber(discount.discount_amount);
    if (discountValue <= 0) {
        return qty * baseUnitPrice;
    }

    let discountedUnit = baseUnitPrice;
    if (discount.discount_is_percentage) {
        const rate = Math.min(Math.max(discountValue, 0), 100);
        discountedUnit = baseUnitPrice * (1 - rate / 100);
    } else {
        discountedUnit = baseUnitPrice - discountValue;
    }

    return qty * Math.max(discountedUnit, 0);
};

const subtotal = computed(() => {
    return props.form.line_items.reduce((sum: number, line: any) => sum + lineTotal(line), 0);
});

const taxTotal = computed(() => {
    if (!props.taxSettings.tax_enabled || props.taxSettings.tax_mode === 'none') {
        return 0;
    }

    if (props.taxSettings.tax_mode === 'global') {
        const tax = taxMap.value.get(Number(props.taxSettings.default_tax_id));
        if (!tax) return 0;
        if (tax.type === 'fixed') return asNumber(tax.rate);
        return subtotal.value * (asNumber(tax.rate) / 100);
    }

    return props.form.line_items.reduce((sum: number, line: any) => {
        const tax = line.tax_id ? taxMap.value.get(Number(line.tax_id)) : null;
        if (!tax) return sum;
        if (tax.type === 'fixed') {
            return sum + asNumber(tax.rate) * asNumber(line.qty);
        }
        return sum + lineTotal(line) * (asNumber(tax.rate) / 100);
    }, 0);
});

const discountAmount = computed(() => {
    const rawValue = asNumber(discountInput.value);
    if (discountMode.value === 'percent') {
        if (subtotal.value <= 0) return 0;
        const rate = Math.min(Math.max(rawValue, 0), 100);
        return (subtotal.value * rate) / 100;
    }
    return Math.max(rawValue, 0);
});
const discountValue = computed(() => discountAmount.value);
const total = computed(() => Math.max(subtotal.value + taxTotal.value - discountValue.value, 0));

const lineItemError = (index: number, field: string) => {
    return props.form.errors?.[`line_items.${index}.${field}`];
};

const clearLineItemErrors = (index: number, fields: string[]) => {
    const keys = fields.map((field) => `line_items.${index}.${field}`);
    if (typeof props.form.clearErrors === 'function') {
        props.form.clearErrors(...keys);
    }
};

const clearFormErrors = (fields: string[]) => {
    if (typeof props.form.clearErrors === 'function') {
        props.form.clearErrors(...fields);
    }
};

const setDiscountMode = (mode: 'amount' | 'percent') => {
    if (discountMode.value === mode) return;
    const currentAmount = discountAmount.value;
    if (mode === 'percent') {
        const nextRate = subtotal.value > 0 ? (currentAmount / subtotal.value) * 100 : 0;
        discountInput.value = nextRate ? nextRate.toFixed(2) : '';
    } else {
        discountInput.value = currentAmount ? currentAmount.toFixed(2) : '';
    }
    discountMode.value = mode;
};

watch(discountAmount, (value) => {
    props.form.discount = value.toFixed(2);
});

const resolveLineItemLabel = (line: { item_id?: number | string | null; name?: string | null; description?: string | null }) => {
    const itemId = line.item_id ? Number(line.item_id) : null;
    if (itemId) {
        const match = itemOptions.value.find((item) => item.id === itemId);
        return match?.name;
    }
    if (line.name && String(line.name).trim()) {
        return String(line.name);
    }
    return line.description ? String(line.description) : undefined;
};

const lineItemNameError = (index: number) => {
    return lineItemError(index, 'item_id') || lineItemError(index, 'name');
};

const resolveLineDiscountLabel = (line: {
    item_id?: number | string | null;
    discount_label?: string | null;
    has_discount?: boolean;
    discount_amount?: string | number | null;
    discount_is_percentage?: boolean;
}) => {
    if (line.discount_label && String(line.discount_label).trim()) {
        return String(line.discount_label);
    }
    const itemId = line.item_id ? Number(line.item_id) : null;
    if (!itemId) return '—';
    const item = itemOptions.value.find((option) => option.id === itemId);
    return formatDiscountLabel(item);
};
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="space-y-2">
                <div class="flex min-h-[34px] items-end justify-between gap-3">
                    <label class="text-sm leading-tight font-semibold text-slate-700">{{ __('Customer') }}</label>
                    <div v-if="allowCustomerCreate" class="flex items-center gap-2 text-xs font-semibold">
                        <button
                            type="button"
                            class="rounded-lg border px-2.5 py-1"
                            :class="!isNewCustomer ? 'border-brand-600 text-brand-600' : 'border-slate-200 text-slate-500'"
                            @click="useExistingCustomer"
                        >
                            {{ __('Existing') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border px-2.5 py-1"
                            :class="isNewCustomer ? 'border-brand-600 text-brand-600' : 'border-slate-200 text-slate-500'"
                            @click="useNewCustomer"
                        >
                            {{ __('New') }}
                        </button>
                    </div>
                </div>

                <SelectInput
                    v-model="form.customer_id"
                    :options="customerOptions"
                    :placeholder="__('Select a customer')"
                    :fetch-url="customerSearchUrl"
                    :search-placeholder="__('Search customers...')"
                    :selected-label="selectedCustomerLabel || undefined"
                    :disabled="isNewCustomer"
                    @update:model-value="() => clearFormErrors(['customer_id'])"
                />
                <p v-if="form.errors.customer_id" class="text-sm text-red-600">{{ form.errors.customer_id }}</p>

                <div v-if="isNewCustomer" class="pt-2">
                    <label class="text-xs font-semibold text-slate-500"> {{ __('Customer Name') }} <span class="text-red-500">*</span> </label>
                    <input
                        v-model="form.customer_name"
                        type="text"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                        @input="clearFormErrors(['customer_name'])"
                    />
                    <p v-if="form.errors.customer_name" class="text-xs text-red-600">{{ form.errors.customer_name }}</p>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex min-h-[34px] items-end">
                    <label class="text-sm leading-tight font-semibold text-slate-700">{{ __('Status') }}</label>
                </div>
                <SelectInput
                    v-model="form.status"
                    :options="[
                        { value: 'draft', label: __('Draft') },
                        { value: 'sent', label: __('Sent') },
                        { value: 'paid', label: __('Paid'), disabled: true },
                        { value: 'overdue', label: __('Overdue'), disabled: true },
                    ]"
                    @update:model-value="() => clearFormErrors(['status'])"
                />
                <p v-if="form.errors.status" class="text-sm text-red-600">{{ form.errors.status }}</p>

                <div v-if="isNewCustomer" class="pt-2">
                    <label class="text-xs font-semibold text-slate-500"> {{ __('Customer Email') }} <span class="text-red-500">*</span> </label>
                    <input
                        v-model="form.customer_email"
                        type="email"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                        @input="clearFormErrors(['customer_email'])"
                    />
                    <p v-if="form.errors.customer_email" class="text-xs text-red-600">{{ form.errors.customer_email }}</p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm leading-tight font-semibold text-slate-700">{{ __('Issue Date') }}</label>
                <input
                    type="date"
                    v-model="form.issue_date"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm"
                    @input="clearFormErrors(['issue_date'])"
                />
                <p v-if="form.errors.issue_date" class="text-sm text-red-600">{{ form.errors.issue_date }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm leading-tight font-semibold text-slate-700">{{ __('Due Date') }}</label>
                <input
                    type="date"
                    v-model="form.due_date"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm"
                    @input="clearFormErrors(['due_date'])"
                />
                <p v-if="form.errors.due_date" class="text-sm text-red-600">{{ form.errors.due_date }}</p>
            </div>
        </div>

        <div class="space-y-4 rounded-[2rem] border border-slate-200/70 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('Line Items') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('Add at least one item to the invoice.') }}</p>
                </div>
                <button
                    type="button"
                    @click="addLineItem"
                    class="flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white"
                >
                    <Plus class="h-4 w-4" />
                    {{ __('Add Item') }}
                </button>
            </div>

            <p v-if="form.errors.line_items" class="text-sm text-red-600">{{ form.errors.line_items }}</p>

            <div class="space-y-4">
                <div v-for="(line, index) in form.line_items" :key="index" class="grid grid-cols-1 items-start gap-3 lg:grid-cols-12">
                    <div class="space-y-1 lg:col-span-3">
                        <label class="text-xs font-semibold text-slate-500">
                            {{ __('Item Name') }} {{ index + 1 }}
                        </label>
                        <SelectInput
                            v-model="line.item_id"
                            :options="itemSelectOptions"
                            :placeholder="__('Select or type item')"
                            :fetch-url="itemSearchUrl"
                            :search-placeholder="__('Search or type item...')"
                            :selected-label="resolveLineItemLabel(line)"
                            :button-class="'px-3 py-2 text-sm'"
                            :allow-custom="true"
                            @update:model-value="() => clearLineItemErrors(index, ['item_id', 'name'])"
                            @select="(option) => handleItemSelect(index, option)"
                            @custom="(label) => handleItemCustom(index, label)"
                        />
                        <p v-if="lineItemNameError(index)" class="text-xs text-red-600">{{ lineItemNameError(index) }}</p>
                    </div>

                    <div class="space-y-1 lg:col-span-3">
                        <label class="text-xs font-semibold text-slate-500">{{ __('Description') }}</label>
                        <input
                            v-model="line.description"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                            @input="clearLineItemErrors(index, ['item_id', 'description'])"
                        />
                        <p v-if="lineItemError(index, 'description')" class="text-xs text-red-600">{{ lineItemError(index, 'description') }}</p>
                    </div>

                    <div class="space-y-1 lg:col-span-1">
                        <label class="text-xs font-semibold text-slate-500">{{ __('Qty') }}</label>
                        <input
                            v-model="line.qty"
                            type="number"
                            min="1"
                            step="1"
                            inputmode="numeric"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                            @input="clearLineItemErrors(index, ['qty'])"
                        />
                        <p v-if="lineItemError(index, 'qty')" class="text-xs text-red-600">{{ lineItemError(index, 'qty') }}</p>
                    </div>

                    <div class="space-y-1 lg:col-span-2">
                        <label class="text-xs font-semibold text-slate-500">{{ __('Unit Price') }}</label>
                        <input
                            v-model="line.unit_price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                            :disabled="Boolean(line.item_id)"
                            :class="Boolean(line.item_id) ? 'bg-slate-50 text-slate-500' : ''"
                            @input="clearLineItemErrors(index, ['unit_price'])"
                        />
                        <p v-if="lineItemError(index, 'unit_price')" class="text-xs text-red-600">{{ lineItemError(index, 'unit_price') }}</p>
                    </div>

                    <div class="space-y-1 lg:col-span-1">
                        <label class="text-xs font-semibold text-slate-500">{{ __('Discount') }}</label>
                        <div class="py-2 text-sm font-semibold text-slate-700">{{ resolveLineDiscountLabel(line) }}</div>
                    </div>

                    <div class="space-y-1 lg:col-span-1">
                        <label class="text-xs font-semibold text-slate-500">{{ __('Total') }}</label>
                        <div class="py-2 text-sm font-semibold text-slate-800">{{ lineTotal(line).toFixed(2) }}</div>
                    </div>

                    <div class="flex justify-end pt-5 lg:col-span-1">
                        <button
                            type="button"
                            @click="removeLineItem(index)"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:border-rose-200 hover:text-rose-500"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-2 lg:col-span-2">
                <label class="text-sm font-semibold text-slate-700">{{ __('Notes') }}</label>
                <textarea v-model="form.notes" rows="4" class="w-full rounded-2xl border border-slate-200 p-4 text-sm"></textarea>
                <p v-if="form.errors.notes" class="text-sm text-red-600">{{ form.errors.notes }}</p>
            </div>

            <div class="space-y-4 rounded-[2rem] border border-slate-200/70 bg-white p-5">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-700">{{ __('Discount') }}</label>
                    <div class="flex items-center gap-2">
                        <input
                            v-model="discountInput"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm"
                            @input="clearFormErrors(['discount'])"
                        />
                        <select
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600"
                            :value="discountMode"
                            @change="setDiscountMode(($event.target as HTMLSelectElement).value as 'amount' | 'percent')"
                        >
                            <option value="amount">{{ __('Amount') }}</option>
                            <option value="percent">%</option>
                        </select>
                    </div>
                    <p v-if="form.errors.discount" class="text-sm text-red-600">{{ form.errors.discount }}</p>
                </div>

                <div class="space-y-2 text-sm text-slate-600">
                    <div class="flex justify-between">
                        <span>{{ __('Subtotal') }}</span>
                        <span class="font-semibold text-slate-900">{{ subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>{{ __('Tax Total') }}</span>
                        <span class="font-semibold text-slate-900">{{ taxTotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>{{ __('Discount') }}</span>
                        <span class="font-semibold text-slate-900">-{{ discountValue.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-bold text-slate-900">
                        <span>{{ __('Total') }}</span>
                        <span>{{ total.toFixed(2) }}</span>
                    </div>
                    <div v-if="balance !== null && balance !== undefined" class="flex justify-between text-sm font-semibold text-slate-700">
                        <span>{{ __('Balance') }}</span>
                        <span>{{ Number(balance).toFixed(2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button
                type="submit"
                class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white transition-colors hover:bg-brand-700 disabled:opacity-60"
                :disabled="form.processing"
            >
                {{ form.processing ? processingLabel : submitLabel }}
            </button>
        </div>
    </form>
</template>
