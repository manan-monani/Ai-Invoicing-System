<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { index, update } from '@/routes/admin/business/settings';
import { Settings, DollarSign, Globe, Search, FileText, AlignLeft } from 'lucide-vue-next';
import RichTextEditor from '@/Components/Common/RichTextEditor.vue';

const props = defineProps<{
    settings: {
        currency_symbol: string;
        currency_position: string;
        language: string;
        invoice_prefix: string;
        invoice_terms: string;
    };
}>();

const form = useForm({
    currency_symbol: props.settings.currency_symbol,
    currency_position: props.settings.currency_position,
    language: props.settings.language,
    invoice_prefix: props.settings.invoice_prefix,
    invoice_terms: props.settings.invoice_terms,
});

const languages = [
    { code: 'en', name: 'English' },
    { code: 'ar', name: 'Arabic' },
];

const submit = () => {
    form.post(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            // Form will be reset with new values from server
        },
    });
};

// Search state
const currencySearch = ref('');

const currencyExamples = [
    { symbol: '$', name: 'US Dollar', search: 'usd dollar' },
    { symbol: '€', name: 'Euro', search: 'eur euro' },
    { symbol: '£', name: 'British Pound', search: 'gbp pound sterling' },
    { symbol: '¥', name: 'Japanese Yen / Chinese Yuan', search: 'jpy cny yen yuan' },
    { symbol: '₹', name: 'Indian Rupee', search: 'inr rupee' },
    { symbol: '৳', name: 'Bangladeshi Taka', search: 'bdt taka' },
    { symbol: 'R$', name: 'Brazilian Real', search: 'brl real' },
    { symbol: '₽', name: 'Russian Ruble', search: 'rub ruble' },
    { symbol: 'A$', name: 'Australian Dollar', search: 'aud australian' },
    { symbol: 'C$', name: 'Canadian Dollar', search: 'cad canadian' },
    { symbol: '₱', name: 'Philippine Peso', search: 'php peso' },
    { symbol: '₩', name: 'South Korean Won', search: 'krw won' },
    { symbol: 'CHF', name: 'Swiss Franc', search: 'chf franc' },
    { symbol: 'kr', name: 'Swedish Krona', search: 'sek krona' },
];

const currencyPositions = [
    { value: 'left', label: 'Left (৳100.00)' },
    { value: 'right', label: 'Right (100.00৳)' },
];

// Filtered currencies based on search
const filteredCurrencies = computed(() => {
    if (!currencySearch.value) return currencyExamples;
    const search = currencySearch.value.toLowerCase();
    return currencyExamples.filter(currency => 
        currency.symbol.toLowerCase().includes(search) ||
        currency.name.toLowerCase().includes(search) ||
        currency.search.toLowerCase().includes(search)
    );
});
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                        <Settings class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ __('Business Logic') }}
                    </h1>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ __('Configure your business currency and language settings.') }}
                </p>
            </div>

            <!-- Settings Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Language Selection -->
                    <div>
                        <label for="language" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <Globe class="w-4 h-4" />
                            {{ __('System Language') }}
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button
                                v-for="lang in languages"
                                :key="lang.code"
                                type="button"
                                @click="form.language = lang.code"
                                class="flex items-center justify-between px-4 py-3 border rounded-lg transition-all"
                                :class="form.language === lang.code
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 shadow-md ring-1 ring-primary-500'
                                    : 'border-gray-200 dark:border-gray-600 hover:border-primary-300 dark:hover:border-primary-700 bg-white dark:bg-gray-800'"
                            >
                                <span class="font-medium text-gray-900 dark:text-white">{{ lang.name }}</span>
                                <div v-if="form.language === lang.code" class="w-2 h-2 rounded-full bg-primary-600"></div>
                            </button>
                        </div>
                        <p v-if="form.errors.language" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.language }}
                        </p>
                    </div>

                    <!-- Currency Symbol with Search -->
                    <div>
                        <label for="currency_symbol" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <DollarSign class="w-4 h-4" />
                            {{ __('Currency Symbol') }}
                        </label>
                        
                        <!-- Search Input -->
                        <div class="relative mb-3">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="currencySearch"
                                type="text"
                                placeholder="Search currency (e.g., USD, Euro, Taka)..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white text-sm"
                            />
                        </div>

                        <!-- Currency Grid -->
                        <div class="max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-700/50">
                            <div v-if="filteredCurrencies.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <button
                                    v-for="currency in filteredCurrencies"
                                    :key="currency.symbol"
                                    type="button"
                                    @click="form.currency_symbol = currency.symbol; currencySearch = ''"
                                    class="flex items-center gap-3 px-4 py-3 text-left bg-white dark:bg-gray-800 border rounded-lg transition-all"
                                    :class="form.currency_symbol === currency.symbol 
                                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 shadow-md' 
                                        : 'border-gray-200 dark:border-gray-600 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50/50 dark:hover:bg-primary-900/10'"
                                >
                                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400 min-w-[40px]">{{ currency.symbol }}</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ currency.name }}</div>
                                    </div>
                                </button>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <Search class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">No currencies found</p>
                            </div>
                        </div>
                        
                        <p v-if="form.errors.currency_symbol" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.currency_symbol }}
                        </p>
                    </div>

                    <!-- Currency Position -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <DollarSign class="w-4 h-4" />
                            {{ __('Currency Position') }}
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button
                                v-for="position in currencyPositions"
                                :key="position.value"
                                type="button"
                                @click="form.currency_position = position.value"
                                class="flex items-center justify-between px-4 py-3 border rounded-lg transition-all"
                                :class="form.currency_position === position.value
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 shadow-md ring-1 ring-primary-500'
                                    : 'border-gray-200 dark:border-gray-600 hover:border-primary-300 dark:hover:border-primary-700 bg-white dark:bg-gray-800'"
                            >
                                <span class="font-medium text-gray-900 dark:text-white">{{ position.label }}</span>
                                <div v-if="form.currency_position === position.value" class="w-2 h-2 rounded-full bg-primary-600"></div>
                            </button>
                        </div>
                        <p v-if="form.errors.currency_position" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.currency_position }}
                        </p>
                    </div>

                    <!-- Invoice Prefix -->
                    <div>
                        <label for="invoice_prefix" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <FileText class="w-4 h-4" />
                            {{ __('Invoice Prefix') }}
                        </label>
                        <input
                            v-model="form.invoice_prefix"
                            type="text"
                            maxlength="10"
                            placeholder="INV-"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white text-sm"
                        />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('This prefix will appear before invoice numbers (e.g., INV-000123).') }}
                        </p>
                        <p v-if="form.errors.invoice_prefix" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.invoice_prefix }}
                        </p>
                    </div>

                    <!-- Invoice Terms -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <AlignLeft class="w-4 h-4" />
                            {{ __('Invoice Terms & Conditions') }}
                        </label>
                        <RichTextEditor v-model="form.invoice_terms" :placeholder="__('Add terms and conditions that appear on invoices.')" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Shown at the bottom of invoice printouts and PDFs.') }}
                        </p>
                        <p v-if="form.errors.invoice_terms" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.invoice_terms }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-400 text-white rounded-lg font-medium transition-colors shadow-lg shadow-primary-500/30 disabled:shadow-none flex items-center gap-2"
                        >
                            <Settings class="w-4 h-4" :class="{ 'animate-spin': form.processing }" />
                            {{ form.processing ? __('Saving...') : __('Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
