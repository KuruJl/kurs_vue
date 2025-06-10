<template>
    <footer class="border-t border-white/20
                   w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8
                   mt-16 sm:mt-24 mb-6 sm:mb-10
                   pt-8 sm:pt-12 flex flex-wrap justify-between items-start gap-8">
        <div class="flex-shrink-0">
            <Link href="/" class="font-norwester text-3xl sm:text-4xl md:text-5xl text-pink-200 hover:opacity-90 transition">hachiroku</Link>
        </div>
        
        <div class="flex flex-col sm:flex-row flex-wrap justify-end gap-8 sm:gap-12 md:gap-16 text-lg sm:text-xl text-white">
            <nav class="flex flex-col gap-3 sm:gap-4">
                <h3 class="font-rubik-medium text-white/90 mb-1">Навигация</h3>
                <Link href="/catalog" class="font-rubik-light hover:opacity-80 transition">Каталог</Link>
                <Link href="/support" class="font-rubik-light hover:opacity-80 transition">Поддержка</Link>
                <Link href="/login" class="font-rubik-light hover:opacity-80 transition">Вход | Регистрация</Link>
            </nav>
            <div class="flex flex-col gap-3 sm:gap-4">
                <h3 class="font-rubik-medium text-white/90 mb-1">Связь</h3>
                <a href="https://vk.com/hachiroku" target="_blank" rel="noopener noreferrer" class="font-rubik-light hover:opacity-80 transition">Наш VK</a>
                <a href="https://t.me/hachiroku" target="_blank" rel="noopener noreferrer" class="font-rubik-light hover:opacity-80 transition">Наш Telegram</a>
                <a href="mailto:hachirokustore@gmail.com" class="font-rubik-light hover:opacity-80 transition">hachirokustore@gmail.com</a>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<script>
export default {
    name: 'AppFooter'
}
</script>