<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppHeader from '../Header.vue';
import AppFooter from '../Footer.vue';
defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AppHeader />

    <Head title="Вход" />

    <!-- Обертка для стилизации фона и центрирования -->
    <div class="min-h-screen bg-Wblue flex flex-col items-center justify-center p-4 font-rubik-regular">

        <!-- Логотип (опционально, можно добавить) -->
        <div class="mb-8">
            <Link href="/">
                <!-- Если у вас есть SVG логотип, вставьте его сюда -->
                <h1 class="font-norwester text-4xl text-pink-200">hachiroku</h1>
            </Link>
        </div>

        <!-- Контейнер формы -->
        <div class="w-full max-w-md p-8 space-y-6 rounded-xl border-2 border-white/30 bg-[#001A33]/50">
            <h2 class="text-3xl font-rubik-semibold text-center text-white">
                Авторизация
            </h2>
            
            <div v-if="status" class="mb-4 text-sm font-medium text-green-400">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block mb-2 text-sm font-rubik-medium text-white/90">Email</label>
                    <input
                        id="email"
                        type="email"
                        class="w-full px-4 py-3 bg-transparent border border-white/30 text-white rounded-md focus:ring-pink-200 focus:border-pink-200 transition"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <label for="password" class="block mb-2 text-sm font-rubik-medium text-white/90">Пароль</label>
                    <input
                        id="password"
                        type="password"
                        class="w-full px-4 py-3 bg-transparent border border-white/30 text-white rounded-md focus:ring-pink-200 focus:border-pink-200 transition"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-white/80">Запомнить меня</span>
                    </label>
                    
                    <Link
                        v-if="canResetPassword"
                        href="/forgot-password"
                        class="text-sm text-white/70 hover:text-white underline"
                    >
                        Забыли пароль?
                    </Link>
                </div>
                
                <div>
                    <button
                        type="submit"
                        class="w-full bg-blue-600/20 text-white border-2 border-white rounded-md py-3 font-rubik-semibold text-lg hover:bg-blue-600/40 transition disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        Войти
                    </button>
                </div>
            </form>

            <div class="text-center">
                <p class="text-white/60">--- или ---</p>
                <Link
                    href="/register"
                    class="block w-full mt-4 py-3 border-2 border-white/50 rounded-md text-white/90 hover:bg-white/10 transition font-rubik-medium"
                >
                    Создать аккаунт
                </Link>
            </div>
        </div>
    </div>
    <AppFooter />

</template>

<!-- Стили для кастомного чекбокса, если нужно -->
<style scoped>
/* Убедитесь, что у вас есть эти цвета в tailwind.config.js или используйте стандартные */
.bg-Wblue { background-color: #011F41; }
.font-norwester { font-family: 'Norwester', sans-serif; /* Подставьте ваш шрифт */ }
.font-rubik-regular { font-family: 'Rubik', sans-serif; font-weight: 400; }
.font-rubik-medium { font-family: 'Rubik', sans-serif; font-weight: 500; }
.font-rubik-semibold { font-family: 'Rubik', sans-serif; font-weight: 600; }

/* Стилизация стандартного чекбокса под темную тему */
input[type="checkbox"] {
    appearance: none;
    -webkit-appearance: none;
    height: 1.25rem;
    width: 1.25rem;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 4px;
    cursor: pointer;
    position: relative;
    background-color: transparent;
}
input[type="checkbox"]:checked {
    background-color: #ec4899; /* pink-500 */
    border-color: #ec4899;
}
input[type="checkbox"]:checked::after {
    content: '✔';
    font-size: 0.9rem;
    color: white;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
</style>
