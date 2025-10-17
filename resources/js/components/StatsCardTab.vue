<template>
  <div class="w-full overflow-x-auto scrollbar-hide">
    <div class="flex flex-row gap-3 min-w-min p-1">
      <button
        v-for="stat in stats"
        :key="stat.id"
        :class="[
          'flex items-center gap-3 px-4 py-3 rounded-full transition-all duration-200',
          'whitespace-nowrap min-w-fit shadow-sm',
          activeTab === stat.id
            ? `${stat.activeClass} font-semibold shadow-md`
            : `${stat.inactiveClass} font-medium hover:-translate-y-0.5 hover:shadow-md`
        ]"
        @click="$emit('update:activeTab', stat.id)"
      >
        <div
          :class="[
            'flex items-center justify-center w-6 h-6 rounded-full flex-shrink-0',
            activeTab === stat.id ? stat.iconActiveClass : stat.iconInactiveClass
          ]"
        >
          <i :class="[stat.icon, 'text-sm']"></i>
        </div>
        <div class="flex flex-col items-start">
          <span class="text-xs opacity-75">{{ stat.label }}</span>
          <span class="text-base font-bold leading-tight">{{ stat.value }}</span>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Stat {
  id: string
  label: string
  value: string | number
  icon: string
  activeClass: string
  inactiveClass: string
  iconActiveClass: string
  iconInactiveClass: string
}

interface Props {
  stats: Stat[]
  activeTab?: string
}

withDefaults(defineProps<Props>(), {
  activeTab: ''
})

defineEmits<{
  'update:activeTab': [value: string]
}>()
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
