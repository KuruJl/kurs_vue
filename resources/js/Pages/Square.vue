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
                <ColoredSquare /> 

</template>