<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref  } from 'vue';
import { router } from '@inertiajs/vue3';
import AppHeader from './Header.vue';
import AppFooter from './Footer.vue';

const props = defineProps({
    cart: {
        type: Array,
        default: () => [],
    },
    total: {
        type: Number,
        default: 0,
    },
});

const cartTotal = computed(() => {
    return props.cart.reduce((total, item) => {
        return total + (item.price * item.quantity);
    }, 0);
});

const handleQuantityChange = (event, productId, maxAvailable) => {
    let newQuantity = parseInt(event.target.value, 10);

    // Валидация введенного значения
    if (isNaN(newQuantity) || newQuantity < 1) {
        newQuantity = 1; // Если ввели ерунду или 0, ставим 1
    }
    if (newQuantity > maxAvailable) {
        newQuantity = maxAvailable; // Если ввели больше, чем есть, ставим максимум
        alert(`Максимально доступное количество этого товара: ${maxAvailable}`);
    }
    
    // Вызываем нашу основную функцию обновления
    updateQuantity(productId, newQuantity);
};

const submitOrder = () => {
    router.post('/checkout', {}, {
        // onSuccess теперь не нужен, редиректом управляет бэкенд
        onError: (errors) => {
            console.error('Ошибка оформления заказа:', errors);
            // Если есть специфическая ошибка от валидации, покажем ее
            if (errors.cart) {
                alert(errors.cart);
            } else {
                alert('Произошла ошибка при оформлении заказа.');
            }
        }
    });
};

const formatPrice = (value) => {
    if (value === undefined || value === null) return '0';
    return new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
};

const updateQuantity = (productId, newQuantity) => {
    // Находим товар в нашем локальном массиве
    const item = props.cart.find(p => p.id === productId);
    if (!item) return;

    // Сохраняем старое количество на случай отката
    const oldQuantity = item.quantity;

    // Проверяем, не выходим ли за рамки доступного
    if (newQuantity > item.max_available) {
        alert(`Максимально доступное количество: ${item.max_available}`);
        return;
    }
    if (newQuantity < 1) {
        // Если количество меньше 1, инициируем удаление
        removeItem(productId);
        return;
    }

    // 1. ОПТИМИСТИЧНОЕ ОБНОВЛЕНИЕ: Мгновенно меняем данные в интерфейсе
    item.quantity = newQuantity;

    // 2. ФОНОВЫЙ ЗАПРОС: Отправляем изменения на сервер
    router.patch(`/cart/${productId}`, { 
        quantity: newQuantity 
    }, {
        preserveScroll: true,
        // 3. ОБРАБОТКА ОШИБКИ: Если сервер отказал, откатываем изменения
        onError: (errors) => {
            // Возвращаем старое количество в интерфейсе
            item.quantity = oldQuantity;
            // Показываем ошибку
            if (errors.message) {
                alert(errors.message);
            } else {
                alert('Не удалось обновить количество товара.');
            }
        },
    });
};

// Ref для блокировки кнопок на время обновления
const isUpdating = ref(false);

const removeItem = (productId) => {
    if (!confirm('Удалить этот товар из корзины?')) return;
    router.delete(`/cart/${productId}/remove`, {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.message) {
                alert(errors.message);
            }
        },
    });
};

const isCartEmpty = computed(() => props.cart.length === 0);
</script>

