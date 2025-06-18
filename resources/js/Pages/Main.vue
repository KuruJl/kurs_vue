<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppHeader from './Header.vue';
import AppFooter from './Footer.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    bestSellers: {
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

console.log('Best Sellers received in Main.vue:', props.bestSellers);
</script>

<template>
    <AppHeader />
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
            <template v-if="bestSellers.length > 0">
                <Link v-for="product in bestSellers" :key="product.id" :href="`/products/${product.slug || product.id}`"
                      class="flex flex-col items-center p-6 sm:p-8 border-2 bg-[#011F41] hover:bg-blue-600/30 transition rounded-xl border-white/50 h-full justify-between">
                    <div class="w-full h-48 sm:h-56 md:h-64 flex items-center justify-center mb-4 sm:mb-6 overflow-hidden">
                        <img :src="product.image_url"
                             :alt="product.name"
                             class="max-w-full max-h-full object-contain" />
                    </div>
                    <h3 class="font-rubik-semibold mb-4 sm:mb-6 text-xl sm:text-2xl text-white/80 text-center flex-grow">{{ product.name }}</h3>
                    <p class="font-rubik-semibold text-xl sm:text-2xl md:text-3xl font-bold rounded-3xl bg-white/80 h-12 sm:h-[50px] text-black/80 w-full 
                    max-w-[220px] flex items-center justify-center">
                        {{ formatPrice(product.price) }} ₽
                    </p>
                </Link>
            </template>
            <template v-else>
                <p class="font-rubik-light text-lg text-white/80 col-span-full text-center">Нет доступных бестселлеров.</p>
            </template>
        </div>
    </section>

    <section class="flex flex-col gap-6 sm:gap-12
                    w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8
                    mb-6 sm:mb-10 mt-12 sm:mt-20 md:mt-24">
        <h2 class="font-rubik-medium text-3xl sm:text-4xl md:text-5xl text-pink-200">КАТЕГОРИИ</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            <Link href="/catalog?category[]=2" class="font-rubik-medium text-base sm:text-lg text-white
                        border-2 border-dashed border-white hover:border-solid hover:bg-white/10 transition
                        h-16 sm:h-[84px] flex items-center justify-center rounded-lg">
                МЫШКИ
            </Link>
            <Link href="/catalog?category[]=1" class="font-rubik-medium text-base sm:text-lg text-white
                        border-2 border-dashed border-white hover:border-solid hover:bg-white/10 transition
                        h-16 sm:h-[84px] flex items-center justify-center rounded-lg">
                КЛАВИАТУРЫ
            </Link>
            <Link href="/catalog?category[]=4" class="font-rubik-medium text-base sm:text-lg text-white
                        border-2 border-dashed border-white hover:border-solid hover:bg-white/10 transition
                        h-16 sm:h-[84px] flex items-center justify-center rounded-lg">
                КОВРИКИ
            </Link>
            <Link href="/catalog?category[]=3" class="font-rubik-medium text-base sm:text-lg text-white
                        border-2 border-dashed border-white hover:border-solid hover:bg-white/10 transition
                        h-16 sm:h-[84px] flex items-center justify-center rounded-lg">
                НАУШНИКИ
            </Link>
        </div>
    </section>

    <section class="flex flex-col lg:flex-row gap-6 sm:gap-10 md:gap-16
                    w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 sm:mt-20 md:mt-24">
        <img src="/images/old_key.png"
             alt="Hachiroku old keyboard"
             class="w-full h-auto max-w-md mx-auto lg:mx-0 rounded-xl object-contain max-h-[400px] sm:max-h-[500px]" /> 
        
        <div class="flex flex-col gap-4 sm:gap-6 lg:w-1/2">
            <p class="font-rubik-light text-base sm:text-lg md:text-xl text-white text-justify">
                Компания Hachiroku была основана в 2010 году группой увлеченных геймеров и инженеров, которые стремились создать 
                инновационные и высококачественные игровые девайсы. Название Hachiroku, что означает "восемь-шесть" на японском, 
                было выбрано в честь знаменитого автомобиля Toyota AE86, символизирующего скорость, точность и страсть к совершенству.
            </p>
            <p class="font-rubik-light text-base sm:text-lg md:text-xl text-white text-justify">
                С первых дней своего существования Hachiroku зарекомендовала себя как лидер в индустрии игровых технологий, 
                постоянно разрабатывая устройства, которые поднимают опыт геймеров на новый уровень. Мы верим, что технологии должны служить игрокам, 
                а не наоборот, поэтому наши продукты всегда ориентированы на удобство, функциональность и стиль.
            </p>
            <p class="font-rubik-light text-base sm:text-lg md:text-xl text-white text-justify">
                Наши достижения включают множество инновационных разработок, таких как адаптивные контроллеры, ультраточные мыши и наушники с иммерсивным звуком, 
                которые получили признание не только среди профессионалов, но и обычных игроков, ценящих качество и надежность.
            </p>
        </div>
    </section>
    <AppFooter />
</template>

<script>
export default {
    name: 'AppMain'
}
</script>