<script setup lang="ts">
import { login, register } from '@/routes';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import Toast from '@/Components/Common/Toast.vue';
import { computed, ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    captcha: '',
});

const captchaUrl = ref('/captcha');
const refreshCaptcha = () => {
    captchaUrl.value = '/captcha?t=' + Date.now();
};

const page = usePage();
const appName = computed(() => page.props.branding?.business_settings?.business_name || page.props.name || 'Laravel');
const logoUrl = computed(() => {
    const value = page.props.branding?.business_settings?.logo_url;
    if (!value) {
        return null;
    }

    if (value.startsWith('http') || value.startsWith('/storage/')) {
        return value;
    }

    return `/storage/${value}`;
});

const isAuthenticating = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    isAuthenticating.value = true;
    form.post(register.url(), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
            isAuthenticating.value = false;
        },
        onError: () => {
            isAuthenticating.value = false;
            refreshCaptcha();
        },
    });
};
</script>

<template>
    <Head :title="`Register | ${appName}`" />
    <Toast />

    <div
        class="flex min-h-screen flex-col bg-white font-sans text-slate-800 antialiased transition-colors duration-300 lg:flex-row dark:bg-gray-900 dark:text-slate-200"
    >
        <!-- Left Side: Professional Branding -->
        <div class="mesh-gradient relative flex flex-col justify-center overflow-hidden p-12 text-white lg:w-[45%]">
            <!-- Abstract Pattern Overlay -->
            <div
                class="absolute inset-0 opacity-20"
                style="background-image: radial-gradient(#ffffff 0.5px, transparent 0.5px); background-size: 24px 24px"
            ></div>

            <!-- Top Branding -->
            <div class="relative z-10">
                <div class="mb-10 flex w-full flex-col items-center justify-center">
                    <Link
                        href="/"
                        class="mb-4 inline-flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl border border-white/20 bg-white/10 text-3xl text-white backdrop-blur-md"
                    >
                        <img v-if="logoUrl" :src="logoUrl" class="h-full w-full object-cover" />
                        <svg v-else class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 576 512">
                            <path
                                d="M0 80C0 53.5 21.5 32 48 32h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80zM64 96v64h64V96H64zM0 336c0-26.5 21.5-48 48-48h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V336zM64 352v64h64v-64H64zM309.7 185.3l-147.9 29.8C154.2 216.7 148 221.7 148 228v56c0 8.8 7.2 16 16 16h32c6.9 0 13.1-4.2 15.3-10.7l22-66H310l34.8 104.5c4.8 14.5-9.6 28.2-22.8 28.2H320c-8.8 0-16 7.2-16 16V392c0 8.8 7.2 16 16 16h32c12 0 22.4-8.2 25.1-19.9l32.3-139c1.9-8.3-5.2-16-13.8-16h-42.5l-23.4-70.3c-2.8-8.4-11.8-13.3-20.2-11.5l-63.5 12.7c36.4 7.2 63.8 39.2 63.8 77.2v.2c0 7.3-1.1 14.3-3 21.1zM480 32c-26.5 0-48 21.5-48 48v96c0 26.5 21.5 48 48 48h96c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48H480zm16 144v-64h64v64H496zm-16 112c-26.5 0-48 21.5-48 48v96c0 26.5 21.5 48 48 48h96c26.5 0 48-21.5 48-48V336c0-26.5-21.5-48-48-48H480zm16 144v-64h64v64H496z"
                            />
                        </svg>
                    </Link>
                    <div class="text-2xl font-bold tracking-tight text-white lg:text-3xl">
                        {{ appName }}
                    </div>
                </div>

                <div class="animate-in">
                    <h1 class="mb-4 text-3xl leading-[1.2] font-extrabold lg:text-4xl">All your invoices, in one place.</h1>
                    <p class="mb-6 max-w-md text-base leading-relaxed text-slate-200 lg:text-lg">
                        View invoices, track payment status, and download receipts—securely and fast.
                    </p>
                    <!-- Feature Bullets -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-primary-300" fill="currentColor" viewBox="0 0 512 512">
                                <path
                                    d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"
                                />
                            </svg>
                            <span class="font-medium text-slate-200">All invoices in one dashboard</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-primary-300" fill="currentColor" viewBox="0 0 512 512">
                                <path
                                    d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"
                                />
                            </svg>
                            <span class="font-medium text-slate-200">Secure payments & receipts</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-primary-300" fill="currentColor" viewBox="0 0 512 512">
                                <path
                                    d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"
                                />
                            </svg>
                            <span class="font-medium text-slate-200">Access from any device</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer: System Stats -->
            <div class="relative z-10 mt-12 flex items-center space-x-8 border-t border-white/10 pt-8 text-sm text-slate-300">
                <div class="flex items-center space-x-2">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                    <span>{{ __($page.props.branding.register?.registration_status) }}</span>
                </div>
                <span>{{ __($page.props.branding.register?.security_badge) }}</span>
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="relative flex items-center justify-center bg-white p-8 transition-colors duration-300 lg:w-1/2 lg:p-12 dark:bg-gray-900">
            <div class="absolute top-4 right-4 flex items-center space-x-2"></div>

            <div class="w-full max-w-md">
                <div class="mb-10">
                    <h2 class="mb-2 text-2xl font-extrabold text-slate-900 transition-colors dark:text-white">
                        {{ __($page.props.branding.register?.form_title) }}
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __($page.props.branding.register?.form_subtitle) }}</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="name">{{
                            __('Full Name')
                        }}</label>
                        <div class="group relative">
                            <input
                                id="name"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('John Doe')"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                            />
                        </div>
                        <div v-if="form.errors.name" class="mt-2 ml-1 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="email">{{
                            __('Work Email')
                        }}</label>
                        <div class="group relative">
                            <input
                                id="email"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('name@company.com')"
                                v-model="form.email"
                                required
                                autocomplete="username"
                            />
                        </div>
                        <div v-if="form.errors.email" class="mt-2 ml-1 text-sm text-red-600">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="password">{{
                            __('Password')
                        }}</label>
                        <div class="group relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('••••••••')"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition-colors hover:text-slate-600 focus:outline-none dark:hover:text-slate-300"
                            >
                                <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    ></path>
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-2 ml-1 text-sm text-red-600">{{ form.errors.password }}</div>
                    </div>

                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="password_confirmation">{{
                            __('Confirm Password')
                        }}</label>
                        <div class="group relative">
                            <input
                                :type="showConfirmPassword ? 'text' : 'password'"
                                id="password_confirmation"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('••••••••')"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition-colors hover:text-slate-600 focus:outline-none dark:hover:text-slate-300"
                            >
                                <svg v-if="!showConfirmPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    ></path>
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password_confirmation" class="mt-2 ml-1 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="captcha">{{
                            __('Enter Captcha')
                        }}</label>
                        <div class="flex items-center space-x-4">
                            <div class="group relative flex-1">
                                <input
                                    id="captcha"
                                    type="text"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                    :placeholder="__('1234')"
                                    v-model="form.captcha"
                                    required
                                />
                            </div>
                            <div
                                class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <img :src="captchaUrl" alt="captcha" class="h-10 w-24 object-contain" />
                                <button
                                    type="button"
                                    @click="refreshCaptcha"
                                    class="absolute inset-0 flex items-center justify-center bg-black/5 opacity-0 transition-opacity hover:opacity-100"
                                    title="Refresh Captcha"
                                >
                                    <svg class="h-5 w-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                        ></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-if="form.errors.captcha" class="mt-2 ml-1 text-sm text-red-600">{{ form.errors.captcha }}</div>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing || isAuthenticating"
                            :class="{ 'btn-loading': form.processing || isAuthenticating }"
                            class="flex w-full transform items-center justify-center space-x-2 rounded-2xl bg-primary-600 py-3 font-bold text-white shadow-xl shadow-primary-200 transition-all hover:bg-primary-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-75 dark:shadow-none"
                        >
                            <span v-if="form.processing || isAuthenticating">{{ __('Creating Account...') }}</span>
                            <span v-else>{{ __('Create Account') }}</span>
                            <svg v-if="!(form.processing || isAuthenticating)" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                            <svg
                                v-else
                                class="ml-2 h-4 w-4 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Helper Footer -->
                <div class="mt-8 flex flex-col items-center space-y-4 border-t border-slate-50 pt-8 dark:border-gray-800">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Already have an account?') }}
                        <Link
                            :href="login.url()"
                            class="font-bold text-primary-600 transition-colors hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                            >{{ __('Sign In') }}</Link
                        >
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

.mesh-gradient {
    position: relative;
    background:
        radial-gradient(1000px circle at 15% 15%, rgba(255, 255, 255, 0.14), transparent 50%),
        radial-gradient(800px circle at 85% 10%, rgba(255, 255, 255, 0.08), transparent 50%),
        linear-gradient(135deg, var(--color-primary-900) 0%, var(--color-primary-700) 45%, var(--color-primary-600) 100%);
}

.mesh-gradient::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255, 255, 255, 0.2) 1px, transparent 1px);
    background-size: 18px 18px;
    opacity: 0.08;
    mix-blend-mode: soft-light;
    pointer-events: none;
}

.glass-panel {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.input-transition {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-loading {
    pointer-events: none;
    opacity: 0.8;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-in {
    animation: slideIn 0.5s ease-out forwards;
}
</style>
