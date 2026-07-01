<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import customer from '@/routes/customer';
import { ArrowUpRight, Download, FileText } from 'lucide-vue-next';

type InvoiceSummary = {
    id: number;
    readable_id?: string;
    issue_date: string;
    due_date: string;
    status: string;
    total: string | number;
    balance: string | number;
};

type PaymentSummary = {
    id: number;
    amount: string | number;
    paid_at: string;
    method: string;
    invoice: { id: number; readable_id?: string; total?: string | number } | null;
};

type NextDueInvoice = {
    id: number;
    readable_id?: string;
    due_date: string;
    balance: number;
} | null;

const props = defineProps<{
    kpis: {
        outstanding: number;
        overdue: number;
        paid_month: number;
        next_due: NextDueInvoice;
    };
    status_summary: {
        draft: number;
        sent: number;
        paid: number;
        overdue: number;
    };
    alerts: {
        due_soon: number;
        overdue: number;
    };
    statement_available?: boolean;
    recent_invoices?: { data: InvoiceSummary[] };
    overdue_invoices?: { data: InvoiceSummary[] };
    payment_history?: PaymentSummary[];
}>();

const page = usePage();
const currencySymbol = computed(() => String((page.props as any).branding?.business_settings?.currency_symbol ?? '$'));
const currencyPosition = computed(() => String((page.props as any).branding?.business_settings?.currency_position ?? 'left'));
const invoicePrefix = computed(() => String((page.props as any).branding?.business_settings?.invoice_prefix ?? '').trim());

const formatAmount = (value: string | number) => {
    const amount = Number(value || 0);
    const formatted = amount.toFixed(2);
    return currencyPosition.value === 'right'
        ? `${formatted}${currencySymbol.value}`
        : `${currencySymbol.value}${formatted}`;
};

const formatDate = (value: string) => {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(date);
};

const formatInvoiceId = (invoice: { id: number; readable_id?: string }) => {
    const base = invoice.readable_id ?? String(invoice.id).padStart(6, '0');
    return invoicePrefix.value ? `${invoicePrefix.value}${base}` : base;
};

const statusClasses = (status: string) => {
    const normalized = status?.toLowerCase();
    if (normalized === 'paid') return 'bg-emerald-50 text-emerald-600';
    if (normalized === 'overdue') return 'bg-rose-50 text-rose-600';
    if (normalized === 'sent') return 'bg-amber-50 text-amber-600';
    return 'bg-slate-100 text-slate-600';
};

const recentInvoicesReady = computed(() => Array.isArray(props.recent_invoices?.data));
const overdueInvoicesReady = computed(() => Array.isArray(props.overdue_invoices?.data));
const paymentHistoryReady = computed(() => Array.isArray(props.payment_history));
const showDraft = computed(() => (props.status_summary?.draft ?? 0) > 0);
</script>

