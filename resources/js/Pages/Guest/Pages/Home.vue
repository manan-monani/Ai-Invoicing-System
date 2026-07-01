<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';


const openFaq = ref<number | null>(null);
const toggleFaq = (index: number | string) => {
    const idx = typeof index === 'string' ? parseInt(index) : index;
    openFaq.value = openFaq.value === idx ? null : idx;
};
</script>

<template>
    <PublicLayout>
        <div class="bg-gray-50 dark:bg-gray-900 min-h-screen font-sans text-gray-900 dark:text-gray-100 transition-colors duration-300 -mx-4 sm:-mx-6 lg:-mx-8 -my-12">


        <!-- Hero Section -->
        <section class="relative pt-20 pb-32 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-primary-50 text-primary-700 text-sm font-medium mb-8 animate-fade-in-up">
                    <span class="flex h-2 w-2 rounded-full bg-primary-600 mr-2"></span>
                    {{ __($page.props.branding.home?.hero?.badge) }}
                </div>
                <h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-8 leading-tight">
                    {{ __($page.props.branding.home?.hero?.title_line_1) }} <br />
                    <span class="text-primary-600 relative">
                        {{ __($page.props.branding.home?.hero?.title_line_2) }}
                        <svg class="absolute w-full h-3 -bottom-1 left-0 text-primary-200 -z-10" viewBox="0 0 100 10" preserveAspectRatio="none">
                             <path d="M0 5 Q 50 10 100 5 L 100 10 L 0 10 Z" fill="currentColor"/>
                        </svg>
                    </span>
                </h1>
                <p class="max-w-2xl mx-auto text-xl text-gray-500 mb-10 leading-relaxed">
                    {{ __($page.props.branding.home?.hero?.description) }}
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4 mb-16">
                    <Link :href="register.url()" class="px-8 py-4 bg-primary-600 text-white rounded-xl font-bold text-lg hover:bg-primary-700 transition-all shadow-xl shadow-primary-500/30 transform hover:-translate-y-1">
                        {{ __($page.props.branding.home?.hero?.cta_primary) }}
                    </Link>
                    <a href="#video-showcase" class="px-8 py-4 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold text-lg hover:bg-gray-50 transition-all hover:border-gray-300">
                        {{ __($page.props.branding.home?.hero?.cta_secondary) }}
                    </a>
                </div>

                <!-- Dashboard Preview -->
                <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-gray-200 bg-white">
                     <div class="absolute inset-x-0 top-0 h-8 bg-gray-100 border-b border-gray-200 flex items-center space-x-2 px-4">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                     </div>
                     <div class="pt-8 pb-12 px-8 bg-gray-50">
                        <!-- Mock Dashboard UI -->
                        <div class="grid grid-cols-12 gap-6 text-left">
                            <div class="col-span-3 bg-white h-64 rounded-xl shadow-sm"></div>
                            <div class="col-span-9 space-y-6">
                                <div class="grid grid-cols-3 gap-6">
                                    <div class="bg-white h-32 rounded-xl shadow-sm"></div>
                                    <div class="bg-white h-32 rounded-xl shadow-sm"></div>
                                    <div class="bg-white h-32 rounded-xl shadow-sm"></div>
                                </div>
                                <div class="bg-white h-96 rounded-xl shadow-sm"></div>
                            </div>
                        </div>
                     </div>
                     <div class="absolute inset-0 bg-gradient-to-t from-gray-50/50 to-transparent pointer-events-none"></div>
                </div>
            </div>
            
             <!-- Background Decorations -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10 pointer-events-none">
                <div class="absolute top-20 left-10 w-72 h-72 bg-primary-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
                <div class="absolute top-20 right-10 w-72 h-72 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-32 left-1/4 w-72 h-72 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
            </div>
        </section>

        <!-- Video Showcase Section -->
        <section id="video-showcase" class="py-24 bg-white relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __($page.props.branding.home?.video?.title) }}</h2>
                    <p class="text-gray-500 max-w-2xl mx-auto">{{ __($page.props.branding.home?.video?.description) }}</p>
                </div>
                <div class="relative aspect-video rounded-2xl overflow-hidden shadow-2xl bg-gray-900 group cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-primary-600 translate-x-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                     <!-- Placeholder for actual video thumbnail -->
                    <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 opacity-50"></div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ __($page.props.branding.home?.features?.title) }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">{{ __($page.props.branding.home?.features?.description) }}</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="(feature, index) in $page.props.branding.home?.features?.items" :key="index" class="p-8 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:shadow-gray-200/50 dark:hover:shadow-black/30 transition-all duration-300 group">
                        <div class="w-12 h-12 bg-primary-50 dark:bg-primary-900/20 rounded-xl flex items-center justify-center text-2xl mb-6 shadow-sm group-hover:scale-110 transition-transform text-primary-600 dark:text-primary-400">
                            {{ feature.icon }}
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ __(feature.title) }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ __(feature.description) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-24 bg-white dark:bg-gray-800 transition-colors duration-300">
             <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ __($page.props.branding.home?.testimonials?.title) }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">{{ __($page.props.branding.home?.testimonials?.description) }}</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                     <div v-for="(testimonial, index) in $page.props.branding.home?.testimonials?.items" :key="index" class="p-8 rounded-2xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700">
                        <p class="text-gray-600 dark:text-gray-300 italic mb-6">"{{ __(testimonial.content) }}"</p>
                        <div class="flex items-center">
                            <img :src="testimonial.avatar" class="w-10 h-10 rounded-full mr-4" :alt="testimonial.name">
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ testimonial.name }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __(testimonial.role) }}</p>
                            </div>
                        </div>
                     </div>
                </div>
             </div>
        </section>

        <!-- Pricing Section -->
        <section class="py-24 bg-gray-900 dark:bg-black text-white overflow-hidden relative transition-colors duration-300">
             <!-- Background effects -->
             <div class="absolute top-0 left-0 w-full h-full overflow-hidden">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-primary-900/20 rounded-full blur-[100px]"></div>
             </div>

             <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold mb-4">{{ __($page.props.branding.home?.pricing?.title) }}</h2>
                    <p class="text-gray-400 max-w-2xl mx-auto">{{ __($page.props.branding.home?.pricing?.description) }}</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8 items-center">
                    <div v-for="(plan, index) in $page.props.branding.home?.pricing?.plans" :key="index" 
                        class="p-8 rounded-2xl border transition-all duration-300 relative"
                        :class="plan.highlight ? 'bg-primary-600 border-primary-500 shadow-2xl scale-105 z-10' : 'bg-gray-800 dark:bg-gray-900 border-gray-700 hover:border-gray-600'"
                    >
                         <h3 class="text-xl font-bold mb-2">{{ __(plan.name) }}</h3>
                         <div class="text-4xl font-bold mb-6">{{ plan.price }}<span class="text-lg font-normal opacity-70">/mo</span></div>
                         <ul class="space-y-4 mb-8">
                             <li v-for="feature in plan.features" :key="feature" class="flex items-center">
                                 <svg class="w-5 h-5 mr-3" :class="plan.highlight ? 'text-white' : 'text-primary-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                 </svg>
                                 {{ __(feature) }}
                             </li>
                         </ul>
                         <button class="w-full py-3 rounded-lg font-bold transition-colors"
                            :class="plan.highlight ? 'bg-white text-primary-600 hover:bg-gray-50' : 'bg-gray-700 hover:bg-gray-600 text-white'"
                         >
                             {{ __(plan.cta) }}
                         </button>
                    </div>
                </div>
             </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-24 bg-white dark:bg-gray-800 transition-colors duration-300">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ __($page.props.branding.home?.faq?.title) }}</h2>
                </div>
                <div class="space-y-4">
                    <div v-for="(faq, index) in $page.props.branding.home?.faq?.items" :key="index" class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <button @click="toggleFaq(index)" class="w-full px-6 py-4 text-left font-bold text-gray-900 dark:text-white flex justify-between items-center bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            {{ __(faq.question) }}
                            <svg class="w-5 h-5 transform transition-transform" :class="openFaq === index ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-show="openFaq === index" class="px-6 py-4 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-t border-gray-200 dark:border-gray-700">
                            {{ __(faq.answer) }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        </div>
    </PublicLayout>
</template>

<style scoped>
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
</style>
