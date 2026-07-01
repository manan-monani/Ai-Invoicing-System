<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PropType, computed, ref, watch } from 'vue';
import admin from '@/routes/admin';
import { debounce } from 'lodash';
import { PlusCircle, Search, Crown, Shield, Pencil, Trash2 } from 'lucide-vue-next';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';

const props = defineProps({
    users: Object as PropType<any>,
    pageTitle: String,
    pageSubtitle: String,
    indexUrl: String,
    searchPlaceholder: String,
    createLabel: String,
});

const hasCreate = computed(() => Boolean(props.createLabel));

const search = ref('');

watch(search, debounce((value) => {
    router.get(props.indexUrl ?? admin.users.index.url(), { search: value }, { preserveState: true, replace: true });
}, 300));

const toggleStatus = (user: any) => {
    const newStatus = user.status === 'active' ? 'inactive' : 'active';
    router.patch(admin.users.status.url(user.id), {
        status: newStatus
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Optimistic update or handled by Inertia reload
        }
    });
};

// Delete Logic
const showDeleteModal = ref(false);
const userToDelete = ref<any>(null);
const processingDelete = ref(false);

const confirmDelete = (user: any) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!userToDelete.value) return;
    
    processingDelete.value = true;
    router.delete(admin.users.destroy.url(userToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            userToDelete.value = null;
        },
        onFinish: () => {
            processingDelete.value = false;
        }
    });
};

const getRoleLabel = (user: any) => {
    if (user.type === 'super-admin') {
        return 'Super Admin';
    }

    const roleName = user.roles?.[0]?.name || user.type;
    if (roleName?.toLowerCase() === 'client' || user.type === 'client') {
        return 'Customer';
    }

    return roleName;
};
</script>

<template>
    <Head :title="props.pageTitle || __('Member Directory')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 text-start">
                <div class="text-start">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ props.pageTitle || __('Organization Members') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ props.pageSubtitle || __('Manage member identities and access levels.') }}</p>
                </div>
                <Link
                    v-if="hasCreate"
                    :href="admin.users.create ? admin.users.create.url() : '#'"
                    class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-2xl font-bold text-sm shadow-xl shadow-brand-600/20 flex items-center transition-all active:scale-95"
                >
                    <PlusCircle :size="18" class="me-2" /> {{ props.createLabel || __('Add New Member') }}
                </Link>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden">
                 <!-- Search -->
                <div class="p-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="relative w-full sm:w-64">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search :size="16" class="text-slate-400" />
                         </div>
                        <input v-model="search" type="text" :placeholder="props.searchPlaceholder || __('Search members...')" class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 text-slate-700 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-start border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/20 border-b border-slate-100 dark:border-slate-700/50">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Identity') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Access Role') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Account Status') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-end">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50 text-start">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all duration-200 group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img :src="user.profile_image ? '/storage/' + user.profile_image : `https://ui-avatars.com/api/?name=${user.name}&background=random`" class="w-11 h-11 rounded-2xl bg-slate-100 dark:bg-slate-800 object-cover">
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ user.name }}</p>
                                            <p class="text-[11px] text-slate-500">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 flex items-center justify-center">
                                            <Crown v-if="user.type === 'super-admin' || user.roles?.[0]?.name.toLowerCase().includes('admin')" :size="14" />
                                            <Shield v-else :size="14" />
                                        </span>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ getRoleLabel(user) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" :checked="user.status === 'active'" class="sr-only peer" @change="toggleStatus(user)">
                                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <div class="flex justify-end items-center gap-2">
                                        <Link :href="admin.users.edit ? admin.users.edit.url(user.id) : '#'" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all flex items-center justify-center">
                                            <Pencil :size="14" />
                                        </Link>
                                        <button @click="confirmDelete(user)" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all flex items-center justify-center">
                                            <Trash2 :size="14" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                 <div v-if="users.links.length > 3" class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                    <!-- Simple pagination implementation -->
                     <div class="flex space-x-1">
                        <Link v-for="(link, key) in users.links" :key="key" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded-lg transition-colors border border-transparent" :class="link.active ? 'bg-brand-600 text-white' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700'" :preserve-state="true" />
                     </div>
                 </div>
            </div>
            
            <ConfirmationModal 
                :show="showDeleteModal" 
                :title="__('Delete Member?')"
                :message="__('Are you sure you want to delete this member? This action cannot be undone.')"
                :confirmText="__('Yes, Delete Member')"
                :cancelText="__('No, Cancel')"
                type="danger"
                :processing="processingDelete"
                @close="showDeleteModal = false"
                @confirm="handleDelete"
            />
        </div>
    </AdminLayout>
</template>
