<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AppHeader from './Header.vue'; // Проверьте правильность пути
import AppFooter from './Footer.vue'; // Проверьте правильность пути

const props = defineProps({
    product: Object,
    relatedProducts: Array,
});

const quantity = ref(1);
const thumbnails = ref([]);
// Начальное значение mainImage должно быть product.image_url, которое теперь гарантированно работает
const mainImage = ref(props.product.image_url);

onMounted(() => {
    // В вашем текущем Product модели, 'image' - это строка.
    // Если у вас нет отдельной таблицы product_images и отношения,
    // то props.product.images будет undefined, и этот блок не нужен.
    // Оставляем его закомментированным, если у вас нет отдельного отношения изображений.
    /*
    const coverImage = props.product.images?.find(img => img.is_cover);
    if (coverImage) {
        mainImage.value = coverImage.url;
    }
    thumbnails.value = props.product.images?.filter(img => !img.is_cover).slice(0, 3) || [];
    */
});

const incrementQuantity = () => {
    // Используем product.quantity_available для проверки, которое теперь должно быть корректно определено
    if (props.product.quantity_available !== undefined && quantity.value < props.product.quantity_available) {
        quantity.value++;
    }
};

const decrementQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const addToCart = () => {
    router.post('/cart', {
        product_id: props.product.id,
        quantity: quantity.value,
    }, {
        onSuccess: () => {
            alert(`"${props.product.name}" (x${quantity.value}) успешно добавлен в корзину!`);
            // Если вы хотите, чтобы страница корзины обновилась после добавления,
            // можете перенаправить на страницу корзины:
            // router.visit('/cart');
        },
        onError: (errors) => {
            if (errors && errors.message) {
                alert(`Ошибка при добавлении в корзину: ${errors.message}`);
            } else if (errors && Object.keys(errors).length > 0) {
                 alert('Произошла ошибка при добавлении в корзину. Подробности в консоли.');
                 console.error('Ошибка добавления в корзину (валидация или другие):', errors);
            } else {
                alert('Произошла неизвестная ошибка при добавлении в корзину.');
            }
        },
        preserveScroll: true,
    });
};

const productFeatures = computed(() => {
    if (props.product.feature) {
        return props.product.feature.split('\n').filter(item => item.trim() !== '');
    }
    return ['Характеристики отсутствуют'];
});
</script>

<template>
    <Head :title="product.name" />

    <div class="min-h-screen bg-darkBlue flex flex-col">
        <AppHeader />

        <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-14">
            <div class="flex flex-col lg:flex-row gap-8 sm:gap-10 md:gap-16">
                <div>
                    <div class="border border-white/30 w-full rounded-lg overflow-hidden bg-Wblue
                                 flex items-center justify-center
                                 aspect-[4/3] sm:aspect-[16/9] lg:aspect-[3/2]
                                 max-w-[600px] mx-auto">
                        <img :src="mainImage" :alt="product.name" class="block w-full h-full object-contain" />
                    </div>
                    <div class="mt-4 flex justify-center gap-2">
                        <div v-for="(thumb, index) in thumbnails" :key="index"
                            class="w-20 h-20 border border-white/30 rounded-lg overflow-hidden bg-Wblue cursor-pointer hover:border-white"
                            @click="mainImage = thumb.url">
                            <img :src="thumb.url" :alt="`Thumbnail ${index + 1}`" class="w-full h-full object-cover" />
                        </div>
                    </div>
                </div>

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
                        <ul class="font-rubik-light flex flex-col text-lg sm:text-xl text-white/80 mt-2 leading-relaxed list-none">
                            <li v-for="(feature, index) in productFeatures" :key="index">{{ feature }}</li>
                        </ul>
                    </div>

                    <form @submit.prevent="addToCart" class="mt-10 sm:mt-12">
                        <div class="flex flex-wrap items-center gap-6 sm:gap-8">
                            <div class="font-rubik-semibold text-4xl sm:text-5xl text-pink-200">{{
                                product.price.toLocaleString('ru-RU') }} ₽</div>

                            <div class="flex items-center">
                                <label for="quantity"
                                    class="font-rubik-light text-white/80 mr-2 whitespace-nowrap">Количество:</label>
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn minus"
                                        @click="decrementQuantity">−</button>
                                    <input type="number" id="quantity" name="quantity" v-model="quantity" min="1"
                                        :max="product.quantity_available" aria-label="Количество товара"
                                        class="quantity-input">
                                    <button type="button" class="quantity-btn plus"
                                        @click="incrementQuantity">+</button>
                                </div>
                            </div>

                            <button type="submit" :disabled="product.quantity_available === 0"
                                class="bg-blue-600/20 text-white border-2 border-white rounded-md py-3 px-8 sm:py-4 sm:px-10 font-rubik-semibold text-lg sm:text-xl hover:bg-blue-600/30 transition whitespace-nowrap"
                                :class="{ 'opacity-50 cursor-not-allowed': product.quantity_available === 0 }">
                                {{ product.quantity_available === 0 ? 'Нет в наличии' : 'Добавить в корзину' }}
                            </button>

                            <template v-if="product.quantity_available > 0">
                                <label for="" class="font-rubik-light text-white/80 mr-2 whitespace-nowrap">В
                                    наличии:</label>
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
                            <h3 class="font-rubik-semibold text-xl sm:text-2xl text-white/90 mb-2">{{ relProduct.name }}
                            </h3>
                            <img :src="relProduct.image_url" :alt="relProduct.name"
                                class="max-w-[80%] max-h-[120px] object-contain mx-auto my-2 group-hover:scale-105 transition duration-300" />
                        </div>
                        <div class="mt-auto p-4 pt-0">
                            <p
                                class="font-rubik-semibold text-xl md:text-2xl rounded-full bg-white/80 text-black/80 py-2 px-4 inline-block">
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
/* Стили для шрифтов и селектора количества остаются без изменений */
.font-norwester {
    font-family: 'Norwester', sans-serif;
}

.font-rubik-light {
    font-family: 'Rubik', sans-serif;
    font-weight: 300;
}

.font-rubik-regular {
    font-family: 'Rubik', sans-serif;
    font-weight: 400;
}

.font-rubik-medium {
    font-family: 'Rubik', sans-serif;
    font-weight: 500;
}

.font-rubik-semibold {
    font-family: 'Rubik', sans-serif;
    font-weight: 600;
}

.quantity-selector {
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.375rem;
    overflow: hidden;
    height: 42px;
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
    -moz-appearance: textfield;
    padding: 0;
}

.quantity-input:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
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

.bg-Wblue {
    background-color: #011F41;
}

.bg-darkBlue {
    background-color: #001A33;
}
</style>