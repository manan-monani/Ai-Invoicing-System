<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PropType } from 'vue';
import { ChevronRight } from 'lucide-vue-next';
import admin from '@/routes/admin';
import SelectInput from '@/Components/Common/SelectInput.vue';
import { roles as roleLookup } from '@/routes/admin/lookups';

defineProps({
    roles: Array as PropType<any[]>,
});

const form = useForm({
    name: '',
    email: '',
    role_id: '',
});

const roleSearchUrl = roleLookup.url();

const submit = () => {
    form.post(admin.users.store.url(), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head :title="__('Add Employee')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-2xl mx-auto">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                     <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <Link :href="admin.users.index.url()" class="hover:text-brand-500">{{ __('Member Directory') }}</Link>
                        <ChevronRight :size="8" class="opacity-40 rtl:rotate-180" />
                        <span class="text-brand-500">{{ __('New Employee') }}</span>
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Add Employee') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Create a new employee account for your organization.') }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-[2rem] border border-slate-200/60 dark:border-slate-700 shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Full Name') }}</label>
                        <input v-model="form.name" type="text" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Email Address') }}</label>
                        <input v-model="form.email" type="email" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('A temporary password will be generated and emailed to the employee.') }}</p>

                    <div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Access Role') }}</label>
                        <SelectInput
                            v-model="form.role_id"
                            :placeholder="__('Select Role')"
                            :options="roles.map((role) => ({ value: role.id, label: role.name }))"
                            :fetch-url="roleSearchUrl"
                            :search-placeholder="__('Search roles...')"
                            :button-class="'px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium text-slate-900 dark:text-white'"
                            :list-class="'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'"
                            :option-class="'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
                        />
                        <p v-if="form.errors.role_id" class="text-red-500 text-xs mt-1">{{ form.errors.role_id }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Link :href="admin.users.index.url()" class="px-6 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all text-center">
                            {{ __('Cancel') }}
                        </Link>
                        <button type="submit" :disabled="form.processing" class="bg-brand-600 text-white px-8 py-3 rounded-2xl font-bold text-sm shadow-xl shadow-brand-600/20 hover:bg-brand-700 transition-all disabled:opacity-50">
                            {{ form.processing ? __('Saving...') : __('Create Employee') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
