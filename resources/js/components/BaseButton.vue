<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    as?: 'button' | 'Link';
    href?: string;
    variant?: 'primary' | 'secondary' | 'success' | 'danger' | 'warning' | 'outline' | 'ghost';
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
    loading?: boolean;
    fullWidth?: boolean;
    icon?: string;
    iconPosition?: 'left' | 'right';
    customClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    as: 'button',
    variant: 'primary',
    size: 'md',
    type: 'button',
    disabled: false,
    loading: false,
    fullWidth: false,
    iconPosition: 'left',
    customClass: '',
});

const emit = defineEmits<{
    click: [event: Event];
}>();

// Computed styles
const buttonClasses = computed(() => {
    const baseClasses =
        'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed';

    // Size classes
    const sizeClasses = {
        xs: 'px-2 py-1 text-xs gap-1',
        sm: 'px-3 py-1.5 text-sm gap-1.5',
        md: 'px-4 py-2 text-sm gap-2',
        lg: 'px-6 py-3 text-base gap-2',
        xl: 'px-8 py-4 text-lg gap-3',
    };

    // Variant classes with emerald theme
    const variantClasses = {
        primary: 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 disabled:bg-emerald-300 shadow-emerald',
        secondary:
            'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 focus:ring-emerald-500 disabled:bg-gray-100 disabled:text-gray-400 border border-emerald-200',
        success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 disabled:bg-green-300',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 disabled:bg-red-300',
        warning: 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-400 disabled:bg-yellow-300',
        outline:
            'bg-transparent text-emerald-700 border-2 border-emerald-600 hover:bg-emerald-50 focus:ring-emerald-500 disabled:border-gray-300 disabled:text-gray-400',
        ghost: 'bg-transparent text-emerald-700 hover:bg-emerald-50 focus:ring-emerald-500 disabled:text-gray-400',
    };

    const widthClass = props.fullWidth ? 'w-full' : '';
    const loadingClass = props.loading ? 'opacity-75' : '';

    return [baseClasses, sizeClasses[props.size], variantClasses[props.variant], widthClass, loadingClass, props.customClass]
        .filter(Boolean)
        .join(' ');
});

function handleClick(event: Event) {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
}
</script>

<template>
    <component
        :is="props.as === 'Link' ? Link : 'button'"
        :href="props.as === 'Link' ? href : undefined"
        :type="props.as === 'button' ? type : undefined"
        :disabled="disabled || loading"
        :class="buttonClasses"
        @click="handleClick"
    >
        <!-- Loading spinner -->
        <svg v-if="loading" class="mr-2 -ml-1 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>

        <!-- Left icon -->
        <i v-if="icon && iconPosition === 'left' && !loading" :class="icon"></i>

        <!-- Button content -->
        <span v-if="$slots.default">
            <slot></slot>
        </span>

        <!-- Right icon -->
        <i v-if="icon && iconPosition === 'right' && !loading" :class="icon"></i>
    </component>
</template>
