<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// 1. Определяем пропсы, которые приходят от контроллера
const props = defineProps({
  order: Object, // Данные заказа (могут быть null)
  availableStatuses: Array, // Массив доступных статусов
});


// --- СУЩЕСТВУЮЩИЕ ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ (оставляем без изменений) ---

// Получает английский ключ статуса из русского названия
const getEnglishStatusKey = (translatedStatus) => {
  switch (translatedStatus) {
    case 'В ожидании': return 'pending';
    case 'В обработке': return 'processing';
    case 'Отправлен': return 'shipped';
    case 'Доставлен': return 'delivered';
    case 'Отменен': return 'cancelled';
    default: return translatedStatus;
  }
};

// Получает русское название статуса из английского ключа
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

// Получает CSS-класс для цвета статуса
const getStatusColorClass = (status) => {
  switch (status) {
    case 'В ожидании': return 'bg-blue-100 text-blue-800';
    case 'В обработке': return 'bg-yellow-100 text-yellow-800';
    case 'Отправлен': return 'bg-purple-100 text-purple-800';
    case 'Доставлен': return 'bg-green-100 text-green-800';
    case 'Отменен': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};


// --- УПРАВЛЕНИЕ СТАТУСОМ ЗАКАЗА ---

// Создаем отдельную форму для обновления статуса
const statusForm = useForm({
  status: props.order ? getEnglishStatusKey(props.order.status) : 'pending',
});

// Функция для отправки формы обновления статуса
const updateOrderStatus = () => {
  statusForm.put(`/admin/orders/${props.order.id}/update-status`);
};


// --- НОВАЯ ЛОГИКА: УПРАВЛЕНИЕ ТОВАРАМИ В ЗАКАЗЕ ---

// Создаем вторую, отдельную форму для управления списком товаров
const itemsForm = useForm({
    // Глубокое копирование массива, чтобы изменения не затрагивали исходные props
    items: props.order ? JSON.parse(JSON.stringify(props.order.items)) : []
});

// Функция для "удаления" товара (устанавливаем его количество в 0)
const removeItem = (itemId) => {
    const item = itemsForm.items.find(i => i.id === itemId);
    if (item) {
        item.quantity = 0;
    }
};

// Вычисляемое свойство, чтобы отслеживать, были ли изменения в составе заказа
const hasChanges = computed(() => {
    if (!props.order) return false;
    // Сравниваем строковые представления исходного массива и текущего
    return JSON.stringify(props.order.items) !== JSON.stringify(itemsForm.items);
});

// Вычисляемое свойство для подсчета новой итоговой суммы "на лету"
const newTotalAmount = computed(() => {
    if (!itemsForm.items) return '0.00';
    return itemsForm.items.reduce((total, item) => {
        return total + (item.price * item.quantity);
    }, 0).toFixed(2);
});

// Функция для отправки формы с изменениями в составе заказа
const submitItemChanges = () => {
    itemsForm.put(`/admin/orders/${props.order.id}/update-items`, {
        preserveScroll: true, // Не прокручивать страницу после обновления
        onSuccess: () => {
            // После успеха обновляем состояние формы из свежих пропсов,
            // чтобы кнопка "Сохранить" снова стала неактивной.
            if (props.order) {
                itemsForm.defaults('items', JSON.parse(JSON.stringify(props.order.items)));
                itemsForm.reset();
            }
        }
    });
};
</script>

<template>
  <Head :title="`Заказ №${order?.order_number || 'Неизвестный заказ'}`" />
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div v-if="order" class="p-6 text-gray-900">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">Детали заказа №{{ order.order_number }}</h2>
          <p class="mb-2"><strong>ID заказа:</strong> {{ order.id }}</p>
          <p class="mb-2"><strong>Текущий статус:</strong> <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusColorClass(order.status)]">{{ order.status }}</span></p>
          <p class="mb-2"><strong>Общая сумма:</strong> {{ order.total_amount }}</p>
          <p class="mb-2"><strong>Дата создания:</strong> {{ order.created_at }}</p>
          <h3 class="font-semibold text-lg text-gray-800 leading-tight mt-6 mb-2">Информация о клиенте</h3>
          <p class="mb-2"><strong>Имя:</strong> {{ order.user.name }}</p>
          <p class="mb-2"><strong>Email:</strong> {{ order.user.email }}</p>

          <form @submit.prevent="submitItemChanges">
            <h3 class="font-semibold text-lg text-gray-800 leading-tight mt-6 mb-2">Товары в заказе</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Изображение</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Количество</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена за ед.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in itemsForm.items" :key="item.id" v-show="item.quantity > 0">
                    <td class="px-6 py-4"><img :src="item.product_image_url" :alt="item.product_name" class="w-16 h-16 object-cover rounded" /></td>
                    <td class="px-6 py-4">
                        <Link v-if="item.product_slug" :href="`/products/${item.product_slug}`" class="text-blue-600 hover:underline">{{ item.product_name }}</Link>
                        <span v-else>{{ item.product_name }}</span>
                    </td>
                    <td class="px-6 py-4">
                      <input type="number" v-model.number="item.quantity" min="0" class="w-20 border-gray-300 rounded-md shadow-sm text-sm" />
                    </td>
                    <td class="px-6 py-4">{{ item.price }}</td>
                    <td class="px-6 py-4 font-semibold">{{ (item.price * item.quantity).toFixed(2) }}</td>
                    <td class="px-6 py-4">
                      <button type="button" @click="removeItem(item.id)" class="text-red-600 hover:text-red-900 text-sm">Удалить</button>
                    </td>
                  </tr>
                   <tr class="bg-gray-50 font-bold">
                        <td colspan="4" class="px-6 py-3 text-right">Новая итоговая сумма:</td>
                        <td colspan="2" class="px-6 py-3 text-left">{{ newTotalAmount }}</td>
                   </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-4 flex justify-end">
                <button
                    type="submit"
                    :disabled="!hasChanges || itemsForm.processing"
                    :class="['px-4 py-2 text-sm font-medium rounded-md', 
                        !hasChanges || itemsForm.processing ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-600 text-white hover:bg-green-700']"
                >
                    <span v-if="itemsForm.processing">Сохранение...</span>
                    <span v-else>Сохранить изменения в заказе</span>
                </button>
            </div>
          </form>

          <h3 class="font-semibold text-lg text-gray-800 leading-tight mt-10 mb-2">Изменить статус заказа</h3>
          
          <!-- === ВОТ ВОССТАНОВЛЕННЫЙ БЛОК === -->
          <form @submit.prevent="updateOrderStatus" class="mt-4 flex items-center space-x-4">
              <label for="status" class="block text-sm font-medium text-gray-700">Новый статус:</label>
              <select 
                id="status" 
                v-model="statusForm.status" 
                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option v-for="statusKey in availableStatuses" :key="statusKey" :value="statusKey">
                    {{ getTranslatedStatus(statusKey) }}
                </option>
              </select>
              <button 
                type="submit" 
                :disabled="statusForm.processing" 
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <span v-if="statusForm.processing">Обновление...</span>
                <span v-else>Обновить статус</span>
              </button>
          </form>
          <!-- ================================== -->
          
          <div v-if="statusForm.errors.status" class="text-red-500 text-sm mt-1">{{ statusForm.errors.status }}</div>
        </div>

        <div v-else class="p-6 text-gray-900">
          <p class="text-red-600 text-lg">Заказ не найден или произошла ошибка при загрузке.</p>
        </div>

        <div class="p-6 pt-0">
            <Link href="/admin/orders" class="inline-block text-indigo-600 hover:underline">
                ← Назад к списку заказов
            </Link>
        </div>
      </div>
    </div>
  </div>
</template>