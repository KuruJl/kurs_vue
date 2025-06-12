<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3'; // Импорт router

import AppHeader from './Header.vue';
import AppFooter from './Footer.vue';

// Определяем props, которые будут передаваться из Laravel контроллера
const props = defineProps({
    cart: {
        type: Array, // Корзина будет массивом объектов товаров
        default: () => [],
    },
    total: {
        type: Number, // Общая сумма корзины
        default: 0,
    },
});

// Vue-функция для форматирования чисел (аналог number_format)
const formatPrice = (value) => {
    return new Intl.NumberFormat('ru-RU').format(value);
};

// Функция для обновления количества товара в корзине
// БЕЗ ZIGGY: строим URL вручную или через глобальный объект window.route
const updateQuantity = (id, newQuantity) => {
    router.patch(`/cart/${id}`, { quantity: newQuantity }, { // <-- Изменено
        preserveScroll: true,
        preserveState: true,
    });
};

// Функция для удаления товара из корзины
// БЕЗ ZIGGY: строим URL вручную или через глобальный объект window.route
const removeItem = (id) => {
    if (confirm('Вы уверены, что хотите удалить этот товар из корзины?')) {
        router.delete(`/cart/${id}/remove`, { // <-- Изменено, если твой маршрут cart.remove ведет на /cart/{id}/remove
            preserveScroll: true,
            preserveState: true,
        });
    }
};

