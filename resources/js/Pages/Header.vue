<template>
    <header class="flex flex-col mt-[50px] sm:flex-row justify-between items-center
                   w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-0 mt-4 sm:h-16">
        <Link href="/" class="font-norwester text-4xl sm:text-5xl md:text-6xl text-pink-200">
            hachiroku
        </Link>

        <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 md:gap-11 w-full sm:w-auto">
            <ul class="flex flex-col sm:flex-row items-center
                       gap-4 sm:gap-6 md:gap-11 text-lg sm:text-xl md:text-2xl text-white">
                <li><Link href="/catalog" class="font-rubik-light hover:opacity-80 transition">каталог</Link></li>
                <li><Link href="/support" class="font-rubik-light hover:opacity-80 transition">поддержка</Link></li>
                <li><Link href="/cart" class="font-rubik-light hover:opacity-80 transition">корзина</Link></li>
                <li><Link href="/profile" class="font-rubik-light hover:opacity-80 transition">профиль</Link></li>
                
                <template v-if="user">
                    <li>
                        <form @submit.prevent="logout">
                            <button type="submit" class="font-rubik-light hover:opacity-80 transition hover:text-red-600">Выйти</button>
                        </form>
                    </li>
                </template>
                <template v-else>
                    <li>
                        <Link href="/login" class="font-rubik-light hover:opacity-80 transition">Вход</Link>
                    </li>
                    <li>
                        <Link href="/register" class="font-rubik-light hover:opacity-80 transition">Регистрация</Link>
                    </li>
                </template>
            </ul>
        </div>
    </header>
</template>

<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

const logout = () => {
    router.post('/logout');
};
</script>

<style scoped>
</style>