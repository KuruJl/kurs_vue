<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';

import AppHeader from './Header.vue';
import AppFooter from './Footer.vue';

const props = defineProps({
    products: {
        type: Object,
        required: true,
        default: () => ({ data: [], links: [], meta: {} }),
    },
    categories: {
        type: Array,
        default: () => [],
    },
    initialMinPrice: {
        type: [Number, null],
        default: null,
    },
    initialMaxPrice: {
        type: [Number, null],
        default: null,
    },
    initialCategoryIds: {
        type: Array,
        default: () => [],
    },
    initialSearch: {
        type: [String, null],
        default: null,
    },
});

// Реактивные переменные для фильтров
const minPrice = ref(props.initialMinPrice);
const maxPrice = ref(props.initialMaxPrice);
const selectedCategoryIds = ref([...props.initialCategoryIds]);
const searchTerm = ref(props.initialSearch);

// Функция для форматирования цен
const formatPrice = (value) => {
    return new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
};

// Функция для применения фильтров
const applyFilters = () => {
    router.get('/catalog', {
        min_price: minPrice.value,
        max_price: maxPrice.value,
        category: selectedCategoryIds.value,
        search: searchTerm.value,
        page: 1
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Функция для сброса фильтров
const resetFilters = () => {
    minPrice.value = null;
    maxPrice.value = null;
    selectedCategoryIds.value = [];
    searchTerm.value = null;
    applyFilters();
};

// Функция для обрезки текста
const truncateDescription = (text, length) => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};

// Отладка
console.log('Products received in Catalog.vue:', props.products.data.map(p => ({
    id: p.id,
    name: p.name,
    slug: p.slug
})));
</script>

<template>
    <Head title="Каталог" />
    <AppHeader />

    <main class="min-h-screen bg-Wblue px-4 sm:px-6 lg:px-8">
        <div class="container mx-auto py-8 sm:py-14">
            <h1 class="font-rubik-semibold text-center text-2xl sm:text-4xl md:text-5xl text-pink-200 mb-6">Фильтр товаров</h1>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <aside class="bg-Wblue rounded-xl border-2 border-white/50 p-6 sm:p-8 h-auto">
                    <h2 class="font-rubik-semibold text-xl sm:text-2xl text-white mb-4">Фильтры</h2>

                    <div class="mb-2">
                        <h3 class="font-rubik-medium text-lg text-white/90 mb-2">Категории</h3>
                        <div class="space-y-2 sm:space-y-3">
                            <div v-for="category in categories" :key="category.id" class="flex items-center">
                                <input type="checkbox" :id="`category_${category.id}`" :value="category.id"
                                       v-model="selectedCategoryIds"
                                       class="form-checkbox h-5 w-5 text-Wblue focus:ring-pink-500 border-white/70 rounded">
                                <label :for="`category_${category.id}`" class="ml-2 font-rubik-light text-lg text-white/80">{{ category.name }}</label>
                            </div>

                            <div class="mt-4 sm:mt-6">
                                <h3 class="font-rubik-medium text-lg text-white/90 mb-2">Цена</h3>
                                <div class="flex items-center gap-2 mb-1">
                                    <label for="min_price" class="font-rubik-light text-lg text-white/80">От:</label>
                                    <input type="number" id="min_price" v-model.number="minPrice"
                                           class="bg-darkBlue text-white border border-white/30 rounded py-2 px-3 w-full focus:outline-none focus:ring-pink-500 focus:border-pink-500 text-sm">
                                </div>
                                <div class="flex items-center gap-2">
                                    <label for="max_price" class="font-rubik-light text-lg text-white/80">До:</label>
                                    <input type="number" id="max_price" v-model.number="maxPrice"
                                           class="bg-darkBlue text-white border border-white/30 rounded py-2 px-3 w-full focus:outline-none focus:ring-pink-500 focus:border-pink-500 text-sm">
                                </div>
                            </div>
                            <div class="mt-4 sm:mt-6">
                                <h3 class="font-rubik-medium text-lg text-white/90 mb-2">Поиск</h3>
                                <input type="text" id="search_term" v-model="searchTerm" placeholder="Найти товар..."
                                       class="bg-darkBlue text-white border border-white/30 rounded py-2 px-3 w-full focus:outline-none focus:ring-pink-500 focus:border-pink-500 text-sm">
                            </div>

                            <button type="button" @click="applyFilters" class="bg-blue-600/20 text-white border-2 border-white rounded-md py-3 px-6 font-rubik-semibold text-lg hover:bg-blue-600/30 transition mt-4">Применить</button>
                            <button type="button" @click="resetFilters" class="inline-block font-rubik-light text-lg text-pink-200 hover:opacity-80 transition ml-2 mt-2">Сбросить</button>
                        </div>
                    </div>
                </aside>

                <section class="md:col-span-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                        <template v-if="products.data.length > 0">
                            <div v-for="product in products.data" :key="product.id"
                                  class="bg-Wblue rounded-xl border-2 border-white/50 overflow-hidden group flex flex-col">
                                <Link :href="`/products/${product.id}`" class="flex-grow flex flex-col">
                                    <img :src="product.image_url" :alt="product.name"
                                         class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                                    <div class="p-4 sm:p-6 flex-grow">
                                        <h3 class="font-rubik-semibold text-xl text-white mb-2">{{ product.name }}</h3>
                                        <p class="font-rubik-light text-lg text-white/80">{{ truncateDescription(product.description, 100) }}</p>
                                    </div>
                                </Link>
                                <div class="p-4 sm:p-6 pt-0 mt-auto">
                                    <p class="font-rubik-semibold text-xl text-pink-200 mb-4">{{ formatPrice(product.price) }} ₽</p>
                                    <Link :href="`/products/${product.id}`"
                                          class="inline-block bg-blue-600/20 text-white border-2 border-white rounded-md py-2 px-4 font-rubik-light text-lg hover:bg-blue-600/30 transition">
                                        Подробнее
                                    </Link>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <p class="font-rubik-light text-lg text-white/80 col-span-full text-center">Нет товаров, соответствующих выбранным фильтрам.</p>
                        </template>
                    </div>

                    <div v-if="products.links && products.links.length > 3" class="mt-8 flex justify-center flex-wrap gap-2">
                        <Link v-for="(link, index) in products.links" :key="index" :href="link.url"
                            v-html="link.label"
                            class="px-4 py-2 mx-1 rounded-md transition duration-300"
                            :class="{
                                'bg-blue-600 text-white font-rubik-medium border border-white': link.active,
                                'text-gray-300 hover:text-white hover:bg-white/10 border border-transparent': !link.active && link.url,
                                'cursor-not-allowed text-gray-500 bg-white/5 border border-transparent': !link.url
                            }">
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <AppFooter />
</template>

<style scoped>
.font-norwester { font-family: 'Norwester', sans-serif; }
.font-rubik-light { font-family: 'Rubik', sans-serif; font-weight: 300; }
.font-rubik-regular { font-family: 'Rubik', sans-serif; font-weight: 400; }
.font-rubik-medium { font-family: 'Rubik', sans-serif; font-weight: 500; }
.font-rubik-semibold { font-family: 'Rubik', sans-serif; font-weight: 600; }
.bg-darkBlue { background-color: #001A33; }
.bg-Wblue { background-color: #011F41; }
</style>