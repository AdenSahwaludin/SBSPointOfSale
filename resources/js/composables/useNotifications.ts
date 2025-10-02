import { ref } from 'vue';

export type NotificationType = 'success' | 'error' | 'warning' | 'info';

export interface Notification {
    id: string;
    type: NotificationType;
    title: string;
    message?: string;
    duration?: number;
}

const notifications = ref<Notification[]>([]);

export function useNotifications() {
    const addNotification = (notification: Omit<Notification, 'id'>) => {
        const id = Date.now().toString();
        const newNotification = {
            id,
            duration: 5000,
            ...notification,
        };
        
        notifications.value.push(newNotification);
        
        if (newNotification.duration > 0) {
            setTimeout(() => {
                removeNotification(id);
            }, newNotification.duration);
        }
        
        return id;
    };
    
    const removeNotification = (id: string) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index > -1) {
            notifications.value.splice(index, 1);
        }
    };
    
    const clearAll = () => {
        notifications.value = [];
    };
    
    const success = (title: string, message?: string) => {
        return addNotification({ type: 'success', title, message });
    };
    
    const error = (title: string, message?: string) => {
        return addNotification({ type: 'error', title, message, duration: 8000 });
    };
    
    const warning = (title: string, message?: string) => {
        return addNotification({ type: 'warning', title, message });
    };
    
    const info = (title: string, message?: string) => {
        return addNotification({ type: 'info', title, message });
    };
    
    return {
        notifications,
        addNotification,
        removeNotification,
        clearAll,
        success,
        error,
        warning,
        info,
    };
}