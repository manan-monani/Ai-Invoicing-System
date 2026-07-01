<script setup lang="ts">
import Pagination from '@/Components/Common/Pagination.vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import customer from '@/routes/customer';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Download, Eye } from 'lucide-vue-next';
import { computed } from 'vue';

type InvoiceSummary = {
    id: number;
    readable_id?: string;
    issue_date: string;
    due_date: string;
    status: string;
    total: string | number;
    balance: string | number;
};

const props = defineProps<{
    invoices: {
        data: InvoiceSummary[];
        links: { url: string | null; label: string; active: boolean }[];
        meta?: { current_page?: number; last_page?: number; per_page?: number; path?: string };
    };
}>();

const page = usePage();
const currencySymbol = computed(() => String((page.props as any).branding?.business_settings?.currency_symbol ?? '$'));
const currencyPosition = computed(() => String((page.props as any).branding?.business_settings?.currency_position ?? 'left'));
const invoicePrefix = computed(() => String((page.props as any).branding?.business_settings?.invoice_prefix ?? '').trim());

const formatAmount = (value: string | number) => {
    const amount = Number(value || 0);
    const formatted = amount.toFixed(2);
    return currencyPosition.value === 'right' ? `${formatted}${currencySymbol.value}` : `${currencySymbol.value}${formatted}`;
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
</script>

<template>
    <Head title="Invoices" />

    <CustomerLayout>
        <div class="animate-fade-in space-y-6">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900">{{ __('Invoices') }}</h1>
                    <p class="text-sm text-slate-500">{{ __('Track your invoices and download statements.') }}</p>
                </div>
                <Link :href="customer.dashboard.url()" class="text-sm font-semibold text-brand-600">
                    {{ __('Back to dashboard') }}
                </Link>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-slate-200/70 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <h3 class="text-sm font-extrabold text-slate-900">{{ __('All Invoices') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-start">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/60">
                                <th class="px-5 py-3 text-start text-[10px] tracking-widest text-slate-400 uppercase">{{ __('Invoice') }}</th>
                                <th class="px-5 py-3 text-start text-[10px] tracking-widest text-slate-400 uppercase">{{ __('Issue Date') }}</th>
                                <th class="px-5 py-3 text-start text-[10px] tracking-widest text-slate-400 uppercase">{{ __('Due Date') }}</th>
                                <th class="px-5 py-3 text-start text-[10px] tracking-widest text-slate-400 uppercase">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-end text-[10px] tracking-widest text-slate-400 uppercase">{{ __('Total') }}</th>
                                <th class="px-5 py-3 text-end text-[10px] tracking-widest text-slate-400 uppercase">{{ __('Balance') }}</th>
                                <th class="px-5 py-3 text-end text-[10px] tracking-widest text-slate-400 uppercase">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="invoice in props.invoices.data" :key="invoice.id" class="hover:bg-slate-50/80">
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900">#{{ formatInvoiceId(invoice) }}</td>
                                <td class="px-5 py-3 text-sm text-slate-600">{{ formatDate(invoice.issue_date) }}</td>
                                <td class="px-5 py-3 text-sm text-slate-600">{{ formatDate(invoice.due_date) }}</td>
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold"
                                        :class="statusClasses(invoice.status)"
                                    >
                                        {{ invoice.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-end text-sm font-semibold text-slate-900">{{ formatAmount(invoice.total) }}</td>
                                <td class="px-5 py-3 text-end text-sm font-semibold text-slate-900">{{ formatAmount(invoice.balance) }}</td>
                                <td class="px-5 py-3 text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="customer.invoices.show.url(invoice.id)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition-colors hover:text-brand-600"
                                            title="View Invoice"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <a
                                            :href="customer.invoices.download.url(invoice.id)"
                                            target="_blank"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition-colors hover:text-brand-600"
                                            title="Download PDF"
                                        >
                                            <Download class="h-4 w-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="props.invoices.data.length === 0">
                                <td colspan="7" class="px-5 py-6 text-center text-sm text-slate-500">{{ __('No invoices found.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :meta="props.invoices.meta" :links="props.invoices.links" />
            </div>
        </div>
    </CustomerLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
