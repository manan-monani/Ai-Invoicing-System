<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Bell, Check, CheckCheck, Trash2, X, Info, CheckCircle, AlertTriangle, AlertCircle } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps<{
    recentUrl: string;
    viewAllUrl: string;
    markAsReadUrl: (id: number) => string;
    markAllAsReadUrl: string;
    deleteUrl: (id: number) => string;
}>();

interface Notification {
    id: number;
    type: 'info' | 'success' | 'warning' | 'error';
    title: string;
    description: string | null;
    action_url: string | null;
    read_at: string | null;
    created_at: string;
}

const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);
const isOpen = ref(false);
const isLoading = ref(false);

// Fetch notifications
const fetchNotifications = async () => {
    try {
        isLoading.value = true;
        const response = await axios.get(props.recentUrl);
        notifications.value = response.data.notifications;
        unreadCount.value = response.data.unread_count;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    } finally {
        isLoading.value = false;
    }
};

// Mark as read
const markAsRead = async (id: number) => {
    try {
        await axios.post(props.markAsReadUrl(id));
        const notification = notifications.value.find(n => n.id === id);
        if (notification) {
            notification.read_at = new Date().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
    } catch (error) {
        console.error('Failed to mark notification as read:', error);
    }
};

// Mark all as read
const markAllAsRead = async () => {
    try {
        await axios.post(props.markAllAsReadUrl);
        notifications.value.forEach(n => {
            n.read_at = new Date().toISOString();
        });
        unreadCount.value = 0;
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
};

// Delete notification
const deleteNotification = async (id: number) => {
    try {
        await axios.delete(props.deleteUrl(id));
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            const wasUnread = !notifications.value[index].read_at;
            notifications.value.splice(index, 1);
            if (wasUnread) {
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        }
    } catch (error) {
        console.error('Failed to delete notification:', error);
    }
};

// Get icon for notification type
const getTypeIcon = (type: string) => {
    switch (type) {
        case 'success': return CheckCircle;
        case 'warning': return AlertTriangle;
        case 'error': return AlertCircle;
        default: return Info;
    }
};

// Get color classes for notification type
const getTypeClasses = (type: string) => {
    switch (type) {
        case 'success': return 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20';
        case 'warning': return 'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20';
        case 'error': return 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20';
        default: return 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20';
    }
};

// Format time ago
const timeAgo = (date: string) => {
    const now = new Date();
    const past = new Date(date);
    const seconds = Math.floor((now.getTime() - past.getTime()) / 1000);
    
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
    return past.toLocaleDateString();
};

// Toggle dropdown
const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && notifications.value.length === 0) {
        fetchNotifications();
    }
};

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.notification-dropdown')) {
        isOpen.value = false;
    }
};

onMounted(() => {
    fetchNotifications();
    document.addEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative notification-dropdown">
        <!-- Bell Button -->
        <button
            @click="toggleDropdown"
            class="relative p-1.5 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            :class="{ 'bg-gray-100 dark:bg-gray-700': isOpen }"
        >
            <Bell :size="20" />
            
            <!-- Unread Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 flex items-center justify-center min-w-[16px] h-[16px] px-0.5 text-[9px] font-bold text-white bg-red-500 rounded-full"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="fixed left-4 right-4 top-20 w-auto sm:absolute sm:right-0 sm:left-auto sm:top-auto sm:mt-2 sm:w-96 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50 overflow-hidden"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Notifications</h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium flex items-center gap-1"
                    >
                        <CheckCheck :size="14" />
                        Mark all read
                    </button>
                </div>

                <!-- Notifications List -->
                <div class="max-h-[400px] overflow-y-auto">
                    <div v-if="isLoading" class="flex items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                    </div>

                    <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-12 px-4">
                        <Bell :size="48" class="text-gray-300 dark:text-gray-600 mb-3" />
                        <p class="text-gray-500 dark:text-gray-400 text-sm">No notifications yet</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div
                            v-for="notification in notifications"
                            :key="notification.id"
                            class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
                            :class="{ 'bg-blue-50/30 dark:bg-blue-900/10': !notification.read_at }"
                        >
                            <div class="flex gap-3">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="getTypeClasses(notification.type)">
                                        <component :is="getTypeIcon(notification.type)" :size="20" />
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ notification.title }}
                                        </h4>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <button
                                                v-if="!notification.read_at"
                                                @click="markAsRead(notification.id)"
                                                class="p-1 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                                title="Mark as read"
                                            >
                                                <Check :size="14" />
                                            </button>
                                            <button
                                                @click="deleteNotification(notification.id)"
                                                class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                                                title="Delete"
                                            >
                                                <Trash2 :size="14" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <p v-if="notification.description" class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                        {{ notification.description }}
                                    </p>
                                    
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-500">
                                            {{ timeAgo(notification.created_at) }}
                                        </span>
                                        <Link
                                            v-if="notification.action_url"
                                            :href="notification.action_url"
                                            class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium"
                                            @click="isOpen = false"
                                        >
                                            View →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="notifications.length > 0" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                    <Link
                        :href="viewAllUrl"
                        class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium block text-center"
                        @click="isOpen = false"
                    >
                        View all notifications
                    </Link>
                </div>
            </div>
        </transition>
    </div>
</template>
