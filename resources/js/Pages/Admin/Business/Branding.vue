<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import { update } from '@/actions/App/Http/Controllers/Admin/BusinessSettingController';
import SelectInput from '@/Components/Common/SelectInput.vue';
import { 
    Palette, Share2, Shield, LayoutDashboard, Users, Briefcase, 
    Menu, Moon, Sun, ArrowLeftRight, Save, Camera, Link as LinkIcon, 
    Upload, Info, Mail, Phone, MapPin, Facebook, Twitter, Instagram, 
    CheckCircle, Globe
} from 'lucide-vue-next';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({}),
    },
    default_colors: {
        type: Object,
        default: () => ({
            primary: '#4f7fa6',
            primary_light: '#e9f1f7',
            primary_dark_text: '#b9d3e6',
            secondary: '#6a747d',
            admin: {
                sidebar_rail_bg: '#2f4f66',
                sidebar_rail_bg_dark: '#213746',
                sidebar_icon_color: '#ffffffff',
                sidebar_icon_color_dark: '#ffffffff',
            }
        }),
    },
});

const form = useForm({
    business_name: props.settings.business_name || 'Nexus Global Corp',
    tagline: props.settings.tagline || 'Innovating the future of enterprise',
    industry: props.settings.industry || 'Technology & SaaS',
    
    logo_url: props.settings.logo_url || 'https://api.dicebear.com/7.x/initials/svg?seed=Nexus&backgroundColor=0284c7',
    favicon_url: props.settings.favicon_url || 'https://api.dicebear.com/7.x/initials/svg?seed=N',
    cover_url: props.settings.cover_url || 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=2000',
    
    primary: props.settings.primary || props.default_colors.primary,
    primary_light: props.settings.primary_light || props.default_colors.primary_light,
    primary_dark_text: props.settings.primary_dark_text || props.default_colors.primary_dark_text,

    secondary: props.settings.secondary || props.default_colors.secondary,
    
    sidebar_rail_bg: props.settings.sidebar_rail_bg || props.default_colors.admin.sidebar_rail_bg,
    sidebar_rail_bg_dark: props.settings.sidebar_rail_bg_dark || props.default_colors.admin.sidebar_rail_bg_dark,
    sidebar_icon_color: props.settings.sidebar_icon_color || props.default_colors.admin.sidebar_icon_color,
    sidebar_icon_color_dark: props.settings.sidebar_icon_color_dark || props.default_colors.admin.sidebar_icon_color_dark,
    
    contact_email: props.settings.contact_email || 'contact@nexus-global.io',
    contact_phone: props.settings.contact_phone || '+1 (555) 000-1234',
    contact_address: props.settings.contact_address || 'San Francisco, CA 94103',
    
    social_facebook: props.settings.social_facebook || '',
    social_twitter: props.settings.social_twitter || '',
    social_instagram: props.settings.social_instagram || '',
});

// File inputs handling
const logoInput = ref<HTMLInputElement | null>(null);
const coverInput = ref<HTMLInputElement | null>(null);

const handleFileChange = (event: Event, key: keyof typeof form) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        const url = URL.createObjectURL(file);
        
        form[key] = file as any;
        previews.value[key] = url;
    }
};

const previews = ref<Record<string, string>>({
    logo_url: props.settings.logo_url || form.logo_url,
    cover_url: props.settings.cover_url || form.cover_url,
    favicon_url: props.settings.favicon_url || form.favicon_url,
});

