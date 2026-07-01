<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { Calendar, FileText, Plus, Printer, Trash2 } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import SelectInput from '@/Components/Common/SelectInput.vue';

type InvoiceDetail = {
        id: number;
        readable_id?: string;
        customer: { id: number; name: string; email: string } | null;
        issue_date: string;
        due_date: string;
        status: string;
        notes: string | null;
        discount: string | number;
        subtotal: string | number;
        tax_total: string | number;
        total: string | number;
        balance: string | number;
        line_items?: Array<{
            id: number;
            item_id: number | null;
            name?: string | null;
            description: string | null;
            qty: string | number;
            unit_price: string | number;
            line_total: string | number;
            tax?: { id: number; name: string; type: string; rate: string | number } | null;
            item?: { id: number; name: string } | null;
        }>;
        payments?: Array<{
            id: number;
            amount: string | number;
            paid_at: string;
            notes: string | null;
            payment_method?: { id: number; name: string } | null;
        }>;
    };

type InvoicePayload = InvoiceDetail | { data: InvoiceDetail };

const props = defineProps<{
    invoice: InvoicePayload;
    payment_methods?: {
        data: Array<{
            id: number;
            name: string;
            is_default: boolean;
            is_active: boolean;
        }>;
    };
    default_payment_method_id?: number | null;
}>();

const invoiceData = computed<InvoiceDetail>(() =>
    'data' in props.invoice ? props.invoice.data : props.invoice,
);

const page = usePage();
const isEmployee = computed(() => {
    const rawType = (page.props as any).auth?.user?.type;
    return rawType === 'employee' || rawType?.value === 'employee' || rawType?.name === 'employee';
});
const basePath = computed(() => (isEmployee.value ? '/admin/employee' : '/admin'));
const invoicesBasePath = computed(() => `${basePath.value}/invoices`);
const invoicePrefix = computed(() => String((page.props as any).branding?.business_settings?.invoice_prefix ?? '').trim());
const businessSettings = computed(() => (page.props as any).branding?.business_settings ?? {});
const currencySymbol = computed(() => String(businessSettings.value.currency_symbol ?? '$'));
const currencyPosition = computed(() => String(businessSettings.value.currency_position ?? 'left'));
const businessName = computed(() => businessSettings.value.business_name || page.props.name || 'Business');
const businessTagline = computed(() => businessSettings.value.tagline || '');
const businessAddress = computed(() => businessSettings.value.contact_address || '');
const businessPhone = computed(() => businessSettings.value.contact_phone || '');
const businessEmail = computed(() => businessSettings.value.contact_email || '');
const invoiceTerms = computed(() => String(businessSettings.value.invoice_terms ?? '').trim());
const businessLogo = computed(() => {
    const value = businessSettings.value.logo_url;
    if (!value) {
        return '';
    }
    if (value.startsWith('/storage/')) {
        return value;
    }
    return value.startsWith('http') ? value : `/storage/${value}`;
});
const displayInvoiceId = computed(() => {
    const base = invoiceData.value.readable_id ?? (invoiceId.value ? String(invoiceId.value).padStart(6, '0') : '');
    if (!base) {
        return '-';
    }
    return invoicePrefix.value ? `${invoicePrefix.value}${base}` : base;
});

const invoiceId = computed(() => invoiceData.value?.id ?? null);
const invoicePaymentsBasePath = computed(() => {
    if (!invoiceId.value) {
        return '';
    }

    return `${invoicesBasePath.value}/${invoiceId.value}/payments`;
});
const lineItems = computed(() => invoiceData.value?.line_items ?? []);
const payments = computed(() => invoiceData.value?.payments ?? []);
const isPaid = computed(() => Number(invoiceData.value?.balance ?? 0) <= 0);
const paymentMethods = computed(() => props.payment_methods?.data ?? []);
const defaultPaymentMethodId = computed(
    () => props.default_payment_method_id ?? paymentMethods.value.find((method) => method.is_default)?.id ?? null,
);
const paymentMethodOptions = computed(() => [
    { value: null, label: 'Select a payment method' },
    ...paymentMethods.value.map((method) => ({
        value: method.id,
        label: method.is_default ? `${method.name} (Default)` : method.name,
    })),
]);
const hasPaymentMethods = computed(() => paymentMethods.value.length > 0);

