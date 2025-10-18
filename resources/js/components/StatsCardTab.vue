<template>
    <div class="scrollbar-hide overflow-x-auto rounded-2xl shadow-md">
        <div class="flex min-w-max flex-row gap-4 bg-white p-4">
            <button
                v-for="stat in stats"
                :key="stat.id"
                :disabled="stat.id === 'total_nilai'"
                :class="[
                    'inline-flex items-center gap-3 rounded-lg px-4 py-2 transition-all duration-200',
                    stat.id === 'total_nilai' ? 'cursor-default' : 'hover:cursor-pointer',
                    'text-sm whitespace-nowrap',
                    activeTab === stat.id ? `${stat.activeClass} font-semibold shadow-sm` : 'font-semibold',
                ]"
                @click="stat.id !== 'total_nilai' && $emit('update:activeTab', stat.id)"
            >
                <div
                    :class="[
                        'flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full',
                        activeTab === stat.id ? stat.iconActiveClass : stat.iconInactiveClass,
                    ]"
                >
                    <i :class="[stat.icon, 'text-[1rem]']"></i>
                </div>
                <span class="font-semibold">{{ stat.label }}</span>
                <span
                    :class="[
                        'rounded-full px-2 py-1 text-xs font-semibold',
                        activeTab === stat.id && stat.id !== 'total_nilai'
                            ? 'bg-gray-200 text-gray-900'
                            : 'bg-transparent text-gray-600',
                    ]"
                >
                    {{ stat.value }}
                </span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Stat {
    id: string;
    label: string;
    value: string | number;
    icon: string;
    activeClass: string;
    iconActiveClass: string;
    iconInactiveClass: string;
}

interface Props {
    stats: Stat[];
    activeTab?: string;
}

withDefaults(defineProps<Props>(), {
    activeTab: '',
});

defineEmits<{
    'update:activeTab': [value: string];
}>();
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
