<script setup>
// import { defineProps } from 'vue'; // Можете удалить defineProps, это просто предупреждение
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  orders: Object,
  availableStatuses: Array, 
});

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
</script>

<template>
  <Head title="Управление заказами" />

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">Управление заказами</h2>

          <div v-if="orders.data.length">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Номер заказа
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Пользователь
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Сумма
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Статус
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Дата
                  </th>
                  <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Действия</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="order in orders.data" :key="order.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ order.order_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ order.user_name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ order.total_amount }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusColorClass(order.status)]">
                      {{ order.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ order.created_at }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link :href="`/admin/orders/${order.id}`" class="text-indigo-600 hover:text-indigo-900">
                      Посмотреть
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="mt-4 flex justify-between items-center">
              <template v-for="link in orders.links" :key="link.label">
                <Link
                  v-if="link.url" :href="link.url"
                  v-html="link.label"
                  class="px-4 py-2 text-sm leading-5 rounded-md focus:outline-none transition ease-in-out duration-150"
                  :class="{ 'bg-indigo-500 text-white': link.active, 'text-gray-700 hover:bg-gray-100': !link.active }"
                />
                <span 
                  v-else 
                  v-html="link.label" 
                  class="px-4 py-2 text-sm leading-5 rounded-md text-gray-400 cursor-not-allowed"
                ></span>
              </template>
            </div>
          </div>
          <p v-else class="text-gray-600">Заказов пока нет.</p>
        </div>
      </div>
    </div>
  </div>
</template>