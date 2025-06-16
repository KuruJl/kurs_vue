<script setup>
import { defineProps } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
});

const formatPrice = (value) => {
    return new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
};

</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-white">История заказов</h2>
            <p class="mt-1 text-sm text-gray-400">
                Здесь вы можете просмотреть историю ваших заказов.
            </p>
        </header>

        <div v-if="orders.length > 0" class="space-y-4">
            <div v-for="order in orders" :key="order.id" class="bg-[#011F41] p-4 sm:p-6 rounded-lg border border-white/30 shadow-md">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 pb-2 border-b border-white/20">
                    <div>
                        <h3 class="font-rubik-semibold text-xl text-white">Заказ №{{ order.order_number }}</h3>
                        <p class="text-sm text-gray-400">от {{ order.created_at }}</p>
                    </div>
                    <div class="mt-2 sm:mt-0 text-right">
                        <p class="font-rubik-semibold text-lg text-pink-200">Итого: {{ formatPrice(order.total_amount) }} ₽</p>
                        <span :class="{
                            'bg-yellow-600/30 text-yellow-200': order.status === 'pending',
                            'bg-blue-600/30 text-blue-200': order.status === 'processing',
                            'bg-green-600/30 text-green-200': order.status === 'completed',
                            'bg-red-600/30 text-red-200': order.status === 'cancelled',
                        }" class="px-2 py-1 rounded-full text-xs font-rubik-medium uppercase inline-block mt-1">
                            {{ order.status === 'pending' ? 'В ожидании' :
                               order.status === 'processing' ? 'В обработке' :
                               order.status === 'completed' ? 'Выполнен' :
                               order.status === 'cancelled' ? 'Отменен' : order.status
                            }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4 bg-white/5 p-3 rounded-md">
                        <Link :href="item.product_slug ? `/products/${item.product_slug}` : '#'"
                              class="flex-shrink-0 w-16 h-16 flex items-center justify-center overflow-hidden rounded-md bg-white/10">
                            <img :src="item.product_image_url" :alt="item.product_name" class="max-w-full max-h-full object-contain">
                        </Link>
                        <div class="flex-grow">
                            <Link :href="item.product_slug ? `/products/${item.product_slug}` : '#'"
                                  class="font-rubik-medium text-base text-white hover:text-pink-200 transition-colors">
                                {{ item.product_name }}
                            </Link>
                            <p class="text-sm text-gray-300">Кол-во: {{ item.quantity }}</p>
                        </div>
                        <p class="font-rubik-semibold text-base text-pink-200">{{ formatPrice(item.price * item.quantity) }} ₽</p>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="bg-[#011F41] p-6 rounded-lg border border-white/30 text-center">
            <p class="text-white/80 font-rubik-light">У вас пока нет заказов.</p>
        </div>
    </section>
</template>