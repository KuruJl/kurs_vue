<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ColoredSquare from '@/Components/ColoredSquare.vue'; // Импортируем наш новый компонент

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <div
        class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white"
    >
        

        <div class="max-w-7xl mx-auto p-6 lg:p-8">
            <ColoredSquare class="mb-8" /> 

            <div class="flex justify-center">
                <img src="/laravel-inertia-logo.svg" alt="Laravel Inertia" class="h-16" />
            </div>

            <div class="mt-16 text-center text-sm text-gray-500 dark:text-gray-400">
                Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
            </div>
        </div>
    </div>
</template>