<template>
    <Head title="Корзина" />
    <AppHeader />

    <main class="min-h-screen bg-Wblue px-4 sm:px-6 lg:px-8 py-8 sm:py-14 flex flex-col items-center">
        <h2 class="font-rubik-semibold text-3xl sm:text-4xl md:text-5xl text-pink-200 text-center mb-8 sm:mb-12">
            Корзина
        </h2>

        <div class="w-full max-w-5xl mx-auto">
            <div v-if="isCartEmpty" class="text-center">
                <p class="font-rubik-light text-xl sm:text-2xl text-white/80">Ваша корзина пуста.</p>
                <Link href="/catalog" class="mt-4 inline-block font-rubik-semibold text-lg text-pink-200 hover:underline">
                    Перейти к каталогу
                </Link>
            </div>

            <div v-else class="flex flex-col gap-6">
                <div v-for="item in props.cart" :key="item.id"
                     class="flex flex-col sm:flex-row items-start sm:items-center p-4 sm:p-6 rounded-xl border border-white/30 bg-blue-600/10 gap-4 sm:gap-6">
                    <div class="w-full sm:w-32 sm:h-32 flex-shrink-0">
                        <div class="relative w-full h-full rounded-md overflow-hidden bg-gray-900/50">
                            <img :src="item.image || '/images/default_product.png'" :alt="item.name || 'Товар'"
                                 class="absolute inset-0 w-full h-full object-contain" />
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col sm:flex-row items-start sm:items-center w-full gap-4 sm:gap-6">
                        <div class="flex flex-col flex-1">
                            <h3 class="font-rubik-semibold text-xl sm:text-2xl text-white">{{ item.name || 'Без названия' }}</h3>
                            <div class="font-rubik-semibold text-xl sm:text-2xl text-white/80 mt-2">
                                {{ formatPrice((item.price || 0) * (item.quantity || 1)) }} ₽
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-4 sm:w-40 ">
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        @click="updateQuantity(item.id, item.quantity - 1)"
                                        :disabled="item.quantity <= 1 || isUpdating"
                                        aria-label="Уменьшить количество"
                                        class="w-8 h-8 bg-white text-black rounded-sm flex items-center justify-center hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="text-xl font-bold">−</span>
                                </button>
                                
                                <!-- === ИЗМЕНЕНИЕ ЗДЕСЬ: INPUT ЗАМЕНЕН НА SPAN === -->
                                <input 
    type="number"
    :value="item.quantity"
    @change="handleQuantityChange($event, item.id, item.max_available)"
    min="1"
    :max="item.max_available"
    class="font-rubik-semibold text-xl text-white w-[75px] text-center bg-transparent border-t border-b border-white/30 h-8 focus:outline-none focus:ring-1 focus:ring-pink-200"
    aria-label="Количество товара"
/>                                
                                <button type="button"
                                        @click="updateQuantity(item.id, item.quantity + 1)"
                                        :disabled="item.quantity >= item.max_available || isUpdating"
                                        aria-label="Увеличить количество"
                                        class="w-8 h-8 bg-white text-black rounded-sm flex items-center justify-center hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="text-xl font-bold">+</span>
                                </button>
                            </div>
                            
                            <button type="button"
                                    @click="removeItem(item.id)"
                                    aria-label="Удалить товар"
                                    class="text-red-500 hover:text-red-400 transition">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 5H15C15 4.44772 14.5523 4 14 4C13.4477 4 13 4.44772 13 5H11C11 4.44772 10.5523 4 10 4C9.44772 4 9 4.44772 9 5ZM7 5C7 3.34315 8.34315 2 10 2C11.6569 2 13 3.34315 13 5H17C18.6569 5 20 6.34315 20 8H4C4 6.34315 5 5 7 5ZM4 9H20V19C20 20.6569 18.6569 22 17 22H7C5.34315 22 4 20.6569 4 19V9ZM9 11C9.55228 11 10 11.4477 10 12V18C10 18.5523 9.55228 19 9 19C8.44772 19 8 18.5523 8 18V12C8 11.4477 8.44772 11 9 11ZM15 11C15.5523 11 16 11.4477 16 12V18C16 18.5523 15.5523 19 15 19C14.4477 19 14 18.5523 14 18V12C14 11.4477 14.4477 11 15 11Z" fill="currentColor"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-8 sm:mt-12">
                    <div class="flex flex-col items-end gap-4">
                        <div class="font-rubik-semibold text-2xl sm:text-3xl text-white">
    Итого: {{ formatPrice(cartTotal) }} ₽
</div>
                        <button @click="submitOrder"
                                class="mt-6 font-rubik-semibold inline-block bg-white hover:bg-pink-200 transition-colors
                                       text-black font-rubik-bold text-lg sm:text-xl px-8 py-3 rounded-lg">
                            Оформить заказ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <AppFooter />
</template>

<style scoped>
.bg-darkBlue { background-color: #001A33; }
.bg-Wblue { background-color: #011F41; }
.font-rubik-light { font-family: 'Rubik', sans-serif; font-weight: 300; }
.font-rubik-semibold { font-family: 'Rubik', sans-serif; font-weight: 600; }

input[type='number']::-webkit-outer-spin-button,
input[type='number']::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type='number'] {
  -moz-appearance: textfield; /* Для Firefox */
}

</style>