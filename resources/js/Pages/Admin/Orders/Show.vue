<script setup>
// import { defineProps, ref } from 'vue'; // Можно убрать defineProps
import { ref } from 'vue'; // ref нужен, если используется
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  order: Object, // Может быть null, если ID заказа не найден!
  availableStatuses: Array, // Это массив английских ключей статусов для <select>
});

// ИСПРАВЛЕНИЕ: Безопасная инициализация формы.
// Если props.order существует, используем его статус, иначе - 'pending' (или любой другой статус по умолчанию).
// ВАЖНО: для useForm.status мы используем **английский** ключ статуса,
// так как именно его мы отправляем на бэкенд.
const form = useForm({
  status: props.order ? getEnglishStatusKey(props.order.status) : 'pending', 
});

const updateOrderStatus = () => {
  form.put(`/admin/orders/${props.order.id}/update-status`, {
    onSuccess: () => {
      // После успешного обновления, Inertia обновит пропсы,
      // и компонент сам перерендерится с новым переведенным статусом.
      // Поэтому здесь напрямую обновлять form.status не нужно.
      // Если это не происходит автоматически, проверьте, что ваш контроллер
      // возвращает полную Inertia::render страницу после успешного обновления.
    },
    onError: (errors) => {
      console.error('Ошибка обновления статуса:', errors);
      // Если есть ошибка, статус в форме остается старым или отображает ошибку
    },
  });
};
const getTranslatedStatus = (statusKey) => {
  switch (statusKey) {
    case 'pending': return 'В ожидании';
    case 'processing': return 'В обработке';
    case 'shipped': return 'Отправлен';
    case 'delivered': return 'Доставлен';
    case 'cancelled': return 'Отменен';
    default: return statusKey;
  }
};
// Функция для получения класса цвета статуса
const getStatusColorClass = (status) => {
  switch (status) {
    case 'В ожидании':
      return 'bg-blue-100 text-blue-800';
    case 'В обработке':
      return 'bg-yellow-100 text-yellow-800';
    case 'Отправлен':
      return 'bg-purple-100 text-purple-800';
    case 'Доставлен':
      return 'bg-green-100 text-green-800';
    case 'Отменен':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

// Вспомогательная функция для получения английского ключа из русского перевода
// Это нужно для того, чтобы selected value в <select> соответствовало английскому ключу.
// Вам нужно будет убедиться, что `availableStatuses` в вашем контроллере
// по-прежнему передаются как английские ключи (pending, processing и т.д.).
const getEnglishStatusKey = (translatedStatus) => {
  switch (translatedStatus) {
    case 'В ожидании': return 'pending';
    case 'В обработке': return 'processing';
    case 'Отправлен': return 'shipped';
    case 'Доставлен': return 'delivered';
    case 'Отменен': return 'cancelled';
    default: return translatedStatus; // Если не найдено, возвращаем как есть (на случай если это уже англ. ключ)
  }
};
</script>

<template>
  <Head :title="`Заказ №${order?.order_number || 'Неизвестный заказ'}`" />
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <div v-if="order">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">Детали заказа №{{ order.order_number }}</h2>
            <p class="mb-2"><strong>ID заказа:</strong> {{ order.id }}</p>
            <p class="mb-2">
                <strong>Текущий статус:</strong> 
                <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusColorClass(order.status)]">
                    {{ order.status }}
                </span>
            </p>
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
              <option v-for="statusKey in availableStatuses" :key="statusKey" :value="statusKey">
  {{ getTranslatedStatus(statusKey) }}
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