watch(() => props.settings, (newSettings) => {
    form.business_name = newSettings.business_name || 'Nexus Global Corp';
    form.tagline = newSettings.tagline || 'Innovating the future of enterprise';
    form.industry = newSettings.industry || 'Technology & SaaS';
    form.logo_url = newSettings.logo_url || 'https://api.dicebear.com/7.x/initials/svg?seed=Nexus&backgroundColor=0284c7';
    form.favicon_url = newSettings.favicon_url || 'https://api.dicebear.com/7.x/initials/svg?seed=N';
    form.cover_url = newSettings.cover_url || 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=2000';
    
    form.primary = newSettings.primary || props.default_colors.primary;
    form.primary_light = newSettings.primary_light || props.default_colors.primary_light;
    form.primary_dark_text = newSettings.primary_dark_text || props.default_colors.primary_dark_text;
    
    form.secondary = newSettings.secondary || props.default_colors.secondary;
    
    form.sidebar_rail_bg = newSettings.sidebar_rail_bg || props.default_colors.admin.sidebar_rail_bg;
    form.sidebar_rail_bg_dark = newSettings.sidebar_rail_bg_dark || props.default_colors.admin.sidebar_rail_bg_dark;
    form.sidebar_icon_color = newSettings.sidebar_icon_color || props.default_colors.admin.sidebar_icon_color;
    form.sidebar_icon_color_dark = newSettings.sidebar_icon_color_dark || props.default_colors.admin.sidebar_icon_color_dark;

    form.contact_email = newSettings.contact_email || 'contact@nexus-global.io';
    form.contact_phone = newSettings.contact_phone || '+1 (555) 000-1234';
    form.contact_address = newSettings.contact_address || 'San Francisco, CA 94103';
    form.social_facebook = newSettings.social_facebook || '';
    form.social_twitter = newSettings.social_twitter || '';
    form.social_instagram = newSettings.social_instagram || '';

    previews.value = {
        logo_url: newSettings.logo_url || form.logo_url,
        cover_url: newSettings.cover_url || form.cover_url,
        favicon_url: newSettings.favicon_url || form.favicon_url,
    };
}, { deep: true });

const submit = () => {
    form.post(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            // Toast handled by backend flash usually, but we can do local success too
        },
    });
};

// UI Helpers
const triggerInput = (id: string) => {
    document.getElementById(id)?.click();
};

const triggerFileInput = (refName: 'logoInput' | 'coverInput') => {
    if (refName === 'logoInput') logoInput.value?.click();
    if (refName === 'coverInput') coverInput.value?.click();
};

</script>