const paymentForm = useForm({
    amount: '',
    paid_at: new Date().toISOString().slice(0, 10),
    notes: '',
    payment_method_id: defaultPaymentMethodId.value,
});

const ensureDefaultPaymentMethod = () => {
    if (paymentForm.payment_method_id) {
        return;
    }
    if (defaultPaymentMethodId.value) {
        paymentForm.payment_method_id = defaultPaymentMethodId.value;
    }
};

ensureDefaultPaymentMethod();

const formatDate = (value: string) => {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(date);
};

const formatAmount = (value: string | number) => {
    const amount = Number(value || 0);
    const formatted = amount.toFixed(2);
    return currencyPosition.value === 'right'
        ? `${formatted}${currencySymbol.value}`
        : `${currencySymbol.value}${formatted}`;
};

const statusClasses = (status: string) => {
    const normalized = status?.toLowerCase();
    if (normalized === 'paid') return 'bg-emerald-50 text-emerald-600';
    if (normalized === 'overdue') return 'bg-rose-50 text-rose-600';
    if (normalized === 'sent') return 'bg-amber-50 text-amber-600';
    return 'bg-slate-100 text-slate-600';
};

const submitPayment = () => {
    if (!invoiceId.value) {
        return;
    }
    if (isPaid.value) {
        return;
    }
    if (!hasPaymentMethods.value) {
        return;
    }

    paymentForm.post(invoicePaymentsBasePath.value, {
        preserveScroll: true,
        onSuccess: () => {
            paymentForm.reset('amount', 'notes');
        },
    });
};

const showDeleteModal = ref(false);
const deleting = ref(false);

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!invoiceId.value) {
        return;
    }

    deleting.value = true;
    router.delete(`${invoicesBasePath.value}/${invoiceId.value}`, {
        onFinish: () => {
            deleting.value = false;
            showDeleteModal.value = false;
        },
    });
};

const printInvoice = () => {
    preparePagedPrint().then(() => {
        window.print();
    });
};

const preparePagedPrint = async () => {
    if (typeof window === 'undefined') {
        return;
    }

    const waitForPrintRoot = async () => {
        await nextTick();
        for (let i = 0; i < 30; i += 1) {
            const root = document.querySelector('.print-only') as HTMLElement | null;
            if (root && root.textContent && root.textContent.trim().length > 0) {
                return root;
            }
            await new Promise((resolve) => setTimeout(resolve, 50));
        }
        return document.querySelector('.print-only') as HTMLElement | null;
    };

    if ((window as any).__pagedReady) {
        return;
    }

    const setPrintPreview = () => {
        if (typeof document !== 'undefined' && document.documentElement) {
            document.documentElement.classList.add('print-preview');
            window.addEventListener(
                'afterprint',
                () => {
                    document.documentElement.classList.remove('print-preview');
                    document.documentElement.classList.remove('paged-ready');
                },
                { once: true },
            );
        }
    };

    const collectStylesheets = () => {
        const stylesheets = Array.from(
            document.querySelectorAll("link[rel='stylesheet']:not([data-pagedjs-ignore], [media~='screen'])"),
        );
        const inlineStyles = Array.from(
            document.querySelectorAll("style:not([data-pagedjs-inserted-styles], [data-pagedjs-ignore], [media~='screen'])"),
        );
        const elements = [...stylesheets, ...inlineStyles].sort((element1, element2) => {
            const position = element1.compareDocumentPosition(element2);
            if (position === Node.DOCUMENT_POSITION_PRECEDING) {
                return 1;
            }
            if (position === Node.DOCUMENT_POSITION_FOLLOWING) {
                return -1;
            }
            return 0;
        });

        return elements
            .map((element) => {
                if (element.nodeName === 'STYLE') {
                    return { [window.location.href]: element.textContent ?? '' };
                }
                if (element.nodeName === 'LINK') {
                    return (element as HTMLLinkElement).href;
                }
                return null;
            })
            .filter(Boolean);
    };

    try {
        if (!(window as any).__pagedLoaded) {
            (window as any).PagedConfig = { auto: false };
            await import('@/vendor/paged.polyfill.js');
            (window as any).__pagedLoaded = true;
        }

        setPrintPreview();

        const printRoot = await waitForPrintRoot();
        if (printRoot && (window as any).PagedPolyfill?.preview) {
            const renderTargetId = 'pagedjs-print-root';
            let renderTarget = document.getElementById(renderTargetId);
            if (!renderTarget) {
                renderTarget = document.createElement('div');
                renderTarget.id = renderTargetId;
                renderTarget.className = 'pagedjs-print';
                document.body.appendChild(renderTarget);
            }

            renderTarget.innerHTML = '';
            const contentClone = printRoot.cloneNode(true) as HTMLElement;
            renderTarget.appendChild(contentClone);

            const stylesheets = collectStylesheets();
            await (window as any).PagedPolyfill.preview(contentClone, stylesheets, renderTarget);

            const pages = renderTarget.querySelectorAll('.pagedjs_page');
            pages.forEach((pageElement, index) => {
                const target = pageElement.querySelector('.pagedjs_margin-bottom-center .pagedjs_margin-content') as HTMLElement | null;
                if (target) {
                    target.textContent = `Page ${index + 1} of ${pages.length}`;
                    target.style.fontSize = '10px';
                    target.style.color = '#94a3b8';
                    target.style.textAlign = 'center';
                }
            });

            if (document.documentElement) {
                document.documentElement.classList.add('paged-ready');
            }
        }
    } catch (error) {
        // If pagedjs isn't available, fall back to native print.
        setPrintPreview();
    }

    (window as any).__pagedReady = true;
};

