<script setup lang="ts">
import Toast from '@/Components/Common/Toast.vue';
import { login, register } from '@/routes';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();

const page = usePage();
const appName = computed(() => page.props.branding?.business_settings?.business_name || page.props.name || 'Laravel');
const appMode = computed(() => (page.props as any).app?.mode);
const isDemoMode = computed(() => appMode.value === 'demo');
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

const form = useForm({
    email: '',
    password: '',
    captcha: '',
    remember: false,
});

const captchaUrl = ref('/captcha');
const refreshCaptcha = () => {
    captchaUrl.value = '/captcha?t=' + Date.now();
};

const showPassword = ref(false);
const isAuthenticating = ref(false);

const submit = () => {
    isAuthenticating.value = true;
    form.post(login.url(), {
        onFinish: () => {
            form.reset('password');
            isAuthenticating.value = false;
        },
        onError: () => {
            isAuthenticating.value = false;
            refreshCaptcha();
        },
    });
};

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const applyDemoAccount = async () => {
    form.email = 'customer@example.com';
    form.password = '12345678';

    try {
        const response = await fetch('/captcha-code');
        const data = await response.json();
        if (data.code) {
            form.captcha = data.code;
        }
    } catch (error) {
        console.error('Failed to fetch captcha code:', error);
    }
};
</script>

