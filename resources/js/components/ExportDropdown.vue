<script lang="ts" setup>
import { ref } from 'vue';

interface Props {
    pdfUrl: string;
    csvUrl: string;
}

defineProps<Props>();

const isOpen = ref(false);
const isLoading = ref(false);

function handleExport(url: string) {
    isLoading.value = true;
    window.location.href = url;
    setTimeout(() => {
        isLoading.value = false;
        isOpen.value = false;
    }, 1000);
}

function toggleDropdown() {
    isOpen.value = !isOpen.value;
}
</script>

<template>
    <div class="relative">
        <!-- Export Button -->
        <button
            @click="toggleDropdown"
            :disabled="isLoading"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-md transition-all hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
        >
            <i class="fas fa-download"></i>
            <span>{{ isLoading ? 'Mengunduh...' : 'Export' }}</span>
            <i class="fas fa-chevron-down text-xs" :class="{ 'rotate-180': isOpen }"></i>
        </button>

        <!-- Dropdown Menu -->
        <Teleport to="body">
            <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-40"></div>
        </Teleport>

        <div v-if="isOpen" class="absolute right-0 z-50 mt-2 w-48 rounded-lg border border-gray-200 bg-white shadow-xl">
            <!-- PDF Option -->
            <button
                @click="handleExport(pdfUrl)"
                :disabled="isLoading"
                class="flex w-full items-center gap-3 border-b border-gray-100 px-4 py-3 text-left text-sm font-medium transition-colors hover:bg-gray-50 disabled:opacity-50"
            >
                <i class="fas fa-file-pdf text-red-500"></i>
                <div>
                    <div class="font-semibold text-gray-900">Export PDF</div>
                    <div class="text-xs text-gray-500">Format siap cetak</div>
                </div>
            </button>

            <!-- CSV Option -->
            <button
                @click="handleExport(csvUrl)"
                :disabled="isLoading"
                class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-medium transition-colors hover:bg-gray-50 disabled:opacity-50"
            >
                <i class="fas fa-file-csv text-green-500"></i>
                <div>
                    <div class="font-semibold text-gray-900">Export CSV</div>
                    <div class="text-xs text-gray-500">Untuk Excel &amp; Sheets</div>
                </div>
            </button>
        </div>
    </div>
</template>

<style scoped></style>
