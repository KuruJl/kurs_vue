<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppHeader from './Header.vue';
import AppFooter from './Footer.vue';

const props = defineProps({
    product: {
        type: Object,
        default: () => ({
            images: [], // Добавляем images в дефолтное значение
            // Добавляем main_image_url для единообразия, если вы решите его использовать
            main_image_url: '/images/default_product.png' 
        })
    }
});

const quantity = ref(1);
const isLoading = ref(false);

// Функция для получения полного URL изображения
// Теперь она просто возвращает путь, так как он уже содержит `/images/` и доступен из public
const getImageUrl = (path) => {
    // Если путь уже полный URL (например, из другого CDN), оставляем как есть
    if (path && path.startsWith('http')) {
        return path;
    }
    // Если путь начинается с /images/, то он уже корректен для public/images
    if (path && path.startsWith('/images/')) {
        return path;
    }
    // Если по какой-то причине путь просто имя файла (например, 'superone1.png'),
    // то добавляем /images/ перед ним. Однако, по вашему сидеру, это не должно быть проблемой.
    if (path) {
        return `/images/${path}`;
    }
    // Дефолтная картинка, если нет пути
    return '/images/default_product.png';
};


// Главное изображение
const mainImage = ref(
    // Предпочтительно использовать main_image_url, если он есть
    props.product?.main_image_url ||
    // Если нет, ищем первое изображение с is_main из массива изображений
    (props.product?.images?.find(img => img.is_main)?.path && getImageUrl(props.product.images.find(img => img.is_main).path)) ||
    // Или первое попавшееся изображение
    (props.product?.images?.[0]?.path && getImageUrl(props.product.images[0].path)) ||
    // Дефолтная картинка
    '/images/default_product.png'
);

// Миниатюры для галереи
const thumbnails = computed(() => {
    const images = props.product?.images || [];
    
    if (images.length > 0) {
        return images.map(img => getImageUrl(img.path));
    }
    
    // Если нет своих изображений, показываем 3 дефолтные заглушки
    return [
        '/images/default_product.png',
        '/images/default_product.png',
        '/images/default_product.png'
    ];
});

const productFeatures = computed(() => {
    if (!props.product?.feature) return [];
    return props.product.feature
        .split('\n')
        .filter(item => item.trim() !== '');
});

const formatPrice = (value) => {
    if (!value && value !== 0) return '0.00 ₽';
    return new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value) + ' ₽';
};

const addToCart = async () => {
    if (isLoading.value || !props.product?.id) return;
    
    isLoading.value = true;
    
    try {
        // Убедитесь, что route() доступен, если вы используете Ziggy
        // Если нет, замените на прямую ссылку '/cart'
        await router.post(route('cart.store'), {
            product_id: props.product.id,
            quantity: quantity.value
        }, {
            preserveScroll: true
        });
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Head :title="product?.name || 'Товар'" />
    <AppHeader />

    <main class="min-h-screen bg-Wblue">
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div v-if="product" class="flex flex-col lg:flex-row gap-8 sm:gap-10 md:gap-16">
                <!-- Блок с изображениями товара -->
                <section class="w-full lg:w-auto lg:max-w-xl flex-shrink-0">
                    <div class="border border-white/30 w-full aspect-[4/3] flex items-center justify-center rounded-lg overflow-hidden bg-darkBlue/20">
                        <img 
                            :src="mainImage" 
                            :alt="product.name" 
                            class="max-w-full max-h-full object-contain p-4"
                        />
                    </div>
                    <div class="flex gap-3 mt-4">
                        <div 
                            v-for="(thumb, index) in thumbnails" 
                            :key="index"
                            @click="mainImage = thumb"
                            class="border border-white/30 flex-1 aspect-square flex items-center justify-center rounded overflow-hidden cursor-pointer hover:border-white/70 transition bg-darkBlue/10"
                            :class="{ 'ring-2 ring-pink-200': thumb === mainImage }"
                        >
                            <img 
                                :src="thumb" 
                                :alt="`${product.name} миниатюра ${index + 1}`" 
                                class="max-w-full max-h-full object-contain p-2"
                            />
                        </div>
                    </div>
                </section>

                <!-- Информация о товаре -->
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
                            <div class="font-rubik-semibold text-4xl sm:text-5xl text-white">{{ formatPrice(product.price) }}</div>
                            <div class="flex items-center">
                                <label class="font-rubik-light text-white/80 mr-2 whitespace-nowrap">Количество:</label>
                                <div class="quantity-selector flex items-center">
                                    <button 
                                        type="button" 
                                        @click="quantity > 1 ? quantity-- : null" 
                                        class="quantity-btn minus w-8 h-8 flex items-center justify-center border border-white/30 bg-transparent text-white text-lg"
                                    >−</button>
                                    <input 
                                        type="number" 
                                        v-model.number="quantity" 
                                        min="1" 
                                        :max="product.quantity_available" 
                                        class="quantity-input w-12 h-8 text-center bg-transparent text-white border-t border-b border-white/30" 
                                    />
                                    <button 
                                        type="button" 
                                        @click="quantity < product.quantity_available ? quantity++ : null" 
                                        class="quantity-btn plus w-8 h-8 flex items-center justify-center border border-white/30 bg-transparent text-white text-lg"
                                    >+</button>
                                </div>
                            </div>
                            <button 
    @click="addToCart"
    :disabled="!product.is_in_stock || isLoading" 
    class="bg-blue-600/20 text-white border-2 border-white rounded-md py-3 px-8 sm:py-4 sm:px-10 font-rubik-semibold text-lg sm:text-xl hover:bg-blue-600/30 transition whitespace-nowrap"
>
    <span v-if="isLoading">Добавление...</span>
    <span v-else>{{ product.is_in_stock ? 'Добавить в корзину' : 'Нет в наличии' }}</span> </button>
<span class="font-rubik-light text-white/80 whitespace-nowrap">
    {{ product.is_in_stock ? 'В наличии' : 'Нет в наличии' }} ({{ product.quantity_available }} шт.) </span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <AppFooter />
</template>

<style scoped>
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

.bg-darkBlue {
    background-color: #001A33;
}
</style>