<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';


import AppHeader from './Header.vue';
import AppFooter from './Footer.vue'; // Предполагаю, что твой AppFooter тоже в той же папке
</script>

<template>
    <AppHeader />
    <main class=" flex-grow flex flex-col items-center gap-6 sm:gap-8 mt-[150px] mb-[240px]">
        <h2 class="font-rubik-medium text-center text-2xl sm:text-3xl text-white max-w-[954px] px-4">
            По вопросам поддержки пишите на почту, вк или телеграмм :)
        </h2>
        
        <div class="flex flex-col md:flex-row gap-4 md:gap-8 justify-center w-full px-4 max-w-7xl">
            <a href="https://t.me/hachiroku" 
   class="font-rubik-medium 
          w-full md:w-1/3 lg:w-[305px] xl:w-[305px]
          h-[84px] !important text-base sm:text-lg text-white border-2 border-dashed border-white 
          hover:border-solid hover:bg-white/10 transition 
          flex items-center justify-center rounded-lg whitespace-nowrap">
    ТЕЛЕГРАМ
</a>
            <a href="https://vk.com/hachiroku" 
               class="font-rubik-medium 
                      w-full md:w-1/3 lg:w-[305px] xl:w-[305px] h-[84px] text-base sm:text-lg text-white border-2 border-dashed border-white 
                      hover:border-solid hover:bg-white/10 transition 
                      flex items-center justify-center rounded-lg whitespace-nowrap">
                ВКОНТАКТЕ
            </a>
            <a href="mailto:hachirokustore@gmail.com" 
               class="font-rubik-medium 
                      w-full md:w-1/3 lg:w-[305px] xl:w-[305px] h-[84px] text-base sm:text-lg text-white border-2 border-dashed border-white 
                      hover:border-solid hover:bg-white/10 transition 
                      flex items-center justify-center rounded-lg whitespace-nowrap">
                ПОЧТА
            </a>
        </div>
    </main>

    <AppFooter />
</template>