<script setup>
// import { defineProps, ref, watch } from 'vue'; // Убрал defineProps
import { ref, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

// Правильные пути к AppHeader и AppFooter (как у вас)
import AppHeader from '../Header.vue';
import AppFooter from '../Footer.vue';

// ИМПОРТ КОМПОНЕНТА ЗАКАЗОВ - ПУТЬ ИСПРАВЛЕН
import OrderHistory from '../../Components/OrderHistory.vue'; // Предполагаем, что OrderHistory.vue находится в resources/js/Components

const page = usePage(); // Получаем доступ к объекту страницы напрямую

// Определяем пропсы, которые приходят из контроллера
const props = defineProps({
    user: Object, // Текущий пользователь
    success: String, // Этот пропс, возможно, не будет использоваться напрямую из-за page.props.flash
    errors: Object, // Ошибки валидации
    mustVerifyEmail: { // Добавляем из контроллера Breeze
        type: Boolean,
        default: false,
    },
    status: { // Добавляем из контроллера Breeze (для status сообщения)
        type: String,
        default: '',
    },
    orders: { // <--- Добавляем пропс для заказов, это объект с data, links и т.д. от пагинации
        type: Object, // Changed to Object since it's paginated data
        default: () => ({ data: [], links: [] }), // Default to empty object with data and links
    },
});

// Реактивная переменная для отслеживания активной вкладки
const currentTab = ref('profile'); // По умолчанию активна вкладка "Профиль"

// Формы и их логика остаются без изменений, так как они встроены в этот же компонент
const form = useForm({
    _method: 'patch',
    name: props.user ? props.user.name : '',
    email: props.user ? props.user.email : '',
    phone: props.user ? props.user.phone || '' : '',
});

const passwordForm = useForm({
    _method: 'put',
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showSuccessMessage = ref(false);

// Объединенный watch для сообщений об успехе (из props.success или flash messages)
watch(() => [props.success, page.props.flash?.success, page.props.flash?.status], ([newPropSuccess, newFlashSuccess, newFlashStatus]) => {
    if (newPropSuccess || newFlashSuccess || newFlashStatus) {
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);
    }
}, { immediate: true });

const updateProfileInformation = () => {
    form.post(route('profile.update'), {
        onSuccess: () => {
            showSuccessMessage.value = true;
            setTimeout(() => showSuccessMessage.value = false, 5000);

            if (page.props.auth.user) {
                form.name = page.props.auth.user.name;
                form.email = page.props.auth.user.email;
                form.phone = page.props.auth.user.phone || '';
            }
        },
        onError: (errors) => {
            console.error('Ошибка обновления информации профиля:', errors);
        },
    });
};

const updatePassword = () => {
    passwordForm.post(route('password.update'), {
        onSuccess: () => {
            passwordForm.reset();
            showSuccessMessage.value = true;
            setTimeout(() => showSuccessMessage.value = false, 5000);
        },
        onError: (errors) => {
            console.error('Ошибка обновления пароля:', errors);
        },
    });
};

// Добавим функцию для удаления пользователя, если у вас она есть
const deleteUser = () => {
    if (confirm('Вы уверены, что хотите удалить свой аккаунт? Все ваши данные будут безвозвратно удалены.')) {
        useForm().delete(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => {
                // Возможно, перенаправление или другое действие после удаления
            },
            onError: (errors) => {
                console.error('Ошибка удаления аккаунта:', errors);
            },
        });
    }
};

</script>

<template>
    <AppHeader />
    <Head title="Профиль" />

    <main class="w-full min-h-screen bg-Wblue pb-12"> 
        <h2 class="font-rubik-semibold mt-10 sm:mt-20 mb-8 sm:mb-16 text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-medium text-center text-rose-100">
            ПРОФИЛЬ
        </h2>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div v-if="showSuccessMessage || page.props.flash?.success || page.props.flash?.status"
                 class="bg-green-200 border-green-400 text-green-700 px-4 py-3 rounded relative mb-8" role="alert">
                <strong class="font-bold">Успешно!</strong>
                <span class="block sm:inline">{{ page.props.flash?.success || page.props.flash?.status || props.success || 'Данные успешно обновлены!' }}</span>
            </div>

            <div class="flex flex-wrap gap-4 mb-6">
                <button @click="currentTab = 'profile'"
                        :class="{ 'bg-blue-600/20 text-white border-white': currentTab === 'profile', 'text-white/70 border-white/30 hover:border-white/50': currentTab !== 'profile' }"
                        class="px-4 py-2 rounded-md transition-colors border-2 font-rubik-medium">
                    Информация о клиенте
                </button>
                <button @click="currentTab = 'password'"
                        :class="{ 'bg-blue-600/20 text-white border-white': currentTab === 'password', 'text-white/70 border-white/30 hover:border-white/50': currentTab !== 'password' }"
                        class="px-4 py-2 rounded-md transition-colors border-2 font-rubik-medium">
                    Изменить пароль
                </button>
                <button @click="currentTab = 'orders'"
                        :class="{ 'bg-blue-600/20 text-white border-white': currentTab === 'orders', 'text-white/70 border-white/30 hover:border-white/50': currentTab !== 'orders' }"
                        class="px-4 py-2 rounded-md transition-colors border-2 font-rubik-medium">
                    Мои заказы
                </button>
            </div>

            <div v-if="currentTab === 'profile'"
                 class="p-4 sm:p-8 bg-[#011F41] shadow sm:rounded-lg border-2 border-white/50">
                <form @submit.prevent="updateProfileInformation" class="space-y-8 mb-16">
                    <h3 class="font-rubik-semibold mb-6 sm:mb-10 text-2xl sm:text-3xl font-bold text-white">
                        Информация о клиенте
                    </h3>

                    <div class="grid gap-6 sm:gap-8">
                        <div class="grid gap-3 sm:gap-5 grid-cols-1 sm:grid-cols-[minmax(200px,300px)_1fr] items-center">
                            <label for="name" class="text-lg sm:text-xl md:text-2xl font-light text-white">ФИО:</label>
                            <div>
                                <input type="text" id="name" v-model="form.name"
                                    class="input-field font-rubik-semibold text-lg sm:text-xl md:text-2xl">
                                <div v-if="form.errors.name" class="text-red-400 mt-1">{{ form.errors.name }}</div>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:gap-5 grid-cols-1 sm:grid-cols-[minmax(200px,300px)_1fr] items-center">
                            <label for="email" class="text-lg sm:text-xl md:text-2xl font-light text-white">Электронная почта:</label>
                            <div>
                                <input type="email" id="email" v-model="form.email"
                                    class="input-field font-rubik-semibold text-lg sm:text-xl md:text-2xl break-all">
                                <div v-if="form.errors.email" class="text-red-400 mt-1">{{ form.errors.email }}</div>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:gap-5 grid-cols-1 sm:grid-cols-[minmax(200px,300px)_1fr] items-center">
                            <label for="phone" class="text-lg sm:text-xl md:text-2xl font-light text-white">Номер телефона:</label>
                            <div>
                                <input type="tel" id="phone" v-model="form.phone"
                                    class="input-field font-rubik-semibold text-lg sm:text-xl md:text-2xl">
                                <div v-if="form.errors.phone" class="text-red-400 mt-1">{{ form.errors.phone }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 mt-10">
                        <button type="submit"
                                :disabled="form.processing"
                                class="bg-blue-600/20 text-white border-2 border-white rounded-md py-3 px-8 sm:py-4 sm:px-10 font-rubik-semibold text-lg sm:text-xl hover:bg-blue-600/30 transition whitespace-nowrap"
                                :class="{ 'opacity-25': form.processing }">
                            Сохранить изменения
                        </button>
                        <button type="button" @click="form.reset()"
                                class="bg-transparent text-white border-2 border-white rounded-md py-3 px-8 sm:py-4 sm:px-10 font-rubik-semibold text-lg sm:text-xl hover:bg-white/10 transition whitespace-nowrap text-center">
                            Отменить
                        </button>
                    </div>
                </form>

                <div v-if="props.mustVerifyEmail && page.props.auth.user.email_verified_at === null" class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    Ваш адрес электронной почты не подтвержден.
                    <inertia-link :href="route('verification.send')" method="post" as="button" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        Нажмите здесь, чтобы повторно отправить письмо с подтверждением.
                    </inertia-link>
                    <div v-show="props.status === 'verification-link-sent'" class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                        Новая ссылка для подтверждения была отправлена на ваш адрес электронной почты.
                    </div>
                </div>
            </div>

            <div v-if="currentTab === 'password'"
                 class="p-4 sm:p-8 bg-[#011F41] shadow sm:rounded-lg border-2 border-white/50">
                <form @submit.prevent="updatePassword" class="space-y-8">
                    <h3 class="font-rubik-semibold mb-6 sm:mb-10 text-2xl sm:text-3xl font-bold text-white">
                        Изменить пароль
                    </h3>

                    <div class="grid gap-6 sm:gap-8">
                        <div class="grid gap-3 sm:gap-5 grid-cols-1 sm:grid-cols-[minmax(200px,300px)_1fr] items-center">
                            <label for="current_password" class="text-lg sm:text-xl md:text-2xl font-light text-white">Текущий пароль:</label>
                            <div>
                                <input type="password" id="current_password" v-model="passwordForm.current_password"
                                    class="input-field font-rubik-light text-lg sm:text-xl md:text-2xl">
                                <div v-if="passwordForm.errors.current_password" class="text-red-400 mt-1">{{ passwordForm.errors.current_password }}</div>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:gap-5 grid-cols-1 sm:grid-cols-[minmax(200px,300px)_1fr] items-center">
                            <label for="password" class="text-lg sm:text-xl md:text-2xl font-light text-white">Новый пароль:</label>
                            <div>
                                <input type="password" id="password" v-model="passwordForm.password" placeholder="Оставьте пустым, если не хотите менять"
                                    class="input-field font-rubik-light text-lg sm:text-xl md:text-2xl">
                                <div v-if="passwordForm.errors.password" class="text-red-400 mt-1">{{ passwordForm.errors.password }}</div>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:gap-5 grid-cols-1 sm:grid-cols-[minmax(200px,300px)_1fr] items-center">
                            <label for="password_confirmation" class="text-lg sm:text-xl md:text-2xl font-light text-white">Подтвердите пароль:</label>
                            <div>
                                <input type="password" id="password_confirmation" v-model="passwordForm.password_confirmation"
                                    class="input-field font-rubik-light text-lg sm:text-xl md:text-2xl">
                                <div v-if="passwordForm.errors.password_confirmation" class="text-red-400 mt-1">{{ passwordForm.errors.password_confirmation }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 mt-10">
                        <button type="submit"
                                :disabled="passwordForm.processing"
                                class="bg-blue-600/20 text-white border-2 border-white rounded-md py-3 px-8 sm:py-4 sm:px-10 font-rubik-semibold text-lg sm:text-xl hover:bg-blue-600/30 transition whitespace-nowrap"
                                :class="{ 'opacity-25': passwordForm.processing }">
                            Изменить пароль
                        </button>
                        <button type="button" @click="passwordForm.reset()"
                                class="bg-transparent text-white border-2 border-white rounded-md py-3 px-8 sm:py-4 sm:px-10 font-rubik-semibold text-lg sm:text-xl hover:bg-white/10 transition whitespace-nowrap text-center">
                            Отменить
                        </button>
                    </div>
                </form>
            </div>

            <div v-if="currentTab === 'orders'"
                 class="p-4 sm:p-8 bg-[#011F41] shadow sm:rounded-lg border-2 border-white/50">
                <h3 class="font-rubik-semibold mb-6 sm:mb-10 text-2xl sm:text-3xl font-bold text-white">
                    Мои заказы
                </h3>
                <OrderHistory :orders="props.orders" /> 
            </div>

            <div class="p-4 sm:p-8 bg-[#011F41] shadow sm:rounded-lg border-2 border-white/50">
                <h3 class="font-rubik-semibold mb-6 sm:mb-10 text-2xl sm:text-3xl font-bold text-white">
                    Удалить аккаунт
                </h3>
                <p class="mt-1 text-sm text-white">
                    После удаления вашего аккаунта все его ресурсы и данные будут безвозвратно удалены. Перед удалением аккаунта, пожалуйста, загрузите любые данные или информацию, которые вы хотите сохранить.
                </p>
                <button @click="deleteUser" class="mt-6 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Удалить аккаунт
                </button>
            </div>
        </div>
    </main>
    <AppFooter />
</template>

<style scoped>
.bg-Wblue { background-color: #011F41; }

.input-field {
    @apply block w-full rounded-md shadow-sm;
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.75rem 1rem;
}
.input-field:focus {
    @apply ring-indigo-500 border-indigo-500;
}
/* Font styles */
.font-rubik-semibold { font-family: 'Rubik', sans-serif; font-weight: 600; }
.font-rubik-medium { font-family: 'Rubik', sans-serif; font-weight: 500; }
.font-rubik-light { font-family: 'Rubik', sans-serif; font-weight: 300; }
</style>