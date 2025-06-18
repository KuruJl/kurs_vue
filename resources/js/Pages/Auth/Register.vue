<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppHeader from '../Header.vue';
import AppFooter from '../Footer.vue';
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AppHeader />

    <Head title="Регистрация" />

    <!-- Обертка для стилизации фона и центрирования -->
    <div class="min-h-screen bg-Wblue flex flex-col items-center justify-center p-4 font-rubik-regular">

        <!-- Логотип (опционально, можно добавить) -->
        <div class="mb-8">
            <Link href="/">
                <h1 class="font-norwester text-4xl text-pink-200">hachiroku</h1>
            </Link>
        </div>

        <!-- Контейнер формы -->
        <div class="w-full max-w-md p-8 space-y-6 rounded-xl border-2 border-white/30 bg-[#001A33]/50">
             <h2 class="text-3xl font-rubik-semibold text-center text-white">
                Создание аккаунта
            </h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="name" class="block mb-2 text-sm font-rubik-medium text-white/90">Имя</label>
                    <input
                        id="name"
                        type="text"
                        class="w-full px-4 py-3 bg-transparent border border-white/30 text-white rounded-md focus:ring-pink-200 focus:border-pink-200 transition"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <label for="email" class="block mb-2 text-sm font-rubik-medium text-white/90">Email</label>
                    <input
                        id="email"
                        type="email"
                        class="w-full px-4 py-3 bg-transparent border border-white/30 text-white rounded-md focus:ring-pink-200 focus:border-pink-200 transition"
                        v-model="form.email"
                        required
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
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div>
                    <label for="password_confirmation" class="block mb-2 text-sm font-rubik-medium text-white/90">Подтверждение пароля</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        class="w-full px-4 py-3 bg-transparent border border-white/30 text-white rounded-md focus:ring-pink-200 focus:border-pink-200 transition"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>
                
                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full bg-blue-600/20 text-white border-2 border-white rounded-md py-3 font-rubik-semibold text-lg hover:bg-blue-600/40 transition disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        Зарегистрироваться
                    </button>
                </div>
            </form>

            <div class="text-center mt-6">
                <Link
                    href="/login"
                    class="text-sm text-white/70 hover:text-white underline"
                >
                    Уже есть аккаунт? Войти
                </Link>
            </div>
        </div>
    </div>
    <AppFooter />

</template>

<style scoped>
/* Эти стили можно вынести в глобальный CSS, если они используются на многих страницах */
.bg-Wblue { background-color: #011F41; }
.font-norwester { font-family: 'Norwester', sans-serif; }
.font-rubik-regular { font-family: 'Rubik', sans-serif; font-weight: 400; }
.font-rubik-medium { font-family: 'Rubik', sans-serif; font-weight: 500; }
.font-rubik-semibold { font-family: 'Rubik', sans-serif; font-weight: 600; }
</style>