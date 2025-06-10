<template>
    <section class="flex flex-col justify-center items-center
                    w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8
                    mt-8 sm:mt-12 md:mt-20
                    rounded-xl border-2 border-white/50 bg-Wblue py-8 sm:py-12 min-h-[200px] sm:min-h-[300px]">
        <h2 class="font-norwester mb-2 sm:mb-4 text-2xl sm:text-4xl md:text-5xl text-center text-pink-200">hachiroku is a gaming device store</h2>
        <p class="font-rubik-regular text-sm sm:text-base md:text-lg text-white text-center max-w-4xl">
            HACHIROKU — это не просто интернет-магазин игровых девайсов, а настоящая экосистема для тех, кто живет скоростью, точностью и бескомпромиссной производительностью.
        </p>
    </section>
    
    <section class="flex flex-col gap-6 sm:gap-12
                    w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 sm:mt-20 md:mt-24">
        <h2 class="font-rubik-medium text-3xl sm:text-4xl md:text-5xl text-pink-200">БЕСТСЕЛЛЕРЫ</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link href="/moonlight" class="flex flex-col items-center p-6 sm:pt-12 border-2 bg-[#011F41] hover:bg-blue-600/30 transition rounded-xl border-white/50 h-full">
                <h3 class="font-rubik-semibold mb-4 sm:mb-8 text-xl sm:text-2xl text-white/80 text-center">hachiroku moonlight</h3>
                <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/2e61af8d9e4321e935ce926f19612372d1168221" 
                    alt="hachiroku moonlight product" 
                    class="w-full max-w-[180px] h-auto mb-4 sm:mb-8" />
                <p class="font-rubik-semibold text-xl sm:text-2xl md:text-3xl font-bold rounded-3xl bg-white/80 h-12 sm:h-[50px] text-black/80 w-full max-w-[220px] flex items-center justify-center">
                    9999 рублей
                </p>
            </Link>

            <Link href="/one" class="flex flex-col items-center p-6 sm:pt-12 border-2 bg-[#011F41] hover:bg-blue-600/30 transition rounded-xl border-white/50 h-full">
                <h3 class="font-rubik-semibold mb-4 sm:mb-8 text-xl sm:text-2xl text-white/80 text-center">hachiroku one</h3>
                <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/456b5347b4d72db4f7331eae6443bf6b09c1ccfb" 
                    alt="hachiroku one product" 
                    class="w-full max-w-[180px] h-auto mb-4 sm:mb-8" />
                <p class="font-rubik-semibold text-xl sm:text-2xl md:text-3xl font-bold rounded-3xl bg-white/80 h-12 sm:h-[50px] text-black/80 w-full max-w-[220px] flex items-center justify-center">
                    6990 рублей
                </p>
            </Link>

            <Link href="/mousepad-red" class="flex flex-col items-center p-6 sm:pt-12 border-2 bg-[#011F41] hover:bg-blue-600/30 transition rounded-xl border-white/50 h-full">
                <h3 class="font-rubik-semibold mb-4 sm:mb-5 text-xl sm:text-2xl text-white/80 text-center">hachiroku mousepad</h3>
                <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/adfef0b946752e9845baf27dced3cf0a079a4c7e" 
                    alt="hachiroku mousepad product" 
                    class="w-full max-w-[180px] h-auto mb-4 sm:mb-5" />
                <p class="font-rubik-semibold text-xl sm:text-2xl md:text-3xl font-bold rounded-3xl bg-white/80 h-12 sm:h-[50px] text-black/80 w-full max-w-[220px] flex items-center justify-center">
                    2499 рублей
                </p>
            </Link>
        </div>
    </section>

    <section class="flex flex-col gap-6 sm:gap-12
                    w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8
                    mb-6 sm:mb-10 mt-12 sm:mt-20 md:mt-24">
        <h2 class="font-rubik-medium text-3xl sm:text-4xl md:text-5xl text-pink-200">КАТЕГОРИИ</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="font-rubik-medium text-base sm:text-lg text-white
                        border-2 border-dashed border-white hover:border-solid hover:bg-white/10 transition
                        h-16 sm:h-[84px] flex items-center justify-center rounded-lg">
                МЫШКИ
            </div>
            <div class="font-rubik-medium text-base sm:text-lg text-white
                        border-2 border-dashed border-white hover:border-solid hover:bg-white/10 transition
                        h-16 sm:h-[84px] flex items-center justify-center rounded-lg">
                КЛАВИАТУРЫ
            </div>
            <div class="font-rubik-medium text-base sm:text-lg text-white
                        border-2 border-dashed border-white hover:border-solid hover:bg-white/10 transition
                        h-16 sm:h-[84px] flex items-center justify-center rounded-lg">
                КОВРИКИ
            </div>
            <div class="font-rubik-medium text-base sm:text-lg text-white
                        border-2 border-dashed border-white hover:border-solid hover:bg-white/10 transition
                        h-16 sm:h-[84px] flex items-center justify-center rounded-lg">
                НАУШНИКИ
            </div>
        </div>
    </section>

    <section class="flex flex-col lg:flex-row gap-6 sm:gap-10 md:gap-16
                    w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 sm:mt-20 md:mt-24">
        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/22f887d00c4f1b67d95bda86ba03c0f7dcb860dd" 
            alt="Hachiroku company image" 
            class="w-full h-auto max-w-md mx-auto lg:mx-0 rounded-xl" />
        
        <div class="flex flex-col gap-4 sm:gap-6 lg:w-1/2">
            <p class="font-rubik-light text-base sm:text-lg md:text-xl text-white text-justify">
                Компания Hachiroku была основана в 2010 году группой увлеченных геймеров и инженеров, которые стремились создать инновационные и высококачественные игровые девайсы. Название Hachiroku, что означает "восемь-шесть" на японском, было выбрано в честь знаменитого автомобиля Toyota AE86, символизирующего скорость, точность и страсть к совершенству.
            </p>
            <p class="font-rubik-light text-base sm:text-lg md:text-xl text-white text-justify">
                С первых дней своего существования Hachiroku зарекомендовала себя как лидер в индустрии игровых технологий, постоянно разрабатывая устройства, которые поднимают опыт геймеров на новый уровень. Мы верим, что технологии должны служить игрокам, а не наоборот, поэтому наши продукты всегда ориентированы на удобство, функциональность и стиль.
            </p>
            <p class="font-rubik-light text-base sm:text-lg md:text-xl text-white text-justify">
                Наши достижения включают множество инновационных разработок, таких как адаптивные контроллеры, ультраточные мыши и наушники с иммерсивным звуком, которые получили признание не только среди профессионалов, но и обычных игроков, ценящих качество и надежность.
            </p>
        </div>
    </section>
</template>

<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<script>
export default {
    name: 'AppMain'
}
</script>