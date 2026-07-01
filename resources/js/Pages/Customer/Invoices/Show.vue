<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Calendar, Download, Printer } from 'lucide-vue-next';
import { computed, nextTick } from 'vue';

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
}>();

const invoiceData = computed<InvoiceDetail>(() => ('data' in props.invoice ? props.invoice.data : props.invoice));

const page = usePage();
const invoicesBasePath = '/customer/invoices';
const invoicePrefix = computed(() => String((page.props as any).branding?.business_settings?.invoice_prefix ?? '').trim());
const businessSettings = computed(() => (page.props as any).branding?.business_settings ?? {});
const currencySymbol = computed(() => String(businessSettings.value.currency_symbol ?? '$'));
const currencyPosition = computed(() => String(businessSettings.value.currency_position ?? 'left'));
const businessName = computed(() => businessSettings.value.business_name || (page.props as any).name || 'Business');
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

const invoiceId = computed(() => invoiceData.value?.id ?? null);
const displayInvoiceId = computed(() => {
    const base = invoiceData.value.readable_id ?? (invoiceId.value ? String(invoiceId.value).padStart(6, '0') : '');
    if (!base) {
        return '-';
    }
    return invoicePrefix.value ? `${invoicePrefix.value}${base}` : base;
});

const lineItems = computed(() => invoiceData.value?.line_items ?? []);
const payments = computed(() => invoiceData.value?.payments ?? []);
const isPaid = computed(() => Number(invoiceData.value?.balance ?? 0) <= 0);

const formatDate = (value: string) => {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(date);
};

const formatAmount = (value: string | number) => {
    const amount = Number(value || 0);
    const formatted = amount.toFixed(2);
    return currencyPosition.value === 'right' ? `${formatted}${currencySymbol.value}` : `${currencySymbol.value}${formatted}`;
};

