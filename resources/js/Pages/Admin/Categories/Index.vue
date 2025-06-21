<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router  } from '@inertiajs/vue3';
import AppHeader from '../../Header.vue';
import AppFooter from '../../Footer.vue';
defineProps({
    categories: Object,
});

const deleteCategory = (categoryId) => {
    if (confirm('Вы уверены, что хотите удалить эту категорию?')) {
        router.delete(`/admin/categories/${categoryId}`);
    }
};
</script>

<template>
    <AppHeader />

    <Head title="Управление категориями" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-semibold text-xl">Управление категориями</h2>
                        <Link href="/admin/categories/create" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                            Добавить категорию
                        </Link>
                    </div>

                    <!-- Отображение ошибок, если они есть -->
                    <div v-if="$page.props.errors.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        {{ $page.props.errors.error }}
                    </div>
                     <!-- Отображение flash-сообщений -->
                    <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                        {{ $page.props.flash.success }}
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                                <th class="relative px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="category in categories.data" :key="category.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ category.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ category.slug }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="`/admin/categories/${category.id}/edit`" class="text-indigo-600 hover:text-indigo-900 mr-4">Редактировать</Link>
                                    <button @click="deleteCategory(category.id)" class="text-red-600 hover:text-red-900">Удалить</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Пагинация, если нужна -->
                </div>
            </div>
        </div>
    </div>
    <AppFooter />

</template>