<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppHeader from './Header.vue';
import AppFooter from './Footer.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
        default: () => ({})
    },
    relatedProducts: {
        type: Array,
        default: () => []
    }
});

const quantity = ref(1);
const mainImage = ref(props.product?.image_url || '');
const thumbnails = ref([
    props.product?.image_url || '',
    'https://i.ibb.co/svLWFTGM/image-12.png',
    'https://i.ibb.co/PZR5L7md/image-14.png'
]);

const productFeatures = computed(() => {
    if (!props.product?.feature) return [];
    return props.product.feature.split('\n').filter(item => item.trim() !== '');
});

const formatPrice = (value) => {
    if (!value) return '0.00';
    return new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};
</script>

<template>
    <Head :title="product?.name || 'Товар'" />
    <AppHeader />

    <main class="min-h-screen bg-Wblue px-4 sm:px-6 lg:px-8">
        <div class="container mx-auto py-8 sm:py-12">
            <!-- Основной контент страницы -->
            <div v-if="product" class="flex flex-col lg:flex-row gap-8 sm:gap-10 md:gap-16">
                <!-- Блок с изображениями товара -->
                <section class="w-full lg:w-auto lg:max-w-xl flex-shrink-0">
                    <div class="border border-white/30 w-full aspect-[4/3] flex items-center justify-center rounded-lg overflow-hidden">
                        <img :src="mainImage" :alt="product.name" class="max-w-full max-h-full object-contain" />
                    </div>
                    <div class="flex gap-3 mt-4">
                        <div v-for="(thumb, index) in thumbnails" :key="index" 
                             @click="mainImage = thumb"
                             class="border border-white/30 flex-1 aspect-square flex items-center justify-center rounded overflow-hidden cursor-pointer hover:border-white/70 transition">
                            <img :src="thumb" :alt="product.name + ' миниатюра ' + (index + 1)" class="max-w-full max-h-full object-contain" />
                        </div>
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

                    <div class="mt-8 sm:mt-10" v-if="productFeatures.length > 0">
                        <h2 class="font-rubik-medium text-xl sm:text-2xl text-white/90">Характеристики</h2>
                        <ul class="font-rubik-light text-lg sm:text-xl text-white/80 mt-2 leading-relaxed list-disc list-inside">
                            <li v-for="(feature, index) in productFeatures" :key="index">{{ feature }}</li>
                        </ul>
                    </div>

                    <div class="mt-10 sm:mt-12">
                        <div class="flex flex-wrap items-center gap-6 sm:gap-8">
                            <div class="font-rubik-semibold text-4xl sm:text-5xl text-white">{{ formatPrice(product.price) }} ₽</div>
                            <div class="flex items-center">
                                <label class="font-rubik-light text-white/80 mr-2 whitespace-nowrap">Количество:</label>
                                <div class="quantity-selector flex items-center">
                                    <button type="button" @click="quantity > 1 ? quantity-- : null" 
                                            class="quantity-btn minus w-8 h-8 flex items-center justify-center border border-white/30 bg-transparent text-white text-lg">−</button>
                                    <input type="number" v-model.number="quantity" min="1" :max="product.quantity_available" 
                                           class="quantity-input w-12 h-8 text-center bg-transparent text-white border-t border-b border-white/30" />
                                    <button type="button" @click="quantity < product.quantity_available ? quantity++ : null" 
                                            class="quantity-btn plus w-8 h-8 flex items-center justify-center border border-white/30 bg-transparent text-white text-lg">+</button>
                                </div>
                            </div>
                            <button :disabled="!product.is_in_stock"
                                    class="bg-blue-600/20 text-white border-2 border-white rounded-md py-3 px-8 sm:py-4 sm:px-10 font-rubik-semibold text-lg sm:text-xl hover:bg-blue-600/30 transition whitespace-nowrap">
                                {{ product.is_in_stock ? 'Добавить в корзину' : 'Нет в наличии' }}
                            </button>
                            <span class="font-rubik-light text-white/80 whitespace-nowrap">
                                {{ product.is_in_stock ? 'В наличии' : 'Нет в наличии' }} ({{ product.quantity_available }} шт.)
                            </span>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Похожие товары -->
            <section class="mt-16 sm:mt-24" v-if="relatedProducts && relatedProducts.length > 0">
                <h2 class="font-rubik-medium text-3xl sm:text-4xl text-pink-200">ТАКЖЕ ПОКУПАЮТ</h2>
                <div class="mt-8 sm:mt-12 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-8">
                    <Link v-for="related in relatedProducts" :key="related.id" :href="`/products/${related.id}`" 
                          class="border border-white/30 bg-blue-600/10 rounded-xl overflow-hidden group flex flex-col text-center hover:bg-blue-600/20 transition duration-300 aspect-[1/1]">
                        <div class="p-4 sm:p-6 flex-grow flex flex-col justify-center">
                            <h3 class="font-rubik-semibold text-xl sm:text-2xl text-white/90 mb-2">{{ related.name }}</h3>
                            <img :src="related.image_url" :alt="related.name" 
                                 class="max-w-[80%] max-h-[120px] object-contain mx-auto my-2 group-hover:scale-105 transition duration-300" />
                        </div>
                        <div class="mt-auto p-4 pt-0">
                            <p class="font-rubik-semibold text-xl md:text-2xl rounded-full bg-white/80 text-black/80 py-2 px-4 inline-block">
                                {{ formatPrice(related.price) }} ₽
                            </p>
                        </div>
                    </Link>
                </div>
            </section>
        </div>
    </main>

    <AppFooter />
</template>

<style scoped>
/* Стили остаются без изменений */
.quantity-selector {
    border-radius: 4px;
    overflow: hidden;
}

.quantity-input {
    -moz-appearance: textfield;
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.quantity-btn {
    transition: all 0.2s;
}

.quantity-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.font-rubik-light {
    font-family: 'Rubik', sans-serif;
    font-weight: 300;
}

.font-rubik-medium {
    font-family: 'Rubik', sans-serif;
    font-weight: 500;
}

.font-rubik-semibold {
    font-family: 'Rubik', sans-serif;
    font-weight: 600;
}

.bg-Wblue {
    background-color: #011F41;
}
</style>