if (typeof window !== 'undefined') {
    (window as any).__prepareInvoicePrint = preparePagedPrint;
}
</script>

<template>
    <Head :title="invoiceId ? `Invoice #${displayInvoiceId}` : __('Invoice')" />

    <AdminLayout>
        <div class="max-w-5xl mx-auto space-y-6 print-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-slate-500">
                        <Link :href="invoicesBasePath" class="hover:text-brand-600">{{ __('Invoices') }}</Link>
                        <span class="mx-2">/</span>
                        <span class="text-slate-700">
                            {{ __('Invoice #') }}{{ displayInvoiceId }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3 mt-2">
                        <h1 class="text-xl font-extrabold text-slate-900">
                            {{ __('Invoice #') }}{{ displayInvoiceId }}
                        </h1>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="statusClasses(invoiceData.status)">
                            {{ invoiceData.status }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500">{{ __('Review invoice details and payments.') }}</p>
                </div>
                <div v-if="invoiceId" class="flex items-center gap-2 print-hidden">
                    <button
                        type="button"
                        @click="printInvoice"
                        class="w-9 h-9 rounded-xl border border-slate-200 text-slate-500 hover:text-brand-600 flex items-center justify-center"
                    >
                        <Printer class="w-4 h-4" />
                    </button>
                    <button
                        type="button"
                        @click="confirmDelete"
                        class="w-9 h-9 rounded-xl border border-slate-200 text-slate-500 hover:text-rose-500 flex items-center justify-center"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>
                    <Link
                        :href="`${invoicesBasePath}/${invoiceId}/edit`"
                        class="px-4 py-2 text-sm font-bold rounded-xl bg-brand-600 text-white flex items-center gap-2"
                    >
                        <FileText class="w-4 h-4" />
                        {{ __('Edit Invoice') }}
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-slate-200/70 rounded-[2rem] p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-500">{{ __('Customer') }}</div>
                                <div class="text-base font-bold text-slate-900">{{ invoiceData.customer?.name || '-' }}</div>
                                <div class="text-sm text-slate-500">{{ invoiceData.customer?.email || '' }}</div>
                            </div>
                            <div class="text-sm text-slate-500">
                                <div class="flex items-center gap-2">
                                    <Calendar class="w-4 h-4" />
                                    {{ __('Issued') }}: {{ formatDate(invoiceData.issue_date) }}
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <Calendar class="w-4 h-4" />
                                    {{ __('Due') }}: {{ formatDate(invoiceData.due_date) }}
                                </div>
                            </div>
                        </div>
                        <div v-if="invoiceData.notes" class="text-sm text-slate-600 border-t border-slate-100 pt-4">
                            {{ invoiceData.notes }}
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200/70 rounded-[2rem]">
                        <div class="p-5 border-b border-slate-100">
                            <h2 class="text-base font-bold text-slate-900">{{ __('Line Items') }}</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-start">
                                <thead class="bg-slate-50/60">
                                    <tr>
                                        <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400">{{ __('Item') }}</th>
                                        <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400">{{ __('Description') }}</th>
                                        <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400">{{ __('Qty') }}</th>
                                        <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400">{{ __('Unit Price') }}</th>
                                        <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400">{{ __('Tax') }}</th>
                                        <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-end">{{ __('Line Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="line in lineItems" :key="line.id">
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ line.name || line.item?.name || '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ line.description || '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ line.qty }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ formatAmount(line.unit_price) }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            <span v-if="line.tax">{{ line.tax.name }} ({{ line.tax.type === 'percentage' ? `${line.tax.rate}%` : `$${line.tax.rate}` }})</span>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-900 text-end">{{ formatAmount(line.line_total) }}</td>
                                    </tr>
                                    <tr v-if="!lineItems.length">
                                        <td colspan="6" class="px-6 py-6 text-center text-sm text-slate-500">{{ __('No line items.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white border border-slate-200/70 rounded-[2rem] p-5 space-y-3">
                        <h3 class="text-base font-bold text-slate-900">{{ __('Summary') }}</h3>
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="font-semibold text-slate-900">{{ formatAmount(invoiceData.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>{{ __('Tax Total') }}</span>
                            <span class="font-semibold text-slate-900">{{ formatAmount(invoiceData.tax_total) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>{{ __('Discount') }}</span>
                            <span class="font-semibold text-slate-900">-{{ formatAmount(invoiceData.discount) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-slate-900 border-t border-slate-100 pt-3">
                            <span>{{ __('Total') }}</span>
                            <span>{{ formatAmount(invoiceData.total) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-slate-700">
                            <span>{{ __('Balance') }}</span>
                            <span>{{ formatAmount(invoiceData.balance) }}</span>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200/70 rounded-[2rem] p-5 space-y-4">
                        <h3 class="text-base font-bold text-slate-900">{{ __('Payments') }}</h3>
                        <div class="space-y-3">
                            <div v-for="payment in payments" :key="payment.id" class="p-3 rounded-xl border border-slate-100">
                                <div class="flex justify-between text-sm font-semibold text-slate-900">
                                    <span>{{ formatAmount(payment.amount) }}</span>
                                    <span class="text-slate-500">{{ formatDate(payment.paid_at) }}</span>
                                </div>
                                <p v-if="payment.payment_method?.name" class="text-xs text-slate-500 mt-1">{{ payment.payment_method.name }}</p>
                                <p v-if="payment.notes" class="text-xs text-slate-500 mt-1">{{ payment.notes }}</p>
                            </div>
                            <div v-if="!payments.length" class="text-sm text-slate-500">{{ __('No payments recorded.') }}</div>
                        </div>

                        <form v-if="!isPaid" @submit.prevent="submitPayment" class="space-y-3 border-t border-slate-100 pt-4">
                            <div class="text-sm font-semibold text-slate-700">{{ __('Record Payment') }}</div>
                            <div>
                                <SelectInput
                                    v-model="paymentForm.payment_method_id"
                                    :options="paymentMethodOptions"
                                    :disabled="paymentForm.processing || isPaid || !hasPaymentMethods"
                                    :button-class="'px-3 py-2 text-sm border border-slate-200 rounded-xl bg-white disabled:bg-slate-100 disabled:text-slate-400'"
                                />
                                <p v-if="paymentForm.errors.payment_method_id" class="text-xs text-red-600">{{ paymentForm.errors.payment_method_id }}</p>
                                <p v-if="!hasPaymentMethods" class="text-xs text-slate-500 mt-1">
                                    {{ __('Add a payment method in System > Payment Methods to record payments.') }}
                                </p>
                            </div>
                            <div>
                                <input v-model="paymentForm.amount" type="number" min="0" step="0.01" placeholder="Amount" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl" :disabled="paymentForm.processing || isPaid" />
                                <p v-if="paymentForm.errors.amount" class="text-xs text-red-600">{{ paymentForm.errors.amount }}</p>
                            </div>
                            <div>
                                <input v-model="paymentForm.paid_at" type="date" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl" :disabled="paymentForm.processing || isPaid" />
                                <p v-if="paymentForm.errors.paid_at" class="text-xs text-red-600">{{ paymentForm.errors.paid_at }}</p>
                            </div>
                            <div>
                                <input v-model="paymentForm.notes" type="text" placeholder="Notes (optional)" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl" :disabled="paymentForm.processing || isPaid" />
                                <p v-if="paymentForm.errors.notes" class="text-xs text-red-600">{{ paymentForm.errors.notes }}</p>
                            </div>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-bold rounded-xl bg-brand-600 text-white flex items-center gap-2"
                                :disabled="paymentForm.processing || isPaid"
                            >
                                <Plus class="w-4 h-4" />
                                {{ paymentForm.processing ? __('Saving...') : __('Add Payment') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="print-only max-w-5xl mx-auto">
            <div class="print-sheet bg-white border border-slate-200/70 rounded-[2rem] p-6 space-y-6">
                <div class="flex items-start justify-between gap-6">
                    <div class="space-y-2 flex-1">
                        <div class="flex items-center gap-3">
                            <div v-if="businessLogo" class="h-14 w-14 rounded-2xl border border-slate-100 bg-white shadow-sm flex items-center justify-center overflow-hidden">
                                <img :src="businessLogo" alt="Logo" class="h-full w-full object-cover" />
                            </div>
                            <div>
                                <div class="text-xl font-extrabold text-slate-900">{{ businessName }}</div>
                                <div v-if="businessTagline" class="text-xs text-slate-500">{{ businessTagline }}</div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-600 flex flex-wrap items-center gap-3">
                            <span>{{ __('Invoice No') }}: {{ displayInvoiceId }}</span>
                            <span>{{ __('Issue Date') }}: {{ formatDate(invoiceData.issue_date) }}</span>
                            <span>{{ __('Due Date') }}: {{ formatDate(invoiceData.due_date) }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 p-4">
                    <div class="text-xs uppercase tracking-widest text-slate-400 mb-3">{{ __('Bill To') }}</div>
                    <div class="flex flex-wrap items-start justify-between gap-4 text-sm text-slate-700">
                        <div>
                            <div class="text-base font-semibold text-slate-900">{{ invoiceData.customer?.name || '-' }}</div>
                            <div>{{ invoiceData.customer?.email || '' }}</div>
                        </div>
                        <div class="text-sm text-slate-600">
                            <div>{{ __('Invoice Date') }}: {{ formatDate(invoiceData.issue_date) }}</div>
                            <div>{{ __('Due Date') }}: {{ formatDate(invoiceData.due_date) }}</div>
                        </div>
                    </div>
                </div>

                <div class="border border-slate-100 rounded-2xl overflow-hidden">
                    <table class="w-full text-start">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="w-12 px-4 py-2 text-[10px] uppercase tracking-widest text-slate-500">{{ __('No') }}</th>
                                <th class="px-4 py-2 text-[10px] uppercase tracking-widest text-slate-500 text-start">{{ __('Description') }}</th>
                                <th class="px-4 py-2 text-[10px] uppercase tracking-widest text-slate-500 text-center">{{ __('Qty') }}</th>
                                <th class="px-4 py-2 text-[10px] uppercase tracking-widest text-slate-500 text-center">{{ __('Price') }}</th>
                                <th class="px-4 py-2 text-[10px] uppercase tracking-widest text-slate-500 text-center">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(line, index) in lineItems" :key="line.id">
                                <td class="px-4 py-2 text-sm font-semibold text-slate-900 text-center">{{ index + 1 }}</td>
                                <td class="px-4 py-2 text-start">
                                    <div class="text-sm font-semibold text-slate-900">
                                        {{ line.name || line.item?.name || '-' }}
                                    </div>
                                    <div v-if="line.description" class="text-xs text-slate-500 mt-1">
                                        {{ line.description }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-sm text-slate-600 text-center">{{ line.qty }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600 text-center">{{ formatAmount(line.unit_price) }}</td>
                                <td class="px-4 py-2 text-sm font-semibold text-slate-900 text-center">{{ formatAmount(line.line_total) }}</td>
                            </tr>
                            <tr v-if="!lineItems.length">
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">{{ __('No line items.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <div class="w-full max-w-sm space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>{{ __('Sub Total') }}</span>
                            <span class="font-semibold text-slate-900">{{ formatAmount(invoiceData.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>{{ __('Tax') }}</span>
                            <span class="font-semibold text-slate-900">{{ formatAmount(invoiceData.tax_total) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>{{ __('Discount') }}</span>
                            <span class="font-semibold text-slate-900">-{{ formatAmount(invoiceData.discount) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-slate-900 border-t border-slate-100 pt-2">
                            <span>{{ __('Grand Total') }}</span>
                            <span>{{ formatAmount(invoiceData.total) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-auto space-y-4">
                    <div v-if="invoiceTerms" class="text-xs text-slate-600 space-y-2">
                        <div class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                            {{ __('Terms & Conditions') }}
                        </div>
                        <div class="print-terms space-y-2" v-html="invoiceTerms"></div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex justify-end">
                        <div class="text-end">
                            <div class="h-10 w-48 border-b border-slate-300 mb-2"></div>
                            <div class="text-sm font-semibold text-slate-700">{{ __('Signature') }}</div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-4 text-xs text-slate-500 flex flex-wrap items-center gap-4">
                        <span v-if="businessAddress">{{ businessAddress }}</span>
                        <span v-if="businessPhone">{{ businessPhone }}</span>
                        <span v-if="businessEmail">{{ businessEmail }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>

    <ConfirmationModal
        :show="showDeleteModal"
        :title="__('Delete Invoice?')"
        :message="__('Are you sure you want to delete this invoice? This action cannot be undone.')"
        :confirmText="__('Yes, Delete Invoice')"
        :cancelText="__('No, Cancel')"
        type="danger"
        :processing="deleting"
        @close="showDeleteModal = false"
        @confirm="handleDelete"
    />
</template>

<style>
@page {
    size: A4;
    margin: 12mm 12mm 16mm;
    @bottom-center {
        content: "Page " counter(page) " of " counter(pages);
        color: #94a3b8;
        font-size: 10px;
    }
}

@media print {

    .print-only {
        display: block !important;
    }

    .paged-ready .pagedjs-print {
        display: block !important;
    }

    .paged-ready .print-only {
        display: none !important;
    }

    .print-hidden {
        display: none !important;
    }

    #admin-sidebar,
    header,
    .print-hidden {
        display: none !important;
    }

    body {
        background: #ffffff !important;
    }

    main {
        padding: 0 !important;
    }

    .print-sheet {
        min-height: 265mm;
        display: flex;
        flex-direction: column;
    }

}

.print-only {
    display: none;
}

.print-preview .print-only {
    display: block !important;
}

.print-preview .print-hidden {
    display: none !important;
}

.print-preview body {
    background: #ffffff;
}

.pagedjs-print {
    display: none;
}

.paged-ready .pagedjs-print {
    display: block !important;
}

.paged-ready .print-only {
    display: none !important;
}

.print-terms p {
    margin: 0 0 6px;
}

.print-terms ul,
.print-terms ol {
    margin: 0 0 6px 18px;
    padding: 0;
}

.print-terms li {
    margin-bottom: 4px;
}
</style>
