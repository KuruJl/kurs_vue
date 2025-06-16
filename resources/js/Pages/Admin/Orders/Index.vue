<script setup>
import { defineProps } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  orders: Object,
  availableStatuses: Array, // Это не используется в этом компоненте, но мы его оставим, так как он приходит
});
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
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ order.status }}
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
              <Link
                v-for="link in orders.links"
                :key="link.label"
                :href="link.url"
                v-html="link.label"
                class="px-4 py-2 text-sm leading-5 rounded-md focus:outline-none transition ease-in-out duration-150"
                :class="{ 'bg-indigo-500 text-white': link.active, 'text-gray-700 hover:bg-gray-100': !link.active }"
              />
            </div>
          </div>
          <p v-else class="text-gray-600">Заказов пока нет.</p>
        </div>
      </div>
    </div>
  </div>
</template>