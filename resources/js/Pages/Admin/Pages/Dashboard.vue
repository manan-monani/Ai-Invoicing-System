<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import admin from '@/routes/admin';
import { FileText, UserPlus, Wallet, ArrowUpRight } from 'lucide-vue-next';

type InvoiceSummary = {
    id: number;
    readable_id?: string;
    customer: { id: number; name: string; email: string } | null;
    issue_date: string;
    due_date: string;
    status: string;
    total: string | number;
    balance: string | number;
};

type TopCustomer = {
    id: number;
    name: string;
    email: string;
    total_billed: number;
    outstanding: number;
};

type SalesTrend = {
    labels: string[];
    billed: number[];
    collected: number[];
};

const props = defineProps<{
    kpis: {
        revenue_month: number;
        outstanding: number;
        overdue: number;
        paid_month: number;
    };
    status_summary: {
        draft: number;
        sent: number;
        paid: number;
        overdue: number;
    };
    payments_summary: {
        today: number;
        week: number;
        month: number;
    };
    alerts: {
        overdue: number;
        due_soon: number;
    };
    tax_summary: {
        enabled: boolean;
        total: number;
    };
    recent_invoices?: { data: InvoiceSummary[] };
    overdue_invoices?: { data: InvoiceSummary[] };
    top_customers?: TopCustomer[];
    sales_trend?: SalesTrend;
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
const topCustomersReady = computed(() => Array.isArray(props.top_customers));
const salesTrendReady = computed(() => Array.isArray(props.sales_trend?.labels));

const trendMax = computed(() => {
    if (!props.sales_trend) {
        return 0;
    }
    const values = [...props.sales_trend.billed, ...props.sales_trend.collected];
    return Math.max(1, ...values);
});
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-start">
                    <h1 class="text-xl font-extrabold text-slate-900">{{ __('Dashboard') }}</h1>
                    <p class="text-sm text-slate-500">{{ __('Revenue, invoices, and payment insights at a glance.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="admin.invoices.create.url()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-brand-600 text-white shadow-sm">
                        <FileText class="w-4 h-4" />
                        {{ __('New Invoice') }}
                    </Link>
                    <Link :href="admin.invoices.index.url()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border border-slate-200 text-slate-700 bg-white">
                        <Wallet class="w-4 h-4" />
                        {{ __('Record Payment') }}
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm text-start">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('Total Revenue (This Month)') }}</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ formatAmount(props.kpis.revenue_month) }}</p>
                </div>
                <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm text-start">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('Outstanding') }}</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ formatAmount(props.kpis.outstanding) }}</p>
                </div>
                <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm text-start">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('Overdue') }}</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ formatAmount(props.kpis.overdue) }}</p>
                </div>
                <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-sm text-start">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('Paid (This Month)') }}</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ formatAmount(props.kpis.paid_month) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div class="bg-white border border-slate-200/60 rounded-[2rem] shadow-sm overflow-hidden">
                            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-extrabold text-slate-900">{{ __('Recent Invoices') }}</h3>
                                    <p class="text-xs text-slate-500">{{ __('Latest 10 invoices') }}</p>
                                </div>
                                <Link :href="admin.invoices.index.url()" class="text-xs font-bold text-brand-600 flex items-center gap-1">
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
                                            <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start">{{ __('Customer') }}</th>
                                            <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start">{{ __('Due Date') }}</th>
                                            <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-end">{{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="invoice in props.recent_invoices?.data" :key="invoice.id" class="hover:bg-slate-50/80">
                                            <td class="px-5 py-3 text-sm font-semibold text-slate-900">
                                                #{{ formatInvoiceId(invoice) }}
                                                <span class="ms-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold" :class="statusClasses(invoice.status)">
                                                    {{ invoice.status }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3 text-sm text-slate-600">
                                                <div class="font-semibold text-slate-900">{{ invoice.customer?.name ?? '-' }}</div>
                                                <div class="text-xs text-slate-500">{{ invoice.customer?.email ?? '' }}</div>
                                            </td>
                                            <td class="px-5 py-3 text-sm text-slate-600">{{ formatDate(invoice.due_date) }}</td>
                                            <td class="px-5 py-3 text-sm font-semibold text-slate-900 text-end">{{ formatAmount(invoice.total) }}</td>
                                        </tr>
                                        <tr v-if="props.recent_invoices?.data?.length === 0">
                                            <td colspan="4" class="px-5 py-6 text-sm text-slate-500 text-center">{{ __('No invoices found.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200/60 rounded-[2rem] shadow-sm overflow-hidden">
                            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-extrabold text-slate-900">{{ __('Overdue Invoices') }}</h3>
                                    <p class="text-xs text-slate-500">{{ __('Invoices past due') }}</p>
                                </div>
                                <Link :href="admin.invoices.index.url()" class="text-xs font-bold text-brand-600 flex items-center gap-1">
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
                                            <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start">{{ __('Customer') }}</th>
                                            <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start">{{ __('Due Date') }}</th>
                                            <th class="px-5 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-end">{{ __('Balance') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="invoice in props.overdue_invoices?.data" :key="invoice.id" class="hover:bg-slate-50/80">
                                            <td class="px-5 py-3 text-sm font-semibold text-slate-900">#{{ formatInvoiceId(invoice) }}</td>
                                            <td class="px-5 py-3 text-sm text-slate-600">
                                                <div class="font-semibold text-slate-900">{{ invoice.customer?.name ?? '-' }}</div>
                                                <div class="text-xs text-slate-500">{{ invoice.customer?.email ?? '' }}</div>
                                            </td>
                                            <td class="px-5 py-3 text-sm text-slate-600">{{ formatDate(invoice.due_date) }}</td>
                                            <td class="px-5 py-3 text-sm font-semibold text-slate-900 text-end">{{ formatAmount(invoice.balance) }}</td>
                                        </tr>
                                        <tr v-if="props.overdue_invoices?.data?.length === 0">
                                            <td colspan="4" class="px-5 py-6 text-sm text-slate-500 text-center">{{ __('No overdue invoices.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200/60 rounded-[2rem] shadow-sm">
                        <div class="p-5 border-b border-slate-100">
                            <h3 class="text-sm font-extrabold text-slate-900">{{ __('Sales Trend (Last 6 Months)') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('Billed vs collected') }}</p>
                        </div>
                        <div v-if="!salesTrendReady" class="p-6 space-y-3 animate-pulse">
                            <div v-for="i in 6" :key="i" class="h-4 bg-slate-100 rounded"></div>
                        </div>
                        <div v-else class="p-6">
                            <div class="flex items-end gap-4 h-40">
                                <div v-for="(label, index) in props.sales_trend?.labels" :key="label" class="flex-1 flex flex-col items-center gap-2">
                                    <div class="flex items-end gap-1 h-28 w-full justify-center">
                                        <div
                                            class="w-3 rounded-md bg-slate-200"
                                            :style="{ height: `${(props.sales_trend?.billed[index] || 0) / trendMax * 100}%` }"
                                        />
                                        <div
                                            class="w-3 rounded-md bg-brand-600"
                                            :style="{ height: `${(props.sales_trend?.collected[index] || 0) / trendMax * 100}%` }"
                                        />
                                    </div>
                                    <div class="text-[10px] text-slate-500">{{ label }}</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-4 mt-4 text-xs text-slate-500">
                                <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-slate-200"></span>{{ __('Billed') }}</div>
                                <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-brand-600"></span>{{ __('Collected') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white border border-slate-200/60 rounded-[2rem] p-5 shadow-sm text-start">
                        <h3 class="text-sm font-extrabold text-slate-900 mb-4">{{ __('Invoice Status Summary') }}</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-200/60 p-3">
                                <p class="text-[10px] uppercase text-slate-400 font-bold">{{ __('Draft') }}</p>
                                <p class="text-lg font-extrabold text-slate-900">{{ props.status_summary.draft }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200/60 p-3">
                                <p class="text-[10px] uppercase text-slate-400 font-bold">{{ __('Sent') }}</p>
                                <p class="text-lg font-extrabold text-slate-900">{{ props.status_summary.sent }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200/60 p-3">
                                <p class="text-[10px] uppercase text-slate-400 font-bold">{{ __('Paid') }}</p>
                                <p class="text-lg font-extrabold text-slate-900">{{ props.status_summary.paid }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200/60 p-3">
                                <p class="text-[10px] uppercase text-slate-400 font-bold">{{ __('Overdue') }}</p>
                                <p class="text-lg font-extrabold text-slate-900">{{ props.status_summary.overdue }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200/60 rounded-[2rem] p-5 shadow-sm text-start">
                        <h3 class="text-sm font-extrabold text-slate-900 mb-4">{{ __('Payment Summary') }}</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">{{ __('Today') }}</span>
                                <span class="font-semibold text-slate-900">{{ formatAmount(props.payments_summary.today) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">{{ __('This Week') }}</span>
                                <span class="font-semibold text-slate-900">{{ formatAmount(props.payments_summary.week) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">{{ __('This Month') }}</span>
                                <span class="font-semibold text-slate-900">{{ formatAmount(props.payments_summary.month) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200/60 rounded-[2rem] p-5 shadow-sm text-start">
                        <h3 class="text-sm font-extrabold text-slate-900 mb-4">{{ __('Alerts') }}</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">{{ __('Overdue invoices') }}</span>
                                <span class="font-semibold text-rose-600">{{ props.alerts.overdue }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">{{ __('Due soon (7 days)') }}</span>
                                <span class="font-semibold text-amber-600">{{ props.alerts.due_soon }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="props.tax_summary.enabled" class="bg-white border border-slate-200/60 rounded-[2rem] p-5 shadow-sm text-start">
                        <h3 class="text-sm font-extrabold text-slate-900 mb-2">{{ __('Tax Summary') }}</h3>
                        <p class="text-xs text-slate-500 mb-3">{{ __('Total tax collected this month') }}</p>
                        <p class="text-xl font-extrabold text-slate-900">{{ formatAmount(props.tax_summary.total) }}</p>
                    </div>

                    <div class="bg-white border border-slate-200/60 rounded-[2rem] shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">{{ __('Top Customers') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('By total billed') }}</p>
                            </div>
                            <Link :href="admin.users.customers.index.url()" class="text-xs font-bold text-brand-600 flex items-center gap-1">
                                {{ __('View All') }} <ArrowUpRight class="w-3 h-3" />
                            </Link>
                        </div>
                        <div v-if="!topCustomersReady" class="p-5 space-y-3 animate-pulse">
                            <div v-for="i in 5" :key="i" class="h-4 bg-slate-100 rounded"></div>
                        </div>
                        <div v-else class="divide-y divide-slate-50">
                            <div v-for="customer in props.top_customers" :key="customer.id" class="px-5 py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ customer.name }}</p>
                                        <p class="text-xs text-slate-500">{{ customer.email }}</p>
                                    </div>
                                    <div class="text-end">
                                        <p class="text-sm font-bold text-slate-900">{{ formatAmount(customer.total_billed) }}</p>
                                        <p class="text-xs text-slate-500">{{ __('Outstanding') }}: {{ formatAmount(customer.outstanding) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="props.top_customers?.length === 0" class="px-5 py-6 text-sm text-slate-500 text-center">
                                {{ __('No customer data yet.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