// Вычисляемые свойства (computed) для общей суммы, если она не передается из бэкенда,
// или для других расчетов на фронтенде.
// В твоем случае 'total' уже приходит из бэкенда.
const computedTotal = computed(() => {
    return props.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

</script>

<template>
    <Head title="Корзина" /> <AppHeader />

    <main class="  min-h-screen bg-Wbluez px-4 sm:px-6 lg:px-8 py-8 sm:py-14 flex flex-col items-center">
        <h2 style="font-family:'Rubik', sans-serif;font-weight:500" class="text-4xl md:text-5xl lg:text-xxl text-pink-200 text-center mb-8">КОРЗИНА</h2>

        <div class="flex   flex-col gap-8 w-full max-w-5xl mx-auto">
            <template v-if="props.cart.length > 0">
                <div v-for="item in props.cart" :key="item.id"
                     class="flex flex-col sm:flex-row items-center p-6 sm:p-10 w-full rounded-xl border border-white gap-6 sm:gap-0 bg-blue-600/10">
                    <img :src="item.image" :alt="item.name" class="w-full sm:w-auto sm:h-[150px] object-cover rounded-md"/>
                    
                    <div class="flex flex-col sm:flex-row flex-1 justify-between items-center w-full sm:ml-8 gap-6 sm:gap-0">
                        <h2 style="font-family:Rubik, serif; font-weight:600" class="text-2xl sm:text-3xl text-white text-center sm:text-left">{{ item.name }}</h2>

                        <div class="flex flex-col sm:flex-row gap-6 sm:gap-12 items-center">
                            <div style="font-family:Rubik, serif; font-weight:600" class="flex gap-4 items-center">
                                <button type="button" 
                                        @click="updateQuantity(item.id, Math.max(1, item.quantity - 1))" 
                                        aria-label="Decrease quantity" 
                                        class="w-8 h-8 text-3xl font-bold text-black bg-white rounded-sm cursor-pointer flex items-center justify-center transition hover:bg-gray-200">
                                    -
                                </button>
                                <span class="text-3xl font-bold text-white">{{ item.quantity }}</span>
                                <button type="button" 
                                        @click="updateQuantity(item.id, item.quantity + 1)" 
                                        aria-label="Increase quantity" 
                                        class="w-8 h-8 text-3xl font-medium text-black bg-white rounded-sm cursor-pointer flex items-center justify-center transition hover:bg-gray-200">
                                    +
                                </button>
                            </div>

                            <button type="button" @click="removeItem(item.id)" aria-label="Remove item" class="cursor-pointer">
                                <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 opacity-50 transition hover:opacity-100">
                                    <path d="M11.5 6H16.5C16.5 5.33696 16.2366 4.70107 15.7678 4.23223C15.2989 3.76339 14.663 3.5 14 3.5C13.337 3.5 12.7011 3.76339 12.2322 4.23223C11.7634 4.70107 11.5 5.33696 11.5 6ZM10 6C10 4.93913 10.4214 3.92172 11.1716 3.17157C11.9217 2.42143 12.9391 2 14 2C15.0609 2 16.0783 2.42143 16.8284 3.17157C17.5786 3.92172 18 4.93913 18 6H24.25C24.4489 6 24.6397 6.07902 24.7803 6.21967C24.921 6.36032 25 6.55109 25 6.75C25 6.94891 24.921 7.13968 24.7803 7.28033C24.6397 7.42098 24.4489 7.5 24.25 7.5H22.94L21.723 22.103C21.6345 23.1653 21.1499 24.1556 20.3655 24.8774C19.5811 25.5992 18.554 25.9999 17.488 26H10.512C9.44599 25.9999 8.41894 25.5992 7.63450 24.8774C6.85007 24.1556 6.36554 23.1653 6.27700 22.103L5.06000 7.5H3.75C3.55109 7.5 3.36032 7.42098 3.21967 7.28033C3.07902 7.13968 3 6.94891 3 6.75C3 6.55109 3.07902 6.36032 3.21967 6.21967C3.36032 6.07902 3.55109 6 3.75 6H10ZM7.772 21.978C7.82919 22.6654 8.14262 23.3062 8.65015 23.7734C9.15767 24.2405 9.82222 24.4999 10.512 24.5H17.488C18.1778 24.4999 18.8423 24.2405 19.3499 23.7734C19.8574 23.3062 20.1708 22.6654 20.228 21.978L21.436 7.5H6.565L7.772 21.978ZM11.75 11C11.9489 11 12.1397 11.079 12.2803 11.2197C12.421 11.3603 12.5 11.5511 12.5 11.75V20.25C12.5 20.4489 12.421 20.6397 12.2803 20.7803C12.1397 20.921 11.9489 21 11.75 21C11.5511 21 11.3603 20.921 11.2197 20.7803C11.079 20.6397 11 20.4489 11 20.25V11.75C11 11.5511 11.079 11.3603 11.2197 11.2197C11.3603 11.079 11.5511 11 11.75 11ZM17 11.75C17 11.5511 16.921 11.3603 16.7803 11.2197C16.6397 11.079 16.4489 11 16.25 11C16.0511 11 15.8603 11.079 15.7197 11.2197C15.579 11.3603 15.5 11.5511 15.5 11.75V20.25C15.5 20.4489 15.579 20.6397 15.7197 20.7803C15.8603 20.921 16.0511 21 16.25 21C16.4489 21 16.6397 20.921 16.7803 20.7803C16.921 20.6397 17 20.4489 17 20.25V11.75Z" fill="#D00000"></path>
                                </svg>
                            </button>
                        </div>

                        <div style="font-family:Rubik, serif; font-weight:600" class="text-3xl sm:text-4xl lg:text-xxl font-medium text-white text-opacity-80">
                            {{ formatPrice(item.price * item.quantity) }} ₽
                        </div>
                    </div>
                </div>

                <div class="flex justify-end w-full mt-12">
                    <div class="flex flex-col items-end gap-4">
                        <div style="font-family:Rubik, serif; font-weight:600" class="text-3xl text-white">
                            Итого: {{ formatPrice(props.total) }} ₽
                        </div>
                        <Link href="/checkout" class="w-full sm:w-[400px] h-20 text-xl sm:text-2xl lg:text-3xl font-bold rounded-md border-2 border-white cursor-pointer bg-blue-600 bg-opacity-20 text-white flex items-center justify-center hover:bg-opacity-30 transition">
                            Оформить заказ
                        </Link>
                    </div>
                </div>
            </template>
            <template v-else>
                <p class="text-2xl text-white text-center">Ваша корзина пуста.</p>
            </template>
        </div>
    </main>

    <div class="border-t-2 border-white/20 my-6 sm:my-8"></div>
    <AppFooter />
</template>

<style scoped>
/* Добавь свои кастомные шрифты и цвета, если они не глобальные */
.font-rubik-medium { font-family: 'Rubik', sans-serif; font-weight: 500; }
.font-rubik-semibold { font-family: 'Rubik', sans-serif; font-weight: 600; }
.bg-darkBlue { background-color: #001A33; } /* Убедись, что этот цвет определен в твоем Tailwind config или здесь */
</style>