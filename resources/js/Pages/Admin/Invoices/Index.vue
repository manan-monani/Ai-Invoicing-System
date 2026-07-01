<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Common/Pagination.vue';
import SelectInput from '@/Components/Common/SelectInput.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import admin from '@/routes/admin';
import { Eye, Loader2, Mail, Pencil, Plus, Printer } from 'lucide-vue-next';

const props = defineProps<{
    invoices: {
        data: Array<{
            id: number;
            readable_id?: string;
            customer: { id: number; name: string; email: string } | null;
            issue_date: string;
            due_date: string;
            status: string;
            subtotal: string | number;
            tax_total: string | number;
            total: string | number;
            balance: string | number;
        }>;
        links: { url: string | null; label: string; active: boolean }[];
        meta?: { current_page?: number; last_page?: number; per_page?: number; path?: string };
    };
    filters?: {
        sort?: string;
        per_page?: number;
        search?: string;
    };
}>();

const sort = ref(props.filters?.sort || 'newest');
const search = ref(props.filters?.search ?? '');
const perPage = computed(() => Number(props.filters?.per_page ?? props.invoices.meta?.per_page ?? 10));
const page = usePage();
const isEmployee = computed(() => {
    const rawType = (page.props as any).auth?.user?.type;
    return rawType === 'employee' || rawType?.value === 'employee' || rawType?.name === 'employee';
});
const canSendEmail = computed(() => !isEmployee.value);
const basePath = computed(() => (isEmployee.value ? '/admin/employee' : '/admin'));
const invoicesBasePath = computed(() => `${basePath.value}/invoices`);
const invoiceIndexUrl = computed(() => invoicesBasePath.value);
const invoiceCreateUrl = computed(() => `${invoicesBasePath.value}/create`);
const invoiceShowUrl = (id: number) => `${invoicesBasePath.value}/${id}`;
const invoiceEditUrl = (id: number) => `${invoicesBasePath.value}/${id}/edit`;
const invoicePrefix = computed(() => String((page.props as any).branding?.business_settings?.invoice_prefix ?? '').trim());
const currencySymbol = computed(() => String((page.props as any).branding?.business_settings?.currency_symbol ?? '$'));
const currencyPosition = computed(() => String((page.props as any).branding?.business_settings?.currency_position ?? 'left'));

const formatInvoiceId = (invoice: { id: number; readable_id?: string }) => {
    const base = invoice.readable_id ?? String(invoice.id).padStart(6, '0');
    return invoicePrefix.value ? `${invoicePrefix.value}${base}` : base;
};

watch(sort, (value) => {
    router.get(
        invoiceIndexUrl.value,
        { sort: value, per_page: perPage.value, search: search.value || null, page: 1 },
        { preserveState: true, replace: true }
    );
});

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(search, (value) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        router.get(
            invoiceIndexUrl.value,
            { sort: sort.value, per_page: perPage.value, search: value || null, page: 1 },
            { preserveState: true, replace: true }
        );
    }, 300);
});

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

const sendingEmail = ref<Record<number, boolean>>({});

const sendInvoiceEmail = (invoice: { id: number }) => {
    if (sendingEmail.value[invoice.id]) {
        return;
    }

    sendingEmail.value[invoice.id] = true;
    router.post(admin.invoices.email.url(invoice.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            sendingEmail.value[invoice.id] = false;
        },
    });
};

const printInvoice = (id: number) => {
    const url = invoiceShowUrl(id);
    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.left = '-9999px';
    iframe.style.top = '0';
    iframe.style.width = '1024px';
    iframe.style.height = '768px';
    iframe.style.border = '0';
    iframe.style.opacity = '0';
    iframe.src = url;

    iframe.onload = () => {
        const targetWindow = iframe.contentWindow;
        if (!targetWindow) {
            iframe.remove();
            return;
        }

        const runPrint = () => {
            targetWindow.focus();
            targetWindow.print();
            targetWindow.onafterprint = () => {
                iframe.remove();
            };
        };

        const waitForPrepare = () =>
            new Promise<(() => Promise<void>) | null>((resolve) => {
                const started = Date.now();
                const check = () => {
                    const candidate = (targetWindow as any).__prepareInvoicePrint as (() => Promise<void>) | undefined;
                    if (candidate) {
                        resolve(candidate);
                        return;
                    }
                    if (Date.now() - started > 4000) {
                        resolve(null);
                        return;
                    }
                    requestAnimationFrame(check);
                };
                check();
            });

        waitForPrepare().then((prepare) => {
            if (prepare) {
                Promise.resolve(prepare()).then(runPrint).catch(runPrint);
                return;
            }
            runPrint();
        });
    };

    document.body.appendChild(iframe);
};
</script>