<template>
    <Head title="Dashboard" />

    <CustomerLayout>
        <div class="space-y-6 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-start">
                    <h1 class="text-xl font-extrabold text-slate-900">{{ __('Dashboard') }}</h1>
                    <p class="text-sm text-slate-500">{{ __('Track invoices, payments, and what’s due next.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="customer.invoices.index.url()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-brand-600 text-white shadow-sm">
                        <FileText class="w-4 h-4" />
                        {{ __('View All Invoices') }}
                    </Link>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border border-slate-200 bg-white text-slate-500"
                        :class="props.statement_available ? 'hover:text-brand-600' : 'opacity-60 cursor-not-allowed'"
                        :disabled="!props.statement_available"
                    >
                        <Download class="w-4 h-4" />
                        {{ __('Download Statement') }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm text-start">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('Total Outstanding') }}</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ formatAmount(props.kpis.outstanding) }}</p>
                </div>
                <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm text-start">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('Overdue Amount') }}</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ formatAmount(props.kpis.overdue) }}</p>
                </div>
                <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm text-start">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('Paid This Month') }}</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ formatAmount(props.kpis.paid_month) }}</p>
                </div>
                <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm text-start">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('Next Due Invoice') }}</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-2" v-if="props.kpis.next_due">
                        {{ formatAmount(props.kpis.next_due.balance) }}
                    </p>
                    <p v-else class="text-sm text-slate-500 mt-2">{{ __('No upcoming invoices') }}</p>
                    <div v-if="props.kpis.next_due" class="text-xs text-slate-500 mt-2">
                        #{{ formatInvoiceId(props.kpis.next_due) }} • {{ formatDate(props.kpis.next_due.due_date) }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-slate-200/60 rounded-[2rem] shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">{{ __('Recent Invoices') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('Latest 10 invoices') }}</p>
                            </div>
                            <Link :href="customer.invoices.index.url()" class="text-xs font-bold text-brand-600 flex items-center gap-1">
                                {{ __('View All') }} <ArrowUpRight class="w-3 h-3" />
                            </Link>
                        </div>
                        <div v-if="!recentInvoicesReady" class="p-5 space-y-3 animate-pulse">
                            <div v-for="i in 5" :key="i" class="h-4 bg-slate-100 rounded"></div>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-start border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/60 border-b border-slate-100">
                                        <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start">{{ __('Invoice') }}</th>
                                        <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start">{{ __('Issue Date') }}</th>
                                        <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start">{{ __('Due Date') }}</th>
                                        <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start">{{ __('Status') }}</th>
                                        <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-end">{{ __('Total') }}</th>
                                        <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-end">{{ __('Balance') }}</th>
                                        <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-end">{{ __('PDF') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="invoice in props.recent_invoices?.data" :key="invoice.id" class="hover:bg-slate-50/80">
                                        <td class="px-5 py-3 text-sm font-semibold text-slate-900">#{{ formatInvoiceId(invoice) }}</td>
                                        <td class="px-5 py-3 text-sm text-slate-600">{{ formatDate(invoice.issue_date) }}</td>
                                        <td class="px-5 py-3 text-sm text-slate-600">{{ formatDate(invoice.due_date) }}</td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold" :class="statusClasses(invoice.status)">
                                                {{ invoice.status }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-sm font-semibold text-slate-900 text-end">{{ formatAmount(invoice.total) }}</td>
                                        <td class="px-5 py-3 text-sm font-semibold text-slate-900 text-end">{{ formatAmount(invoice.balance) }}</td>
                                        <td class="px-5 py-3 text-end">
                                            <a
                                                :href="customer.invoices.download.url(invoice.id)"
                                                target="_blank"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:text-brand-600"
                                            >
                                                <Download class="w-4 h-4" />
                                            </a>
                                        </td>
                                    </tr>
                                    <tr v-if="props.recent_invoices?.data?.length === 0">
                                        <td colspan="7" class="px-5 py-6 text-sm text-slate-500 text-center">{{ __('No invoices found.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200/60 rounded-[2rem] shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">{{ __('Overdue Invoices') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('Past due balances') }}</p>
                            </div>
                            <Link :href="customer.invoices.index.url()" class="text-xs font-bold text-brand-600 flex items-center gap-1">
                                {{ __('View All') }} <ArrowUpRight class="w-3 h-3" />
                            </Link>
                        </div>
                        <div v-if="!overdueInvoicesReady" class="p-5 space-y-3 animate-pulse">
                            <div v-for="i in 5" :key="i" class="h-4 bg-slate-100 rounded"></div>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-start border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/60 border-b border-slate-100">
                                        <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start">{{ __('Invoice') }}</th>
                                        <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start">{{ __('Due Date') }}</th>
                                        <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-end">{{ __('Balance') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="invoice in props.overdue_invoices?.data" :key="invoice.id" class="hover:bg-rose-50/40">
                                        <td class="px-5 py-3 text-sm font-semibold text-slate-900">#{{ formatInvoiceId(invoice) }}</td>
                                        <td class="px-5 py-3 text-sm text-rose-600">{{ formatDate(invoice.due_date) }}</td>
                                        <td class="px-5 py-3 text-sm font-semibold text-rose-600 text-end">{{ formatAmount(invoice.balance) }}</td>
                                    </tr>
                                    <tr v-if="props.overdue_invoices?.data?.length === 0">
                                        <td colspan="3" class="px-5 py-6 text-sm text-slate-500 text-center">{{ __('No overdue invoices.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white border border-slate-200/60 rounded-[2rem] shadow-sm p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-slate-900">{{ __('Invoice Summary') }}</h3>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div v-if="showDraft" class="p-3 rounded-xl border border-slate-100 text-start">
                                <p class="text-[10px] uppercase tracking-widest text-slate-400">{{ __('Draft') }}</p>
                                <p class="text-lg font-extrabold text-slate-900">{{ props.status_summary.draft }}</p>
                            </div>
                            <div class="p-3 rounded-xl border border-slate-100 text-start">
                                <p class="text-[10px] uppercase tracking-widest text-slate-400">{{ __('Sent') }}</p>
                                <p class="text-lg font-extrabold text-slate-900">{{ props.status_summary.sent }}</p>
                            </div>
                            <div class="p-3 rounded-xl border border-slate-100 text-start">
                                <p class="text-[10px] uppercase tracking-widest text-slate-400">{{ __('Paid') }}</p>
                                <p class="text-lg font-extrabold text-slate-900">{{ props.status_summary.paid }}</p>
                            </div>
                            <div class="p-3 rounded-xl border border-slate-100 text-start">
                                <p class="text-[10px] uppercase tracking-widest text-slate-400">{{ __('Overdue') }}</p>
                                <p class="text-lg font-extrabold text-rose-600">{{ props.status_summary.overdue }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200/60 rounded-[2rem] shadow-sm p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-slate-900">{{ __('Alerts') }}</h3>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-500">{{ __('Due in next 7 days') }}</span>
                                <span class="font-bold text-slate-900">{{ props.alerts.due_soon }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-500">{{ __('Overdue') }}</span>
                                <span class="font-bold text-rose-600">{{ props.alerts.overdue }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200/60 rounded-[2rem] shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-slate-100">
                            <h3 class="text-sm font-extrabold text-slate-900">{{ __('Payment History') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('Latest 10 payments') }}</p>
                        </div>
                        <div v-if="!paymentHistoryReady" class="p-5 space-y-3 animate-pulse">
                            <div v-for="i in 5" :key="i" class="h-4 bg-slate-100 rounded"></div>
                        </div>
                        <div v-else class="divide-y divide-slate-50">
                            <div v-for="payment in props.payment_history" :key="payment.id" class="p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ formatAmount(payment.amount) }}</div>
                                        <div class="text-xs text-slate-500">{{ formatDate(payment.paid_at) }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-xs text-slate-400">{{ __('Method') }}</div>
                                        <div class="text-sm font-semibold text-slate-700">{{ payment.method }}</div>
                                    </div>
                                </div>
                                <div v-if="payment.invoice" class="text-xs text-slate-500 mt-2">
                                    {{ __('Invoice') }} #{{ formatInvoiceId(payment.invoice) }}
                                </div>
                            </div>
                            <div v-if="props.payment_history?.length === 0" class="p-5 text-sm text-slate-500 text-center">
                                {{ __('No payments recorded yet.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
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
