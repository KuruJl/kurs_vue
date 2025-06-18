<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { watch, ref } from 'vue'; // <-- Добавляем ref для предпросмотра
import AppHeader from '../../Header.vue';
import AppFooter from '../../Footer.vue';

const props = defineProps({
    categories: Array,
});

const form = useForm({
    name: '',
    description: '',
    price: '',
    category_id: '',
    feature: '',
    quantity: '',
    slug: '',
    images: [],
});

// <-- Добавляем ref для хранения URL-адресов предпросмотра
const imagePreviews = ref([]);

watch(() => form.name, (newName) => {
    form.slug = newName
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
});

const submit = () => {
    form.post('/admin/products', {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            imagePreviews.value = []; // Очищаем превью после успешной отправки
        },
    });
};

// <-- Обновляем обработчик, чтобы он создавал превью
const handleImageUpload = (event) => {
    form.images = Array.from(event.target.files);
    imagePreviews.value = []; // Очищаем старые

    form.images.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};
</script>

<template>
    <Head title="Создать новый товар" />
    <AppHeader />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form @submit.prevent="submit">
                    <!-- Все поля формы остаются без изменений -->
                    <div class="mb-4">
                        <label for="name" class="block font-medium text-sm text-gray-700">Название продукта</label>
                        <input id="name" type="text" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" v-model="form.name" required autofocus />
                        <div v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</div>
                    </div>
                    <div class="mb-4">
                        <label for="slug" class="block font-medium text-sm text-gray-700">Slug (ЧПУ)</label>
                        <input id="slug" type="text" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" v-model="form.slug" required />
                        <div v-if="form.errors.slug" class="text-sm text-red-600 mt-2">{{ form.errors.slug }}</div>
                        <p class="text-xs text-gray-500 mt-1">Отображается в URL.</p>
                    </div>
                    <!-- ... и остальные поля ... -->
                    <div class="mb-4">
                        <label for="description" class="block font-medium text-sm text-gray-700">Описание</label>
                        <textarea id="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" v-model="form.description" rows="5" required></textarea>
                        <div v-if="form.errors.description" class="text-sm text-red-600 mt-2">{{ form.errors.description }}</div>
                    </div>
                    <div class="mb-4">
                        <label for="price" class="block font-medium text-sm text-gray-700">Цена</label>
                        <input id="price" type="number" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" v-model="form.price" step="0.01" required />
                        <div v-if="form.errors.price" class="text-sm text-red-600 mt-2">{{ form.errors.price }}</div>
                    </div>
                    <div class="mb-4">
                        <label for="category_id" class="block font-medium text-sm text-gray-700">Категория</label>
                        <select id="category_id" v-model="form.category_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                            <option value="" disabled>Выберите категорию</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                        </select>
                        <div v-if="form.errors.category_id" class="text-sm text-red-600 mt-2">{{ form.errors.category_id }}</div>
                    </div>
                    <div class="mb-4">
                        <label for="feature" class="block font-medium text-sm text-gray-700">Характеристики</label>
                        <textarea id="feature" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" v-model="form.feature" rows="3"></textarea>
                        <div v-if="form.errors.feature" class="text-sm text-red-600 mt-2">{{ form.errors.feature }}</div>
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="block font-medium text-sm text-gray-700">Количество</label>
                        <input id="quantity" type="number" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" v-model="form.quantity" required />
                        <div v-if="form.errors.quantity" class="text-sm text-red-600 mt-2">{{ form.errors.quantity }}</div>
                    </div>

                    <div class="mb-4">
                        <label for="images" class="block font-medium text-sm text-gray-700">Изображения</label>
                        <input id="images" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" @change="handleImageUpload" multiple accept="image/*" />
                        <div v-if="form.errors.images" class="text-sm text-red-600 mt-2">{{ form.errors.images }}</div>
                        <template v-if="form.errors['images.0']"><div class="text-sm text-red-600 mt-2">{{ form.errors['images.0'] }}</div></template>
                    </div>

                    <!-- === Блок для предпросмотра изображений === -->
                    <div v-if="imagePreviews.length > 0" class="mb-4 p-4 border-dashed border-2 border-gray-200 rounded-lg">
                        <p class="font-medium text-sm text-gray-700 mb-2">Предпросмотр:</p>
                        <div class="flex flex-wrap gap-4">
                            <div v-for="(preview, index) in imagePreviews" :key="index" class="w-24 h-24 border rounded-md overflow-hidden flex items-center justify-center">
                                <img :src="preview" alt="Предпросмотр" class="max-w-full max-h-full object-contain">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Создать продукт
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <AppFooter />
</template>