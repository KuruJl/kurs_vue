<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppHeader from './Header.vue'; // Импорт AppHeader из текущей директории
import AppFooter from './Footer.vue'; // Импорт AppFooter из текущей директории

const props = defineProps({
    product: Object, // Объект с данными о текущем товаре
    relatedProducts: Array, // Массив с данными о связанных товарах
});

// Состояние для счетчика количества
const quantity = ref(1);

const incrementQuantity = () => {
    if (quantity.value < props.product.quantity_available) { // Ограничиваем по наличию
        quantity.value++;
    }
};

const decrementQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

// Функция для добавления в корзину (пока заглушка)
const addToCart = () => {
    // Здесь будет логика добавления товара в корзину
    // Например, можно отправить POST-запрос на сервер
    console.log(`Добавлено в корзину: ${props.product.name}, Количество: ${quantity.value}`);
    alert(`"${props.product.name}" (x${quantity.value}) добавлен в корзину! (Это пока заглушка)`);
    // В реальном приложении ты бы использовал Inertia.post или axios
    // router.post(route('cart.add'), {
    //     product_id: props.product.id,
    //     quantity: quantity.value,
    // });
};

// Если у тебя есть характеристики в виде массива строк (как в старом Blade)
// то нужно будет их либо передать из бэкенда, либо распарсить здесь
// Сейчас просто заглушка для характеристик.
const productFeatures = computed(() => {
    // В идеале, характеристики должны быть полем в БД или связанной таблицей.
    // Если они в описании, можно попробовать их распарсить, но это менее надежно.
    // Для примера:
    if (props.product.name.includes('Мышь')) { // Пример хардкода, замени на реальные данные
        return [
            'Вес: 57 г',
            'Частота опроса: 125-1000 Гц',
            'Время работы: 40 часов при 1000 Гц',
        ];
    } else if (props.product.name.includes('Клавиатура')) {
        return [
            'Тип переключателей: Механические',
            'Подсветка: RGB',
            'Материал корпуса: Алюминий',
        ];
    }
    return ['Характеристики отсутствуют']; // Заглушка
});

// Состояние для главной картинки (если будет галерея)
const mainImage = ref(props.product.image_url);

// Если будут дополнительные изображения, можно добавить их здесь
// const additionalImages = ref([
//     'url_доп_изображения_1',
//     'url_доп_изображения_2',
// ]);
// const setMainImage = (url) => {
//     mainImage.value = url;
// };

</script>

