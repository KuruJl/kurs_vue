<script setup>
import { defineProps, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  order: Object, // Может быть null, если ID заказа не найден!
  availableStatuses: Array,
});

// ИСПРАВЛЕНИЕ: Безопасная инициализация формы.
// Если props.order существует, используем его статус, иначе - 'pending' (или любой другой статус по умолчанию).
const form = useForm({
  status: props.order ? props.order.status : 'pending', 
});

const updateOrderStatus = () => {
  form.put(`/admin/orders/${props.order.id}/update-status`, {
    onSuccess: () => {
      // Это сработает, если Inertia обновляет пропсы.
      // Если пропсы не обновляются автоматически, и вы хотите видеть свежий статус,
      // возможно, придется перенаправить или перезагрузить только данные.
      // Если форма очищается после успеха, а пропсы не обновляются,
      // то `form.status` может вернуться к 'pending', пока вы не перейдете снова.
      // Обычно Inertia.js автоматически обновляет пропсы после PUT/POST/DELETE.
      if (props.order) { // Проверяем, что order все еще существует
        form.status = props.order.status; 
      }
    },
    onError: (errors) => {
      console.error('Ошибка обновления статуса:', errors);
      // Если есть ошибка, статус в форме остается старым или отображает ошибку
    },
  });
};
</script>

<template>
  <Head :title="`Заказ №${order?.order_number || 'Неизвестный заказ'}`" /> <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <div v-if="order">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">Детали заказа №{{ order.order_number }}</h2>
            <p class="mb-2"><strong>ID заказа:</strong> {{ order.id }}</p>
            <p class="mb-2"><strong>Текущий статус:</strong> {{ order.status }}</p>
            <p class="mb-2"><strong>Общая сумма:</strong> {{ order.total_amount }}</p>
            <p class="mb-2"><strong>Дата создания:</strong> {{ order.created_at }}</p>

            <h3 class="font-semibold text-lg text-gray-800 leading-tight mt-6 mb-2">Информация о клиенте</h3>
            <p class="mb-2"><strong>Имя:</strong> {{ order.user.name }}</p>
            <p class="mb-2"><strong>Email:</strong> {{ order.user.email }}</p>

            <h3 class="font-semibold text-lg text-gray-800 leading-tight mt-6 mb-2">Товары в заказе</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Изображение
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Название товара
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Количество
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Цена за ед.
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in order.items" :key="item.product_id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <img :src="item.product_image_url" alt="Изображение товара" class="w-16 h-16 object-cover rounded" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <Link v-if="item.product_slug" :href="`/products/${item.product_slug}`" class="text-blue-600 hover:underline">
                        {{ item.product_name }}
                      </Link>
                      <span v-else>{{ item.product_name }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ item.quantity }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ item.price }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <h3 class="font-semibold text-lg text-gray-800 leading-tight mt-6 mb-2">Изменить статус заказа</h3>
            <form @submit.prevent="updateOrderStatus" class="mt-4 flex items-center space-x-4">
              <label for="status" class="block text-sm font-medium text-gray-700">Новый статус:</label>
              <select
                id="status"
                v-model="form.status"
                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option v-for="statusOption in availableStatuses" :key="statusOption" :value="statusOption">
                  {{ statusOption }}
                </option>
              </select>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <span v-if="form.processing">Обновление...</span>
                <span v-else>Обновить статус</span>
              </button>
            </form>
            <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
          </div>
          <div v-else>
            <p class="text-red-600 text-lg">Заказ не найден или произошла ошибка при загрузке.</p>
          </div>

          <Link href="/admin/orders" class="mt-8 inline-block text-indigo-600 hover:underline">
            &larr; Назад к списку заказов
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>