<template>
    <Head :title="`${appName} | Login`" />
    <Toast />

    <div
        class="flex min-h-screen flex-col bg-white font-sans text-slate-700 transition-colors duration-300 lg:flex-row dark:bg-gray-900 dark:text-slate-300"
    >
        <!-- Left Side: System Branding/Information -->
        <div class="branded-bg relative flex flex-col items-center justify-center overflow-hidden p-12 text-white lg:w-1/2">
            <!-- Decorative Shapes -->
            <div class="pointer-events-none absolute top-0 left-0 h-full w-full opacity-10">
                <div class="animate-float absolute -top-24 -left-24 h-96 w-96 rounded-full bg-white mix-blend-overlay blur-3xl filter"></div>
                <div
                    class="animate-float absolute right-0 bottom-1/4 h-64 w-64 rounded-full bg-white mix-blend-overlay blur-3xl filter"
                    style="animation-delay: 2s"
                ></div>
            </div>

            <div class="relative z-10 max-w-lg text-left">
                <div class="mb-6 flex w-full flex-col items-center justify-center">
                    <Link
                        href="/"
                        class="mb-4 inline-flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl border border-white/20 bg-white/10 text-3xl text-white backdrop-blur-md"
                    >
                        <img v-if="logoUrl" :src="logoUrl" class="h-full w-full object-cover" />
                        <svg v-else class="h-8 w-8" fill="currentColor" viewBox="0 0 320 512">
                            <path
                                d="M296 160H180.6l42.6-129.8C227.2 15 215.7 0 200 0H56C44 0 33.8 8.9 32.2 20.8l-32 240C-1.7 275.2 25.2 288 40 288h115.4l-42.6 129.8C108.8 434.9 120.3 448 136 448h144c12 0 22.2-8.9 23.8-20.8l32-240c1.9-14.3-9.2-27.2-24-27.2zM250.7 220.3l-32 240c-.6 4.3-4.1 7.7-8.7 7.7h-144c-4.1 0-7.7-2.9-8.4-7L100.2 332H40c-4.1 0-7.7-2.9-8.4-7L-11.4 69.8c-.8-5.8 3.7-10.9 9.5-10.9h144c4.1 0 7.7 2.9 8.4 7L167.8 196H228c4.1 0 7.7 2.9 8.4 7l24.3 15.3z"
                            />
                        </svg>
                    </Link>
                    <div class="text-2xl font-bold tracking-tight text-white lg:text-3xl">
                        {{ appName }}
                    </div>
                </div>
                <h1 class="mb-4 text-3xl leading-tight font-bold lg:text-4xl">All your invoices, in one place.</h1>
                <p class="mb-6 text-base leading-relaxed font-light text-primary-100 lg:text-lg">
                    View invoices, track payment status, and download receipts—securely and fast.
                </p>
                <!-- Feature Bullets -->
                <div class="mb-12 space-y-3">
                    <div class="flex items-start space-x-3">
                        <svg class="mt-1 h-5 w-5 shrink-0 text-primary-300" fill="currentColor" viewBox="0 0 512 512">
                            <path
                                d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"
                            />
                        </svg>
                        <span class="font-medium text-primary-100">All invoices in one dashboard</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <svg class="mt-1 h-5 w-5 shrink-0 text-primary-300" fill="currentColor" viewBox="0 0 512 512">
                            <path
                                d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"
                            />
                        </svg>
                        <span class="font-medium text-primary-100">Secure payments & receipts</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <svg class="mt-1 h-5 w-5 shrink-0 text-primary-300" fill="currentColor" viewBox="0 0 512 512">
                            <path
                                d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"
                            />
                        </svg>
                        <span class="font-medium text-primary-100">Access from any device</span>
                    </div>
                </div>
            </div>

            <!-- Footer Copyright (Left) -->
            <div class="absolute bottom-8 left-12 hidden text-sm text-primary-200/50 lg:block">
                &copy; {{ new Date().getFullYear() }} {{ appName }} Inc. All rights reserved.
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="relative flex items-center justify-center bg-white p-6 transition-colors duration-300 md:p-8 lg:w-1/2 dark:bg-gray-900">
            <div class="absolute top-4 right-4 flex items-center space-x-2"></div>

            <div class="w-full max-w-md">
                <!-- Mobile Branding (Only visible on small screens) -->
                <div class="mb-10 text-center lg:hidden">
                    <Link
                        href="/"
                        class="mb-4 inline-flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-primary-600 text-white"
                    >
                        <img v-if="logoUrl" :src="logoUrl" class="h-full w-full object-cover" />
                        <svg v-else class="h-6 w-6" fill="currentColor" viewBox="0 0 320 512">
                            <path
                                d="M296 160H180.6l42.6-129.8C227.2 15 215.7 0 200 0H56C44 0 33.8 8.9 32.2 20.8l-32 240C-1.7 275.2 25.2 288 40 288h115.4l-42.6 129.8C108.8 434.9 120.3 448 136 448h144c12 0 22.2-8.9 23.8-20.8l32-240c1.9-14.3-9.2-27.2-24-27.2zM250.7 220.3l-32 240c-.6 4.3-4.1 7.7-8.7 7.7h-144c-4.1 0-7.7-2.9-8.4-7L100.2 332H40c-4.1 0-7.7-2.9-8.4-7L-11.4 69.8c-.8-5.8 3.7-10.9 9.5-10.9h144c4.1 0 7.7 2.9 8.4 7L167.8 196H228c4.1 0 7.7 2.9 8.4 7l24.3 15.3z"
                            />
                        </svg>
                    </Link>
                    <div class="mb-2 text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400">
                        {{ appName }}
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 dark:text-white">{{ __($page.props.branding.login?.welcome_back) }}</h2>
                </div>

                <!-- Header -->
                <div class="mb-10 hidden lg:block">
                    <h2 class="mb-2 text-2xl font-bold text-slate-800 dark:text-white">{{ __($page.props.branding.login?.form_title) }}</h2>
                    <p class="text-slate-500 dark:text-slate-400">{{ __($page.props.branding.login?.form_subtitle) }}</p>
                </div>

                <div
                    v-if="status"
                    class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-600 dark:border-green-800 dark:bg-green-900/30 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="mb-2 ml-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="email">{{
                            __('Email Address')
                        }}</label>
                        <div class="group relative">
                            <span
                                class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 transition-colors duration-300 group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    ></path>
                                </svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pr-4 pl-11 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('name@company.com')"
                                v-model="form.email"
                                required
                                autofocus
                            />
                        </div>
                        <div v-if="form.errors.email" class="mt-2 ml-1 text-sm text-red-600">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <div class="mb-2 flex justify-between px-1">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300" for="password">{{ __('Password') }}</label>
                            <a
                                v-if="canResetPassword"
                                href="#"
                                class="text-xs font-bold text-primary-600 transition-colors hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                                >{{ __('Forgot?') }}</a
                            >
                        </div>
                        <div class="group relative">
                            <span
                                class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 transition-colors duration-300 group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 448 512">
                                    <path
                                        d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"
                                    />
                                </svg>
                            </span>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pr-12 pl-11 font-medium text-slate-700 transition-all duration-300 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:focus:bg-gray-800 dark:focus:ring-primary-500/20"
                                :placeholder="__('••••••••')"
                                v-model="form.password"
                                required
                            />
                            <button
                                type="button"
                                @click="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition-colors hover:text-slate-600 focus:outline-none dark:hover:text-slate-300"
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

                    <div class="flex items-center px-1">
                        <input
                            type="checkbox"
                            id="remember"
                            v-model="form.remember"
                            class="highlight-primary h-4 w-4 cursor-pointer rounded border-slate-300 bg-gray-100 text-primary-600 transition-all focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                        />
                        <label for="remember" class="ml-2 cursor-pointer text-sm font-medium text-slate-600 select-none dark:text-slate-400">{{
                            __('Stay signed in')
                        }}</label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || isAuthenticating"
                        :class="{ 'btn-loading': form.processing || isAuthenticating }"
                        class="flex w-full transform items-center justify-center space-x-2 rounded-2xl bg-primary-600 py-3 font-bold text-white shadow-xl shadow-primary-200 transition-all hover:bg-primary-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-75 dark:shadow-none"
                    >
                        <span v-if="form.processing || isAuthenticating">{{ __('Logging in...') }}</span>
                        <span v-else>{{ __('Login to Platform') }}</span>
                        <svg
                            v-if="!(form.processing || isAuthenticating)"
                            class="h-4 w-4 transition-transform group-hover:translate-x-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                        <svg
                            v-else
                            class="mr-3 -ml-1 h-5 w-5 animate-spin text-white"
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

                    <div v-if="isDemoMode" class="mt-8 space-y-4">
                        <button
                            type="button"
                            @click="applyDemoAccount"
                            class="group relative flex w-full items-center justify-between overflow-hidden rounded-xl border border-slate-200 bg-white p-4 transition-all hover:border-primary-500 hover:shadow-lg dark:border-gray-700 dark:bg-gray-900"
                        >
                            <div class="flex items-center space-x-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        ></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-bold text-slate-700 dark:text-white">Customer Account</div>
                                    <div class="text-xs text-slate-400">customer@example.com</div>
                                </div>
                            </div>
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 transition-colors group-hover:bg-primary-50 dark:bg-gray-800 dark:group-hover:bg-primary-900/50"
                            >
                                <svg
                                    class="h-4 w-4 text-slate-400 transition-colors group-hover:text-primary-600 dark:group-hover:text-primary-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"
                                    ></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </form>

                <div class="mt-6 space-y-2 text-center">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('New here?') }}
                        <Link
                            :href="register.url()"
                            class="font-semibold text-primary-600 transition-colors hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                        >
                            {{ __('Create a customer account') }}
                        </Link>
                    </div>
                    <a
                        href="/admin/login"
                        class="inline-flex items-center text-sm font-medium text-slate-500 transition-colors hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                    >
                        {{ __($page.props.branding.login?.login_as_admin) }}
                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

.branded-bg {
    position: relative;
    background:
        radial-gradient(900px circle at 12% 18%, rgba(255, 255, 255, 0.14), transparent 45%),
        radial-gradient(700px circle at 88% 8%, rgba(255, 255, 255, 0.08), transparent 45%),
        linear-gradient(135deg, var(--color-primary-900) 0%, var(--color-primary-700) 45%, var(--color-primary-600) 100%);
}

.branded-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255, 255, 255, 0.2) 1px, transparent 1px);
    background-size: 18px 18px;
    opacity: 0.08;
    mix-blend-mode: soft-light;
    pointer-events: none;
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

.input-focus {
    transition: all 0.3s ease;
}

.input-focus:focus {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.1);
}

.btn-loading {
    pointer-events: none;
    opacity: 0.8;
}
</style>