<template>
    <Head :title="product.name" />

    <div class="min-h-screen bg-darkBlue flex flex-col">
        <AppHeader />

        <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-14">
            <div class="flex flex-col lg:flex-row gap-8 sm:gap-10 md:gap-16">
                <section class="w-full lg:w-auto lg:max-w-xl flex-shrink-0">
                    <div class="border border-white/30 w-full rounded-lg overflow-hidden bg-Wblue
                                flex items-center justify-center
                                relative aspect-w-21 aspect-h-9  max-w-[700px] lg:max-w-xl xl:max-w-[800px] mx-auto">
                        <img :src="mainImage" :alt="product.name" class="absolute inset-0 block w-full h-full object-contain p-4" />
                    </div>
                    </section>

                <section class="flex-1">
                    <h1 class="font-rubik-semibold text-4xl sm:text-5xl md:text-6xl text-white">{{ product.name }}</h1>

                    <div class="mt-6 sm:mt-8">
                        <h2 class="font-rubik-medium text-xl sm:text-2xl text-white/90">Описание</h2>
                        <p class="font-rubik-light text-lg sm:text-xl text-white/80 mt-2 leading-relaxed">
                            {{ product.description }}
                        </p>
                    </div>

                    <div class="mt-8 sm:mt-10">
                        <h2 class="font-rubik-medium text-xl sm:text-2xl text-white/90">Характеристики</h2>
                        <ul class="font-rubik-light text-lg sm:text-xl text-white/80 mt-2 leading-relaxed list-disc list-inside">
                            <li v-for="(feature, index) in productFeatures" :key="index">{{ feature }}</li>
                        </ul>
                    </div>

                    <form @submit.prevent="addToCart" class="mt-10 sm:mt-12">
                        <div class="flex flex-wrap items-center gap-6 sm:gap-8">
                            <div class="font-rubik-semibold text-4xl sm:text-5xl text-pink-200">{{ product.price.toLocaleString('ru-RU') }} ₽</div>
                            
                            <div class="flex items-center">
                                <label for="quantity" class="font-rubik-light text-white/80 mr-2 whitespace-nowrap">Количество:</label>
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn minus" @click="decrementQuantity">−</button>
                                    <input type="number" id="quantity" name="quantity" v-model="quantity" min="1" :max="product.quantity_available" aria-label="Количество товара" class="quantity-input">
                                    <button type="button" class="quantity-btn plus" @click="incrementQuantity">+</button>
                                </div>
                            </div>

                            <button type="submit"
                                    :disabled="product.quantity_available === 0"
                                    class="bg-blue-600/20 text-white border-2 border-white rounded-md py-3 px-8 sm:py-4 sm:px-10 font-rubik-semibold text-lg sm:text-xl hover:bg-blue-600/30 transition whitespace-nowrap"
                                    :class="{'opacity-50 cursor-not-allowed': product.quantity_available === 0}">
                                {{ product.quantity_available === 0 ? 'Нет в наличии' : 'Добавить в корзину' }}
                            </button>
                            
                            <template v-if="product.quantity_available > 0">
                                <label for="" class="font-rubik-light text-white/80 mr-2 whitespace-nowrap">В наличии:</label>
                                <span class="font-rubik-semibold text-white/90">{{ product.quantity_available }}</span>
                            </template>
                            <template v-else>
                                <span class="font-rubik-semibold text-red-500">Нет в наличии</span>
                            </template>
                        </div>
                    </form>
                </section>
            </div>

            <section v-if="relatedProducts.length > 0" class="mt-16 sm:mt-24">
                <h2 class="font-rubik-medium text-3xl sm:text-4xl text-pink-200">ТАКЖЕ ПОКУПАЮТ</h2>
                <div class="mt-8 sm:mt-12 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-8">
                    <Link v-for="relProduct in relatedProducts" :key="relProduct.id"
                          :href="`/products/${relProduct.slug}`"
                          class="border border-white/30 bg-blue-600/10 rounded-xl overflow-hidden group flex flex-col text-center hover:bg-blue-600/20 transition duration-300 aspect-[1/1]">
                        <div class="p-4 sm:p-6 flex-grow flex flex-col justify-center">
                            <h3 class="font-rubik-semibold text-xl sm:text-2xl text-white/90 mb-2">{{ relProduct.name }}</h3>
                            <img :src="relProduct.image_url" :alt="relProduct.name" class="max-w-[80%] max-h-[120px] object-contain mx-auto my-2 group-hover:scale-105 transition duration-300" />
                        </div>
                        <div class="mt-auto p-4 pt-0">
                            <p class="font-rubik-semibold text-xl md:text-2xl rounded-full bg-white/80 text-black/80 py-2 px-4 inline-block">
                                {{ relProduct.price.toLocaleString('ru-RU') }} ₽
                            </p>
                        </div>
                    </Link>
                </div>
            </section>
        </main>

        <AppFooter />
    </div>
</template>

<style scoped>
/* Стили из твоего старого шаблона, перенесенные сюда */
.font-norwester { font-family: 'Norwester', sans-serif; }
/* body { font-family: 'Rubik', sans-serif; font-weight: 400; }  - это лучше глобально в app.css */
.font-rubik-light { font-family: 'Rubik', sans-serif; font-weight: 300; }
.font-rubik-regular { font-family: 'Rubik', sans-serif; font-weight: 400; }
.font-rubik-medium { font-family: 'Rubik', sans-serif; font-weight: 500; }
.font-rubik-semibold { font-family: 'Rubik', sans-serif; font-weight: 600; }
 
/* Стили для счетчика количества */
.quantity-selector {
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.375rem;
    overflow: hidden;
    height: 42px; /* Фиксированная высота для кнопок и инпута */
}
 
.quantity-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 100%;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1rem;
}
 
.quantity-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}
 
.quantity-input {
    width: 50px;
    height: 100%;
    text-align: center;
    border: none;
    border-left: 1px solid rgba(255, 255, 255, 0.3);
    border-right: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 1rem;
    -moz-appearance: textfield; /* Скрывает стрелки в Firefox */
    padding: 0;
}
 
.quantity-input:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); /* Можно использовать pink-500 для Tailwind */
}
 
.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
 
label[for="quantity"] {
    font-family: 'Rubik', sans-serif;
    font-weight: 300;
    color: rgba(255, 255, 255, 0.8);
    margin-right: 0.5rem;
}

/* Кастомные цвета */
.bg-Wblue {
    background-color: #011F41;
}
.bg-darkBlue {
    background-color: #001A33;
}
</style>