const statusClasses = (status: string) => {
    const normalized = status?.toLowerCase();
    if (normalized === 'paid') return 'bg-emerald-50 text-emerald-600';
    if (normalized === 'overdue') return 'bg-rose-50 text-rose-600';
    if (normalized === 'sent') return 'bg-amber-50 text-amber-600';
    return 'bg-slate-100 text-slate-600';
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
        const stylesheets = Array.from(document.querySelectorAll("link[rel='stylesheet']:not([data-pagedjs-ignore], [media~='screen'])"));
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

    <CustomerLayout>
        <div class="print-hidden mx-auto max-w-5xl space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-slate-500">
                        <Link :href="invoicesBasePath" class="hover:text-brand-600">{{ __('Invoices') }}</Link>
                        <span class="mx-2">/</span>
                        <span class="text-slate-700"> {{ __('Invoice #') }}{{ displayInvoiceId }} </span>
                    </div>
                    <div class="mt-2 flex items-center gap-3">
                        <h1 class="text-xl font-extrabold text-slate-900">{{ __('Invoice #') }}{{ displayInvoiceId }}</h1>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClasses(invoiceData.status)">
                            {{ invoiceData.status }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500">{{ __('Review invoice details and payments.') }}</p>
                </div>
                <div v-if="invoiceId" class="print-hidden flex items-center gap-2">
                    <button
                        type="button"
                        @click="printInvoice"
                        class="flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition-colors hover:text-brand-600"
                        title="Print"
                    >
                        <Printer class="h-4 w-4" />
                    </button>
                    <a
                        :href="`/customer/invoices/${invoiceId}/download`"
                        target="_blank"
                        class="flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white shadow-sm shadow-brand-200 transition-all hover:bg-brand-700"
                    >
                        <Download class="h-4 w-4" />
                        {{ __('Download PDF') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="space-y-4 rounded-[2rem] border border-slate-200/70 bg-white p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-slate-500">{{ __('Customer') }}</div>
                                <div class="text-base font-bold text-slate-900">{{ invoiceData.customer?.name || '-' }}</div>
                                <div class="text-sm text-slate-500">{{ invoiceData.customer?.email || '' }}</div>
                            </div>
                            <div class="text-end text-sm text-slate-500">
                                <div class="flex items-center justify-end gap-2">
                                    <Calendar class="h-4 w-4" />
                                    {{ __('Issued') }}: {{ formatDate(invoiceData.issue_date) }}
                                </div>
                                <div class="mt-1 flex items-center justify-end gap-2">
                                    <Calendar class="h-4 w-4" />
                                    {{ __('Due') }}: {{ formatDate(invoiceData.due_date) }}
                                </div>
                            </div>
                        </div>
                        <div v-if="invoiceData.notes" class="border-t border-slate-100 pt-4 text-sm text-slate-600">
                            {{ invoiceData.notes }}
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200/70 bg-white">
                        <div class="border-b border-slate-100 p-5">
                            <h2 class="text-base font-bold text-slate-900">{{ __('Line Items') }}</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-start">
                                <thead class="border-b border-slate-100 bg-slate-50/60 text-xs font-bold text-slate-500 uppercase">
                                    <tr>
                                        <th class="px-6 py-4 text-left">{{ __('Item') }}</th>
                                        <th class="px-6 py-4 text-center">{{ __('Qty') }}</th>
                                        <th class="px-6 py-4 text-right">{{ __('Price') }}</th>
                                        <th class="px-6 py-4 text-right">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="line in lineItems" :key="line.id" class="transition-colors hover:bg-slate-50/30">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-semibold text-slate-900">{{ line.name || line.item?.name || '-' }}</div>
                                            <div v-if="line.description" class="mt-0.5 text-xs text-slate-500">{{ line.description }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm text-slate-600">{{ line.qty }}</td>
                                        <td class="px-6 py-4 text-right text-sm text-slate-600">{{ formatAmount(line.unit_price) }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-bold text-slate-900">{{ formatAmount(line.line_total) }}</td>
                                    </tr>
                                    <tr v-if="!lineItems.length">
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500 italic">{{ __('No line items.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="space-y-3 rounded-[2rem] border border-slate-200/70 bg-white p-5">
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
                        <div class="flex justify-between border-t border-slate-100 pt-3 text-base font-bold text-slate-900">
                            <span>{{ __('Total') }}</span>
                            <span>{{ formatAmount(invoiceData.total) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-slate-700">
                            <span>{{ __('Balance') }}</span>
                            <span :class="isPaid ? 'text-emerald-600' : 'text-slate-900'">{{ formatAmount(invoiceData.balance) }}</span>
                        </div>
                    </div>

                    <div class="space-y-4 rounded-[2rem] border border-slate-200/70 bg-white p-5">
                        <h3 class="text-base font-bold text-slate-900">{{ __('Payments') }}</h3>
                        <div class="space-y-3">
                            <div v-for="payment in payments" :key="payment.id" class="rounded-xl border border-slate-100 bg-slate-50/20 p-3">
                                <div class="flex justify-between text-sm font-semibold text-slate-900">
                                    <span>{{ formatAmount(payment.amount) }}</span>
                                    <span class="text-xs font-normal text-slate-500">{{ formatDate(payment.paid_at) }}</span>
                                </div>
                                <p v-if="payment.payment_method?.name" class="mt-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">
                                    {{ payment.payment_method.name }}
                                </p>
                                <p v-if="payment.notes" class="mt-1 text-xs text-slate-500 italic">{{ payment.notes }}</p>
                            </div>
                            <div v-if="!payments.length" class="py-4 text-center text-sm text-slate-400 italic">
                                {{ __('No payments recorded.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="print-only mx-auto max-w-5xl">
            <div class="print-sheet space-y-6 rounded-[2rem] border border-slate-200/70 bg-white p-6">
                <!-- Print logic follows Admin patterns -->
                <div class="flex items-start justify-between gap-6">
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-3">
                            <div
                                v-if="businessLogo"
                                class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
                            >
                                <img :src="businessLogo" alt="Logo" class="h-full w-full object-cover" />
                            </div>
                            <div>
                                <div class="text-xl font-extrabold text-slate-900">{{ businessName }}</div>
                                <div v-if="businessTagline" class="text-xs text-slate-500">{{ businessTagline }}</div>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600">
                            <span>{{ __('Invoice No') }}: {{ displayInvoiceId }}</span>
                            <span>{{ __('Issue Date') }}: {{ formatDate(invoiceData.issue_date) }}</span>
                            <span>{{ __('Due Date') }}: {{ formatDate(invoiceData.due_date) }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 p-4">
                    <div class="mb-3 text-xs tracking-widest text-slate-400 uppercase">{{ __('Bill To') }}</div>
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

                <div class="overflow-hidden rounded-2xl border border-slate-100">
                    <table class="w-full text-start">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="w-12 px-4 py-2 text-[10px] tracking-widest text-slate-500 uppercase">{{ __('No') }}</th>
                                <th class="px-4 py-2 text-start text-[10px] tracking-widest text-slate-500 uppercase">{{ __('Description') }}</th>
                                <th class="px-4 py-2 text-center text-[10px] tracking-widest text-slate-500 uppercase">{{ __('Qty') }}</th>
                                <th class="px-4 py-2 text-center text-[10px] tracking-widest text-slate-500 uppercase">{{ __('Price') }}</th>
                                <th class="px-4 py-2 text-center text-[10px] tracking-widest text-slate-500 uppercase">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(line, index) in lineItems" :key="line.id">
                                <td class="px-4 py-2 text-center text-sm font-semibold text-slate-900">{{ index + 1 }}</td>
                                <td class="px-4 py-2 text-start">
                                    <div class="text-sm font-semibold text-slate-900">
                                        {{ line.name || line.item?.name || '-' }}
                                    </div>
                                    <div v-if="line.description" class="mt-1 text-xs text-slate-500">
                                        {{ line.description }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-center text-sm text-slate-600">{{ line.qty }}</td>
                                <td class="px-4 py-2 text-center text-sm text-slate-600">{{ formatAmount(line.unit_price) }}</td>
                                <td class="px-4 py-2 text-center text-sm font-semibold text-slate-900">{{ formatAmount(line.line_total) }}</td>
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
                            <span>{{ __('Tax Total') }}</span>
                            <span class="font-semibold text-slate-900">{{ formatAmount(invoiceData.tax_total) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>{{ __('Discount') }}</span>
                            <span class="font-semibold text-slate-900">-{{ formatAmount(invoiceData.discount) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-bold text-slate-900">
                            <span>{{ __('Total') }}</span>
                            <span>{{ formatAmount(invoiceData.total) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-auto space-y-4">
                    <div v-if="invoiceTerms" class="space-y-2 text-xs text-slate-600">
                        <div class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                            {{ __('Terms & Conditions') }}
                        </div>
                        <div class="print-terms space-y-2" v-html="invoiceTerms"></div>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-6">
                        <div class="text-end">
                            <div class="mb-2 h-10 w-48 border-b border-slate-300"></div>
                            <div class="text-sm font-semibold text-slate-700">{{ __('Signature') }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-4 text-xs text-slate-500">
                        <span v-if="businessAddress">{{ businessAddress }}</span>
                        <span v-if="businessPhone">{{ businessPhone }}</span>
                        <span v-if="businessEmail">{{ businessEmail }}</span>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

<style>
@page {
    size: A4;
    margin: 12mm 12mm 16mm;
    @bottom-center {
        content: 'Page ' counter(page) ' of ' counter(pages);
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
</style>
