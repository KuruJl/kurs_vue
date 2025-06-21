<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppHeader from '../../Header.vue';
import AppFooter from '../../Footer.vue';
const props = defineProps({
    category: Object,
});

const form = useForm({
    name: props.category.name,
    slug: props.category.slug,
});

const submit = () => {
    form.put(`/admin/categories/${props.category.id}`);
};
</script>

<template>
    <AppHeader />

    <Head :title="`Редактировать: ${category.name}`" />
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="font-semibold text-xl mb-4">Редактировать категорию</h2>
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Название</label>
                            <input type="text" v-model="form.name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>
                        <div class="mb-4">
                            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                            <input type="text" v-model="form.slug" id="slug" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <div v-if="form.errors.slug" class="text-red-500 text-xs mt-1">{{ form.errors.slug }}</div>
                        </div>
                        <div class="flex items-center justify-end gap-4">
                            <Link href="/admin/categories" class="text-sm text-gray-600 hover:underline">Отмена</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-black rounded-md hover:bg-blue-700">Обновить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <AppFooter />

</template>