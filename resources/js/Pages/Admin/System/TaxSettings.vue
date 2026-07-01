<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Receipt, Settings } from 'lucide-vue-next';
import { update } from '@/routes/admin/system/tax-settings';
import SelectInput from '@/Components/Common/SelectInput.vue';
import { taxes as taxLookup } from '@/routes/admin/lookups';

const props = defineProps<{
    settings: {
        tax_enabled: boolean;
        tax_mode: 'none' | 'global' | 'item' | 'category' | string;
        default_tax_id: number | null;
    };
    taxes: Array<{
        id: number;
        name: string;
        type: string;
        rate: number;
    }>;
}>();

const form = useForm({
    tax_enabled: props.settings.tax_enabled,
    tax_mode: props.settings.tax_mode,
    default_tax_id: props.settings.default_tax_id,
});

const lastEnabledMode = ref(form.tax_mode === 'none' ? 'global' : form.tax_mode);

const isTaxEnabled = computed(() => form.tax_enabled);
const isGlobalMode = computed(() => form.tax_mode === 'global');

watch(() => form.tax_enabled, (value) => {
    if (!value) {
        if (form.tax_mode !== 'none') {
            lastEnabledMode.value = form.tax_mode;
        }
        form.tax_mode = 'none';
        form.default_tax_id = null;
        return;
    }

    if (form.tax_mode === 'none') {
        form.tax_mode = lastEnabledMode.value || 'global';
    }
});

watch(() => form.tax_mode, (mode) => {
    if (mode !== 'global') {
        form.default_tax_id = null;
    }
    if (mode !== 'none') {
        lastEnabledMode.value = mode;
    }
});

const submit = () => {
    form.put(update.url(), {
        preserveScroll: true,
    });
};

const formatTaxLabel = (tax: { name: string; type: string; rate: number }) => {
    const rateValue = tax.type === 'percentage' ? `${tax.rate}%` : `$${tax.rate}`;
    return `${tax.name} (${rateValue})`;
};

const taxSearchUrl = taxLookup.url();
</script>

<template>
    <Head title="Tax Setup" />

    <AdminLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div>
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                        <Receipt class="w-5 h-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900">{{ __('Tax Setup') }}</h1>
                        <p class="text-sm text-slate-500">{{ __('Configure how taxes apply across invoices.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200/70 rounded-[2rem] shadow-sm">
                <form @submit.prevent="submit" class="p-5 space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">{{ __('Enable Taxes') }}</h2>
                            <p class="text-sm text-slate-500">{{ __('Turn taxes on or off for invoices.') }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" v-model="form.tax_enabled" />
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-300 rounded-full peer peer-checked:bg-brand-600">
                                <div class="h-5 w-5 bg-white rounded-full absolute top-0.5 left-0.5 transition-all peer-checked:translate-x-5"></div>
                            </div>
                        </label>
                    </div>
                    <p v-if="form.errors.tax_enabled" class="text-sm text-red-600">{{ form.errors.tax_enabled }}</p>

                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-sm font-bold text-slate-700">
                            <Settings class="w-4 h-4" />
                            {{ __('Tax Mode') }}
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <label class="border rounded-xl p-3 cursor-pointer transition-all"
                                :class="form.tax_mode === 'none' ? 'border-brand-500 bg-brand-50' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" class="sr-only" value="none" v-model="form.tax_mode" :disabled="!isTaxEnabled" />
                                <div class="text-sm font-semibold text-slate-900">{{ __('No Tax') }}</div>
                                <p class="text-xs text-slate-500 mt-1">{{ __('Ignore all tax calculations.') }}</p>
                            </label>
                            <label class="border rounded-xl p-3 cursor-pointer transition-all"
                                :class="form.tax_mode === 'global' ? 'border-brand-500 bg-brand-50' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" class="sr-only" value="global" v-model="form.tax_mode" :disabled="!isTaxEnabled" />
                                <div class="text-sm font-semibold text-slate-900">{{ __('Apply Global Tax') }}</div>
                                <p class="text-xs text-slate-500 mt-1">{{ __('Apply one tax rate to the full invoice.') }}</p>
                            </label>
                            <label class="border rounded-xl p-3 cursor-pointer transition-all"
                                :class="form.tax_mode === 'item' ? 'border-brand-500 bg-brand-50' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" class="sr-only" value="item" v-model="form.tax_mode" :disabled="!isTaxEnabled" />
                                <div class="text-sm font-semibold text-slate-900">{{ __('Apply Item Tax') }}</div>
                                <p class="text-xs text-slate-500 mt-1">{{ __('Use tax per item or service.') }}</p>
                            </label>
                            <label class="border rounded-xl p-3 cursor-pointer transition-all"
                                :class="form.tax_mode === 'category' ? 'border-brand-500 bg-brand-50' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" class="sr-only" value="category" v-model="form.tax_mode" :disabled="!isTaxEnabled" />
                                <div class="text-sm font-semibold text-slate-900">{{ __('Apply Category Tax') }}</div>
                                <p class="text-xs text-slate-500 mt-1">{{ __('Use tax assigned to each category.') }}</p>
                            </label>
                        </div>
                        <p v-if="form.errors.tax_mode" class="text-sm text-red-600">{{ form.errors.tax_mode }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700" for="default_tax_id">
                            {{ __('Default Tax') }}
                        </label>
                        <SelectInput
                            id="default_tax_id"
                            v-model="form.default_tax_id"
                            :disabled="!isTaxEnabled || !isGlobalMode"
                            :options="[
                                { value: null, label: __('Select a tax rate') },
                                ...taxes.map((tax) => ({ value: tax.id, label: formatTaxLabel(tax) })),
                            ]"
                            :fetch-url="taxSearchUrl"
                            :search-placeholder="__('Search taxes...')"
                            :button-class="'px-4 py-2 text-sm border border-slate-200 rounded-xl bg-white disabled:bg-slate-100 disabled:text-slate-400'"
                        />
                        <p class="text-xs text-slate-500">
                            {{ __('Required only for Global Tax mode.') }}
                        </p>
                        <p v-if="form.errors.default_tax_id" class="text-sm text-red-600">{{ form.errors.default_tax_id }}</p>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm font-bold rounded-xl bg-brand-600 text-white hover:bg-brand-700 transition-colors disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? __('Saving...') : __('Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