<template>
    <Head title="Invoices" />

    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900">{{ __('Invoices') }}</h1>
                    <p class="text-sm text-slate-500">{{ __('Manage customer invoices and payments.') }}</p>
                </div>
                <Link :href="invoiceCreateUrl" class="px-4 py-2 text-sm font-bold rounded-xl bg-brand-600 text-white flex items-center gap-2">
                    <Plus class="w-4 h-4" />
                    {{ __('New Invoice') }}
                </Link>
            </div>

            <div class="bg-white border border-slate-200/70 rounded-[2rem] shadow-sm">
                <div class="p-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-slate-700">{{ __('All Invoices') }}</div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by invoice # (e.g. 000123)"
                                class="h-9 w-[220px] rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400"
                            />
                        </div>
                        <SelectInput
                            v-model="sort"
                            :options="[
                                { value: 'newest', label: __('Newest') },
                                { value: 'oldest', label: __('Oldest') },
                                { value: 'due_asc', label: __('Due Date (Asc)') },
                                { value: 'due_desc', label: __('Due Date (Desc)') },
                            ]"
                            :button-class="'px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white'"
                            :wrapper-class="'w-auto'"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-start border-collapse">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-slate-100">
                                <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start whitespace-nowrap align-middle">{{ __('Invoice') }}</th>
                                <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start whitespace-nowrap align-middle">{{ __('Customer') }}</th>
                                <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start whitespace-nowrap align-middle">{{ __('Issue Date') }}</th>
                                <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-start whitespace-nowrap align-middle">{{ __('Due Date') }}</th>
                                <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-center whitespace-nowrap align-middle">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-end whitespace-nowrap align-middle">{{ __('Total') }}</th>
                                <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-end whitespace-nowrap align-middle">{{ __('Balance') }}</th>
                                <th class="px-6 py-3 text-[10px] uppercase tracking-widest text-slate-400 text-end whitespace-nowrap align-middle">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 align-middle whitespace-nowrap">
                                    #{{ formatInvoiceId(invoice) }}
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <div class="text-sm font-semibold text-slate-900">{{ invoice.customer?.name || '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ invoice.customer?.email || '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 align-middle whitespace-nowrap">{{ formatDate(invoice.issue_date) }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 align-middle whitespace-nowrap">{{ formatDate(invoice.due_date) }}</td>
                                <td class="px-6 py-4 align-middle text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold" :class="statusClasses(invoice.status)">
                                        {{ invoice.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 text-end align-middle whitespace-nowrap">{{ formatAmount(invoice.total) }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 text-end align-middle whitespace-nowrap">{{ formatAmount(invoice.balance) }}</td>
                                <td class="px-6 py-4 text-end align-middle">
                                    <div class="flex justify-end items-center gap-2">
                                        <Link :href="invoiceShowUrl(invoice.id)" class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:text-brand-600 flex items-center justify-center">
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                        <Link :href="invoiceEditUrl(invoice.id)" class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:text-brand-600 flex items-center justify-center">
                                            <Pencil class="w-4 h-4" />
                                        </Link>
                                        <button
                                            v-if="canSendEmail"
                                            type="button"
                                            :disabled="!invoice.customer?.email || sendingEmail[invoice.id]"
                                            @click="sendInvoiceEmail(invoice)"
                                            class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:text-brand-600 flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed"
                                            :title="invoice.customer?.email ? __('Send invoice email') : __('Customer email missing')"
                                        >
                                            <Loader2 v-if="sendingEmail[invoice.id]" class="w-4 h-4 animate-spin" />
                                            <Mail v-else class="w-4 h-4" />
                                        </button>
                                        <button type="button" @click="printInvoice(invoice.id)" class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:text-brand-600 flex items-center justify-center">
                                            <Printer class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!invoices.data.length">
                                <td colspan="8" class="px-6 py-8 text-center text-sm text-slate-500">{{ __('No invoices yet.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination
                    :meta="invoices.meta"
                    :links="invoices.links"
                    :query="{ sort: sort, search: search }"
                />
            </div>
        </div>

    </AdminLayout>
</template>
