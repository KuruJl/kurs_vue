<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import AppHeader from '../../Header.vue';
import AppFooter from '../../Footer.vue';

const props = defineProps({
    products: Object,
    categories: Array,
    query: Object,
});

// --- Логика сортировки и фильтрации (без изменений) ---
const sortColumn = ref(props.query.sort || 'id');
const sortDirection = ref(props.query.direction || 'desc');
const filters = ref({
  search: props.query.search || '',
  category_id: props.query.category_id || '',
});

const sortBy = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
};

let debounceTimeout = null;
watch(
  [filters, sortColumn, sortDirection],
  ([newFilters, newSortColumn, newSortDirection]) => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
      router.get(
        '/admin/products',
        {
          ...newFilters,
          sort: newSortColumn,
          direction: newSortDirection,
          page: 1,
        },
        { preserveState: true, replace: true }
      );
    }, 300);
  },
  { deep: true }
);

const resetFilters = () => {
    filters.value.search = '';
    filters.value.category_id = '';
};

const deleteProduct = (productId) => {
    if (confirm('Вы уверены, что хотите удалить этот товар? Это действие необратимо.')) {
        router.delete(`/admin/products/${productId}`, {
            onSuccess: () => {
                alert('Товар успешно удален!');
            },
            onError: (errors) => {
                console.error('Ошибка при удалении:', errors);
                alert('Произошла ошибка при удалении товара.');
            }
        });
    }
};

// --- ИЗМЕНЕНИЕ ЗДЕСЬ ---
// Вспомогательная функция для изображений
const getFirstImageUrl = (images) => {
    // Проверяем, есть ли массив images и есть ли в нем хотя бы один элемент
    if (images && images.length > 0 && images[0].path) {
        // Просто возвращаем путь как есть, так как он уже приходит в правильном формате из Laravel
        return images[0].path; // БЫЛО: `/images/${images[0].path}`
    }
    // Возвращаем путь к заглушке, если изображения нет.
    return '/images/default-product.png'; 
};
</script>

<template>
    <Head title="Управление товарами" />
    <AppHeader />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Заголовок и кнопки -->
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Список товаров</h3>
                        <div class="flex space-x-4">
                            <Link href="/admin/products/create" class="bg-blue-500 hover:bg-blue-600 text-black font-bold py-2 px-4 rounded">
                                Добавить товар
                            </Link>
                            <Link href="/admin/orders" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                Управление заказами
                            </Link>
                        </div>
                    </div>

                    <!-- Форма фильтров -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg flex items-center gap-4">
                        <div class="flex-grow">
                            <input type="text" v-model="filters.search" placeholder="Поиск по названию..." class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <select v-model="filters.category_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Все категории</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select>
                        </div>
                        <button @click="resetFilters" class="text-sm text-gray-600 hover:text-gray-900">Сбросить</button>
                    </div>

                    <!-- Таблица -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Изображение</th>
                                    <th @click="sortBy('id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">ID <span v-if="query.sort === 'id'">{{ query.direction === 'asc' ? '▲' : '▼' }}</span></th>
                                    <th @click="sortBy('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">Название <span v-if="query.sort === 'name'">{{ query.direction === 'asc' ? '▲' : '▼' }}</span></th>
                                    <th @click="sortBy('price')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">Цена <span v-if="query.sort === 'price'">{{ query.direction === 'asc' ? '▲' : '▼' }}</span></th>
                                    <th @click="sortBy('quantity')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">Кол-во <span v-if="query.sort === 'quantity'">{{ query.direction === 'asc' ? '▲' : '▼' }}</span></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody v-if="products.data.length" class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products.data" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap"><img :src="getFirstImageUrl(product.images)" alt="Product Image" class="w-12 h-12 object-cover rounded"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ product.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ product.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ product.price }} ₽</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ product.quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                        <Link :href="`/admin/products/${product.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Редактировать</Link>
                                        <button @click="deleteProduct(product.id)" class="text-red-600 hover:text-red-900">Удалить</button>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">Товаров, соответствующих фильтрам, не найдено.</td>
</tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="products.data.length" class="mt-4">
                        <Pagination :links="products.links" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <AppFooter />
</template>