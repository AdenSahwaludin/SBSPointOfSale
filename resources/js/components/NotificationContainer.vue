<script lang="ts" setup>
import { useNotifications } from '@/composables/useNotifications';

const { notifications, removeNotification } = useNotifications();

const getIconClass = (type: string) => {
    switch (type) {
        case 'success':
            return 'fas fa-check-circle text-green-500';
        case 'error':
            return 'fas fa-exclamation-circle text-red-500';
        case 'warning':
            return 'fas fa-exclamation-triangle text-yellow-500';
        case 'info':
            return 'fas fa-info-circle text-blue-500';
        default:
            return 'fas fa-info-circle text-gray-500';
    }
};

const getBgClass = (type: string) => {
    switch (type) {
        case 'success':
            return 'bg-green-50 border-green-200';
        case 'error':
            return 'bg-red-50 border-red-200';
        case 'warning':
            return 'bg-yellow-50 border-yellow-200';
        case 'info':
            return 'bg-blue-50 border-blue-200';
        default:
            return 'bg-gray-50 border-gray-200';
    }
};
</script>

<template>
    <div class="fixed top-4 right-4 z-50 max-w-sm space-y-2">
        <transition-group name="notification" tag="div">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                :class="['rounded-lg border p-4 shadow-lg', getBgClass(notification.type)]"
            >
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i :class="getIconClass(notification.type)"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-medium text-gray-900">
                            {{ notification.title }}
                        </h4>
                        <p v-if="notification.message" class="mt-1 text-sm text-gray-700">
                            {{ notification.message }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button
                            @click="removeNotification(notification.id)"
                            class="inline-flex rounded-md text-gray-400 hover:text-gray-600 focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </transition-group>
    </div>
</template>

<style scoped>
.notification-enter-active,
.notification-leave-active {
    transition: all 0.3s ease;
}

.notification-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.notification-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.notification-move {
    transition: transform 0.3s ease;
}
</style>