<template>
    <Head :title="__('Business Branding')" />

    <AdminLayout>
        <div class="h-full">
            
            <!-- BUSINESS PANE -->
            <div class="space-y-6 max-w-6xl mx-auto animate-fade-in">
                
                <!-- Header -->
                 <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ __('business_branding') }}</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Manage your organization\'s visual identity and public profile.') }}</p>
                    </div>
                    <div class="flex gap-3">
                         <button @click="submit" :disabled="form.processing" class="text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg transition-all flex items-center gap-2 hover:opacity-90 disabled:opacity-70" :style="{ backgroundColor: 'var(--brand-primary)', boxShadow: '0 10px 15px -3px var(--brand-primary-transparent, rgba(0,0,0,0.1))' }">
                            <Save v-if="!form.processing" :size="18" />
                            <span v-if="form.processing">{{ __('Saving...') }}</span>
                            <span v-else>{{ __('Save Changes') }}</span>
                        </button>
                    </div>
                </div>

                <!-- 1. HEADER & COVER SECTION -->
                <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all duration-300">
                    <div class="relative h-64 bg-slate-200 dark:bg-slate-800 group">
                        <img :src="previews.cover_url" class="w-full h-full object-cover transition-opacity duration-500">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                            <button @click="triggerFileInput('coverInput')" class="bg-white/20 backdrop-blur-md text-white px-6 py-2 rounded-full font-bold border border-white/30 hover:bg-white/40 transition-all flex items-center gap-2">
                                <Camera :size="18" /> {{ __('Change Cover') }}
                            </button>
                            <input type="file" ref="coverInput" class="hidden" accept="image/*" @change="(e) => handleFileChange(e, 'cover_url')">
                        </div>
                        <div class="absolute -bottom-6 inset-x-10 flex items-end space-x-6 rtl:space-x-reverse">
                            <div class="relative group/logo">
                                <img :src="previews.logo_url" class="w-32 h-32 rounded-[2rem] border-4 border-white dark:border-slate-900 shadow-2xl bg-white dark:bg-slate-800 object-contain p-2">
                                <button @click="triggerFileInput('logoInput')" class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-[2rem] opacity-0 group-hover/logo:opacity-100 transition-all text-white text-xs font-bold cursor-pointer">
                                    {{ __('Edit') }}
                                </button>
                                <input type="file" ref="logoInput" class="hidden" accept="image/*" @change="(e) => handleFileChange(e, 'logo_url')">
                            </div>
                            <div class="mb-14 text-start">
                                <h3 class="text-2xl font-black text-white drop-shadow-xl">{{ form.business_name }}</h3>
                                <p class="text-white/80 font-medium text-sm drop-shadow-md">{{ form.tagline }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="pt-16 pb-6 px-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-2 block text-start">{{ __('Business Name') }}</label>
                                <input v-model="form.business_name" type="text" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-2 block text-start">{{ __('Tagline / Slogan') }}</label>
                                <input v-model="form.tagline" type="text" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-2 block text-start">{{ __('Industry Type') }}</label>
                                <SelectInput
                                    v-model="form.industry"
                                    :options="[
                                        { value: 'Technology & SaaS', label: __('Technology & SaaS') },
                                        { value: 'Healthcare', label: __('Healthcare') },
                                        { value: 'Finance', label: __('Finance') },
                                        { value: 'E-commerce', label: __('E-commerce') },
                                        { value: 'Retail', label: __('Retail') },
                                        { value: 'Education', label: __('Education') },
                                        { value: 'Others', label: __('Others') },
                                    ]"
                                    :button-class="'px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all'"
                                    :list-class="'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'"
                                    :option-class="'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. CONTACT & SOCIAL -->
                <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300 p-5">
                    <h4 class="text-base font-black text-slate-800 dark:text-white mb-8 text-start flex items-center">
                        <Briefcase class="me-4" :size="20" :style="{ color: 'var(--brand-primary)' }" />
                        <span>{{ __('Contact & Connectivity') }}</span>
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-6">
                            <h5 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 text-start">{{ __('Business Details') }}</h5>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400"><Mail :size="16" /></div>
                                    <input v-model="form.contact_email" type="email" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400"><Phone :size="16" /></div>
                                    <input v-model="form.contact_phone" type="text" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400"><MapPin :size="16" /></div>
                                    <input v-model="form.contact_address" type="text" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <h5 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 text-start">{{ __('Social Presence') }}</h5>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center" :style="{ color: 'var(--brand-primary)', backgroundColor: 'color-mix(in srgb, var(--brand-primary), transparent 90%)' }"><Facebook :size="16" /></div>
                                    <input v-model="form.social_facebook" type="text" :placeholder="__('Facebook Profile URL')" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all text-xs" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/20 flex items-center justify-center" :style="{ color: 'var(--brand-primary)', backgroundColor: 'color-mix(in srgb, var(--brand-primary), transparent 90%)' }"><Twitter :size="16" /></div>
                                    <input v-model="form.social_twitter" type="text" :placeholder="__('Twitter Profile URL')" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all text-xs" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-pink-50 dark:bg-pink-900/20 flex items-center justify-center" :style="{ color: 'var(--brand-primary)', backgroundColor: 'color-mix(in srgb, var(--brand-primary), transparent 90%)' }"><Instagram :size="16" /></div>
                                    <input v-model="form.social_instagram" type="text" :placeholder="__('Instagram Profile URL')" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all text-xs" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. VISUAL ASSETS & COLORS -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Assets -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300 p-5 space-y-5">
                        <h4 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center">
                            <LinkIcon class="me-3" :size="16" :style="{ color: 'var(--brand-primary)' }" /> {{ __('Asset Resources') }}
                        </h4>
                        <div class="space-y-5">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-2 block text-start">{{ __('Logo URL') }}</label>
                                <div class="flex gap-2">
                                    <input v-model="form.logo_url" type="text" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all text-xs" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-2 block text-start">{{ __('Favicon URL') }}</label>
                                <div class="flex gap-2 items-center">
                                    <input v-model="form.favicon_url" type="text" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all text-xs" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                                    <div class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 flex-shrink-0 flex items-center justify-center p-1 bg-white dark:bg-slate-800">
                                        <img :src="form.favicon_url" class="w-full h-full object-contain">
                                    </div>
                                </div>
                            </div>
                             <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-2 block text-start">{{ __('Hero Cover URL') }}</label>
                                <div class="flex gap-2">
                                    <input v-model="form.cover_url" type="text" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all text-xs" :style="{ '--tw-ring-color': 'var(--brand-primary)' }">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colors -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300 p-5 space-y-5">
                        <h4 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-6 flex items-center">
                            <Palette class="me-3" :size="16" :style="{ color: 'var(--brand-primary)' }" /> {{ __('Brand Colors') }}
                        </h4>
                        <div class="space-y-6">
                            <!-- Helper Text -->
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30 rounded-xl">
                                <p class="text-[11px] text-blue-600 dark:text-blue-400 flex items-start gap-2">
                                    <Info :size="14" class="mt-0.5 shrink-0" />
                                    <span>{{ __('Customize your admin panel theme. These colors affect navigation, buttons, and text contrast.') }}</span>
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Primary Palette -->
                                <div class="space-y-4">
                                    <h5 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 text-start">{{ __('Primary Palette') }}</h5>
                                    
                                    <div class="space-y-3">
                                        <!-- Primary -->
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block mb-1 text-start">{{ __('Primary Color') }}</label>
                                            <div class="flex items-center gap-3">
                                                <input v-model="form.primary" type="color" class="h-8 w-14 rounded cursor-pointer border-0 bg-transparent p-0">
                                                <input v-model="form.primary" type="text" class="w-full text-xs font-mono bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded px-2 py-1.5 outline-none focus:ring-1 focus:ring-primary">
                                            </div>
                                        </div>
                                        <!-- Primary Light -->
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block mb-1 text-start">{{ __('Primary Light (soft bg)') }}</label>
                                            <div class="flex items-center gap-3">
                                                <input v-model="form.primary_light" type="color" class="h-8 w-14 rounded cursor-pointer border-0 bg-transparent p-0">
                                                <input v-model="form.primary_light" type="text" class="w-full text-xs font-mono bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded px-2 py-1.5 outline-none focus:ring-1 focus:ring-primary">
                                            </div>
                                        </div>
                                        <!-- Primary Dark Text -->
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block mb-1 text-start">{{ __('Primary Dark Text (for dark mode)') }}</label>
                                            <div class="flex items-center gap-3">
                                                <input v-model="form.primary_dark_text" type="color" class="h-8 w-14 rounded cursor-pointer border-0 bg-transparent p-0">
                                                <input v-model="form.primary_dark_text" type="text" class="w-full text-xs font-mono bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded px-2 py-1.5 outline-none focus:ring-1 focus:ring-primary">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Secondary & Sidebar -->
                                <div class="space-y-4">
                                     <h5 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 text-start">{{ __('Secondary & Sidebar') }}</h5>
                                    
                                     <div class="space-y-3">
                                        <!-- Secondary -->
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 block mb-1 text-start">{{ __('Secondary Color') }}</label>
                                            <div class="flex items-center gap-3">
                                                <input v-model="form.secondary" type="color" class="h-8 w-14 rounded cursor-pointer border-0 bg-transparent p-0">
                                                <input v-model="form.secondary" type="text" class="w-full text-xs font-mono bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded px-2 py-1.5 outline-none focus:ring-1 focus:ring-primary">
                                            </div>
                                        </div>
                                        
                                        <!-- Sidebar Rail -->
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-[9px] font-bold text-slate-500 dark:text-slate-400 block mb-1 text-start">{{ __('Sidebar Rail BG') }}</label>
                                                <div class="flex items-center gap-2">
                                                    <input v-model="form.sidebar_rail_bg" type="color" class="h-6 w-8 rounded cursor-pointer border-0 bg-transparent p-0">
                                                    <input v-model="form.sidebar_rail_bg" type="text" class="w-full text-[10px] font-mono bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-primary">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-[9px] font-bold text-slate-500 dark:text-slate-400 block mb-1 text-start">{{ __('Rail BG (Dark)') }}</label>
                                                <div class="flex items-center gap-2">
                                                    <input v-model="form.sidebar_rail_bg_dark" type="color" class="h-6 w-8 rounded cursor-pointer border-0 bg-transparent p-0">
                                                    <input v-model="form.sidebar_rail_bg_dark" type="text" class="w-full text-[10px] font-mono bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-primary">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sidebar Icons -->
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-[9px] font-bold text-slate-500 dark:text-slate-400 block mb-1 text-start">{{ __('Sidebar Icon') }}</label>
                                                <div class="flex items-center gap-2">
                                                    <input v-model="form.sidebar_icon_color" type="color" class="h-6 w-8 rounded cursor-pointer border-0 bg-transparent p-0">
                                                    <input v-model="form.sidebar_icon_color" type="text" class="w-full text-[10px] font-mono bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-primary">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-[9px] font-bold text-slate-500 dark:text-slate-400 block mb-1 text-start">{{ __('Icon (Dark)') }}</label>
                                                <div class="flex items-center gap-2">
                                                    <input v-model="form.sidebar_icon_color_dark" type="color" class="h-6 w-8 rounded cursor-pointer border-0 bg-transparent p-0">
                                                    <input v-model="form.sidebar_icon_color_dark" type="text" class="w-full text-[10px] font-mono bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-primary">
                                                </div>
                                            </div>
                                        </div>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
