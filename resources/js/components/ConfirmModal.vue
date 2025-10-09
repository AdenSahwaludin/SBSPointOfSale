<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Overlay -->
        <div class="bg-opacity-50 fixed inset-0 bg-black transition-opacity" @click="$emit('cancel')"></div>

        <!-- Modal -->
        <div class="flex min-h-screen items-center justify-center px-4 py-8">
            <div class="relative mx-auto w-full max-w-md rounded-lg bg-white shadow-xl dark:bg-gray-800">
                <!-- Header -->
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-600">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ title }}
                    </h3>
                </div>

                <!-- Body -->
                <div class="px-6 py-4">
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ message }}
                    </p>
                </div>

                <!-- Footer -->
                <div class="flex justify-end space-x-3 border-t border-gray-200 px-6 py-4 dark:border-gray-600">
                    <button
                        @click="$emit('cancel')"
                        class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                    >
                        Batal
                    </button>
                    <button
                        @click="$emit('confirm')"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700"
                    >
                        Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Konfirmasi',
    },
    message: {
        type: String,
        default: 'Apakah Anda yakin?',
    },
});

defineEmits(['confirm', 'cancel']);
</script>
