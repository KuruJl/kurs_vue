<template>
    <header class="flex flex-col mt-[50px] sm:flex-row justify-between items-center
                   w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-0 mt-4 sm:h-16">
        <Link href="/" class="font-norwester  text-4xl sm:text-5xl md:text-6xl text-pink-200">
            hachiroku
        </Link>

        <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 md:gap-11 w-full sm:w-auto">
            <ul class="flex flex-col sm:flex-row items-center
                       gap-4 sm:gap-6 md:gap-11 text-lg sm:text-xl md:text-2xl text-white">
                <li><Link href="/catalog" class="font-rubik-light hover:opacity-80 transition">каталог</Link></li>
                <li><Link href="/support" class="font-rubik-light hover:opacity-80 transition">поддержка</Link></li>
                <li><Link href="/cart" class="font-rubik-light hover:opacity-80 transition">корзина</Link></li>
                <li><Link href="/profile" class="font-rubik-light hover:opacity-80 transition">профиль</Link></li>
                <li>
                    <Link v-if="$page.props.auth.user && $page.props.auth.user.is_admin" href="/admin/products" class="nav-link font-rubik-light">
    Админ-панель
</Link>
</li>       
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
        <div v-if="flash.success" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded z-50">
        {{ flash.success }}
    </div>
    <div v-if="flash.error" class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded z-50">
        {{ flash.error }}
    </div>
    </header>
    
</template>

<script setup>
import { usePage, Link, router } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';

// Сначала инициализируем page
const page = usePage();

// Затем используем page для вычисляемых свойств
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);

// И только потом watch
watch(flash, (newVal) => {
    if (newVal?.success || newVal?.error) {
        setTimeout(() => {
            page.props.flash = {};
        }, 1500);
    }
}, { deep: true });

const logout = () => {
    router.post('/logout');
};
</script>

<style scoped>
</style>