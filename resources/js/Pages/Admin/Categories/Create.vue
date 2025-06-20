<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const form = useForm({
    name: '',
    slug: '',
});

// Автогенерация slug
watch(() => form.name, (newName) => {
    form.slug = newName.toLowerCase().trim().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
});

const submit = () => {
    form.post('/admin/categories');
};
</script>

<template>
    <Head title="Создать категорию" />
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="font-semibold text-xl mb-4">Создать новую категорию</h2>
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
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-black rounded-md hover:bg-blue-700">Создать</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>