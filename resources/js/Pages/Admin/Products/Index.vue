<script setup>
import { Head, Link, router } from '@inertiajs/vue3'; // <--- Добавьте 'router' сюда
import { ref } from 'vue';
import Pagination from '@/Components/Pagination.vue'; // <-- ДОБАВЬТЕ ЭТУ СТРОКУ
import AppHeader from '../../Header.vue'; // <--- Исправленный путь
import AppFooter from '../../Footer.vue'; // <--- Исправленный путь

defineProps({
    products: Object
});
const deleteProduct = (productId) => {
    if (confirm('Вы уверены, что хотите удалить этот товар? Это действие необратимо.')) {
        router.delete(route('admin.products.destroy', productId), {
            onSuccess: () => {
                // Можно добавить уведомление об успехе
                alert('Товар успешно удален!');
            },
            onError: (errors) => {
                // Обработка ошибок, например, если продукт не найден или нет прав
                console.error('Ошибка при удалении:', errors);
                alert('Произошла ошибка при удалении товара.');
            }
        });
    }
};
</script>

<template>
        <AppHeader />

        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between mb-6">
                            <h3 class="text-lg font-medium">Список товаров</h3>
<Link href="/admin/products/create" class="btn btn-primary">
                                Добавить товар
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in products.data" :key="product.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ product.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ product.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ product.price }} ₽</td>
                                        <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                            <Link :href="`/admin/products/${product.id}/edit`" class="text-indigo-600 hover:text-indigo-900">                                                Редактировать
                                            </Link>
                                            <button @click="deleteProduct(product.id)"
                                            class="text-red-600 hover:text-red-900">
                                        Удалить
                                    </button>   
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <Pagination :links="products.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <AppFooter />

</template>