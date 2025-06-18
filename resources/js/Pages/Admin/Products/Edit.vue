<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { watch, ref } from 'vue';

const props = defineProps({
    product: Object,
    categories: Array,
});

const form = useForm({
    _method: 'put',
    name: props.product.name,
    slug: props.product.slug,
    description: props.product.description,
    price: props.product.price,
    category_id: props.product.category_id,
    feature: props.product.feature,
    quantity: props.product.quantity,
    new_images: [],
    deleted_images: [],
});

const newImagePreviews = ref([]);
const currentImages = ref(props.product.images || []);

watch(() => form.name, (newName) => {
    const currentSlugFromOldName = props.product.name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');

    if (!form.slug || form.slug === currentSlugFromOldName) {
        form.slug = newName
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }
});

const handleNewImageUpload = (event) => {
    form.new_images = Array.from(event.target.files);
    newImagePreviews.value = [];

    form.new_images.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            newImagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const deleteImage = (imageId) => {
    form.deleted_images.push(imageId);
    currentImages.value = currentImages.value.filter(image => image.id !== imageId);
};

const submit = () => {
    // ИЗМЕНЕНИЕ 2: Убираем Ziggy
    form.post(`/admin/products/${props.product.id}`, { // БЫЛО: route('admin.products.update', props.product.id)
        forceFormData: true,
        onSuccess: () => {
           // Перенаправление происходит в контроллере
        },
        onError: (errors) => {
            console.error("Ошибки при отправке формы:", errors);
        }
    });
};
</script>

<template>
    <Head :title="`Редактировать товар: ${product.name}`" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-semibold mb-6">Редактировать товар: {{ product.name }}</h2>

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
                    <div class="mb-4">
                        <label for="description" class="block font-medium text-sm text-gray-700">Описание</label>
                        <textarea id="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" v-model="form.description" rows="5" required></textarea>
                        <div v-if="form.errors.description" class="text-sm text-red-600 mt-2">{{ form.errors.description }}</div>
                    </div>
                    <!-- ... и остальные поля ... -->
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
                        <label class="block font-medium text-sm text-gray-700 mb-2">Существующие изображения</label>
                        <div v-if="currentImages.length > 0" class="flex flex-wrap gap-4">
                            <div v-for="image in currentImages" :key="image.id" class="relative w-32 h-32 border rounded-md overflow-hidden flex items-center justify-center">
                                <!-- ИЗМЕНЕНИЕ 1: Исправляем путь к изображению -->
                                <img :src="image.path" :alt="product.name" class="max-w-full max-h-full object-contain"> <!-- БЫЛО: `/storage/${image.path}` -->
                                <button
                                    type="button"
                                    @click="deleteImage(image.id)"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs leading-none"
                                    title="Удалить изображение"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                        <p v-else class="text-gray-500 text-sm">Нет загруженных изображений.</p>
                    </div>

                    <div class="mb-4">
                        <label for="new_images" class="block font-medium text-sm text-gray-700">Загрузить новые изображения</label>
                        <input id="new_images" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" @change="handleNewImageUpload" multiple accept="image/*" />
                        <div v-if="form.errors.new_images" class="text-sm text-red-600 mt-2">{{ form.errors.new_images }}</div>
                        <template v-if="form.errors['new_images.0']"><div class="text-sm text-red-600 mt-2">{{ form.errors['new_images.0'] }}</div></template>
                        <div v-if="newImagePreviews.length > 0" class="flex flex-wrap gap-4 mt-4">
                            <div v-for="(preview, index) in newImagePreviews" :key="index" class="w-32 h-32 border rounded-md overflow-hidden flex items-center justify-center">
                                <img :src="preview" alt="Предпросмотр нового изображения" class="max-w-full max-h-full object-contain">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Обновить продукт
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>