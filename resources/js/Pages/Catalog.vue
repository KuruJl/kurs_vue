<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const selectedCategories = ref(props.filters.category || []);
const minPrice = ref(props.filters.min_price || '');
const maxPrice = ref(props.filters.max_price || '');

const applyFilters = () => {
    router.get('/catalog', {
        category: selectedCategories.value,
        min_price: minPrice.value,
        max_price: maxPrice.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    selectedCategories.value = [];
    minPrice.value = '';
    maxPrice.value = '';
    applyFilters();
};

import AppHeader from './Header.vue';
import AppFooter from './Footer.vue';

const showMobileFilters = ref(false);

// Если ты используешь переводную карту для категорий
const categoryTranslations = {
    'keyboards': 'Клавиатуры',
    'mice': 'Мыши',
    'headphones': 'Наушники',
    'carpets': 'Коврики',
    // Добавь здесь все свои категории
};
</script>

<template>
    <Head title="Каталог" />

    <div class="min-h-screen bg-blue flex flex-col">
        <AppHeader />

        <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-14">
            <h1 class="font-rubik-semibold text-2xl sm:text-4xl md:text-5xl text-pink-200 mb-6 text-center">Фильтр товаров</h1>

            <button @click="showMobileFilters = !showMobileFilters"
                    class="md:hidden bg-blue-600/20 text-white border-2 border-white rounded-md py-2 px-4 font-rubik-semibold text-lg hover:bg-blue-600/30 transition mb-6 w-full">
                {{ showMobileFilters ? 'Скрыть фильтры' : 'Показать фильтры' }}
            </button>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:items-start"> 
                <aside :class="{'block': showMobileFilters, 'hidden': !showMobileFilters}"
                       class="md:block bg-Wblue rounded-xl border-2 border-white/50 p-6 sm:p-8 h-auto">
                    <h2 class="font-rubik-semibold text-xl sm:text-2xl text-white mb-4">Фильтры</h2>

                    <form @submit.prevent="applyFilters" class="space-y-4">
                        <div class="mb-2">
                            <h3 class="font-rubik-medium text-lg text-white/90 mb-2">Категории</h3>
                            <div class="space-y-2 sm:space-y-3">
                                <div v-for="category in categories" :key="category.id" class="flex items-center">
                                    <input type="checkbox" :id="'category_' + category.id" :value="category.id"
                                           v-model="selectedCategories"
                                           class="form-checkbox h-5 w-5 text-pink-200 focus:ring-pink-500 border-white/70 rounded">
                                    <label :for="'category_' + category.id" class="ml-2 font-rubik-light text-lg text-white/80">
                                        {{ categoryTranslations[category.name.toLowerCase()] || category.name }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 sm:mt-6">
                            <h3 class="font-rubik-medium text-lg text-white/90 mb-2">Цена</h3>
                            <div class="flex items-center gap-2 mb-1">
                                <label for="min_price" class="font-rubik-light text-lg text-white/80">От:</label>
                                <input type="number" id="min_price" v-model="minPrice"
                                       class="bg-darkBlue text-white border border-white/30 rounded py-2 px-3 w-full focus:outline-none focus:ring-pink-500 focus:border-pink-500 text-sm">
                            </div>
                            <div class="flex items-center gap-2">
                                <label for="max_price" class="font-rubik-light text-lg text-white/80">До:</label>
                                <input type="number" id="max_price" v-model="maxPrice"
                                       class="bg-darkBlue text-white border border-white/30 rounded py-2 px-3 w-full focus:outline-none focus:ring-pink-500 focus:border-pink-500 text-sm">
                            </div>
                        </div>

                        <button type="submit" class="bg-blue-600/20 text-white border-2 border-white rounded-md py-3 px-6 font-rubik-semibold text-lg hover:bg-blue-600/30 transition mt-4 w-full">Применить</button>
                        <button type="button" @click="resetFilters" class="font-rubik-light text-lg text-pink-200 hover:opacity-80 transition mt-2 w-full text-center">Сбросить</button>
                    </form>
                </aside>

                <section class="md:col-span-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6 sm:gap-8">
                        <div v-if="products.data.length === 0" class="font-rubik-light text-lg text-white/80 col-span-full">
                            Нет товаров, соответствующих выбранным фильтрам.
                        </div>
                        <div v-else v-for="product in products.data" :key="product.id"
                             class="bg-Wblue rounded-xl border-2 border-white/50 overflow-hidden flex flex-col">
                            
                            <div class="w-full h-48 overflow-hidden flex items-center justify-center p-2"> <img :src="product.image" :alt="product.name" class="w-full h-full object-contain">
                            </div>
                            
                            <div class="p-3 sm:p-4 flex-grow flex flex-col justify-between"> <h3 class="font-rubik-semibold text-xl text-white mb-1">{{ product.name }}</h3> <p class="font-rubik-semibold text-xl text-pink-200">{{ product.price.toLocaleString('ru-RU') }} ₽</p>
                                <Link :href="'/products/' + product.slug" class="inline-block bg-blue-600/20 text-white border-2 border-white rounded-md py-1.5 px-3 font-rubik-light text-lg hover:bg-blue-600/30 transition mt-1.5 text-center">Подробнее</Link> </div>
                        </div>
                    </div>

                    <div v-if="products.links.length > 3" class="mt-8 flex justify-center">
                        <div class="flex flex-wrap -mb-1">
                            <template v-for="(link, key) in products.links">
                                <div v-if="link.url === null" :key="key"
                                     class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                                     v-html="link.label" />
                                <Link v-else :key="'link-' + key"
                                      class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                                      :class="{ 'bg-white text-indigo-500': link.active, 'text-white': !link.active }"
                                      :href="link.url" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

<style scoped>
.bg-Wblue {
    background-color: #011F41;
}
.bg-darkBlue {
    background-color: #001A33;
}
</style>