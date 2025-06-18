<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        $mouseCategory = Category::where('slug', 'mice')->first();

        if ($mouseCategory) {
            
            $product1 = Product::create([
                'category_id' => $mouseCategory->id,
                'name' => 'Hachiroku superone',
                'slug' => 'superone', // Сделайте slug уникальным для продукта
                'description' => 'Время работы от одного заряда - 130 часов (объём аккумулятора 500 mAh). Чип Nordic 52840 с поддержкой частоты опроса до 4000 Гц 
                (требует специального ресивера, который можно приобрести отдельно)',
                'price' => 7499,
                'feature' => 'Тип подключения: Беспроводная
                Формат: 98%
                Размеры: 392x141x44мм
                Вес: 1010г
                Наличие Hot-Swap: да',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $product1->images()->createMany([
                ['path' => '/images/superonemain1.png', 'is_main' => true],
                ['path' => '/images/superone1.png', 'is_main' => false],
                ['path' => '/images/superone12.png', 'is_main' => false],
                ['path' => '/images/superone13.png', 'is_main' => false],
            ]);
            $product2 = Product::create([
                'category_id' => $mouseCategory->id,
                'name' => 'Hachiroku one',
                'slug' => 'one', // Сделайте slug уникальным для продукта
                'description' => 'Топовая игровая мышь, которая адаптируется под тебя. поддерживает hot-swap и позволяет с легкостью менять микро-переключатели, подбирая самый комфортной клик. Больше не нужно идти на компромиссы.',
                'price' => 6990,
                'feature' => 'Тип подключения: Беспроводная
                Формат: 98%
                Размеры: 392x141x44мм
                Вес: 110г
                Наличие Hot-Swap: да',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $product2->images()->createMany([
                ['path' => '/images/mousemain1.png', 'is_main' => true],
                ['path' => '/images/mouse1.png', 'is_main' => false],
                ['path' => '/images/mouse12.png', 'is_main' => false],
                ['path' => '/images/mouse13.png', 'is_main' => false],
            ]);
        } else {
            $this->command->warn('Категория "mice" не найдена. Убедитесь, что CategorySeeder запущен.');
        }       
         
        // Получаем категорию "Клавиатуры" по slug
        $keyboardCategory = Category::where('slug', 'keyboards')->first();

        if ($keyboardCategory) {
            $product3 = Product::create([
                'category_id' => $keyboardCategory->id,
                'name' => 'Hachiroku Space',
                'slug' => 'space', // Сделайте slug уникальным для продукта
                'description' => 'Флагманская клавиатура премиального уровня, в которой продумана каждая мелкая деталь, чтобы игровые сессии даже самых требовательных пользователей проходили с максимальным комфортом',
                'feature' => 'Тип подключения: Беспроводная
                Формат: 98%
                Размеры: 392x141x44мм
                Вес: 1010г
                Наличие Hot-Swap: да',
                'price' => 8999,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $product3->images()->createMany([
                ['path' => '/images/spacemain.png', 'is_main' => true],
                ['path' => '/images/space1.png', 'is_main' => false],
                ['path' => '/images/space2.png', 'is_main' => false],
                ['path' => '/images/space3.png', 'is_main' => false],
            ]);
            $product4 = Product::create([
                'category_id' => $keyboardCategory->id,
                'name' => 'hachiroku moonlight',
                'slug' => 'moonlight', // Сделайте slug уникальным для продукта
                'description' => 'Проводное подключение и уравновешенный комплект поставки позволили добиться комфортной цены на девайс, сохранив все важнейшие преимущества премиального устройства - невероятно приятный тайпинг, исключительную функциональность и абсолютную надежность.',
                'price' => 9999,
                'feature' => 'Тип подключения: Беспроводная
                Формат: 98%
                Размеры: 392x141x44мм
                Вес: 1010г
                Наличие Hot-Swap: да',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $product4->images()->createMany([
                ['path' => '/images/moonlightmain.png', 'is_main' => true],
                ['path' => '/images/moonlightmain1.png', 'is_main' => false],
                ['path' => '/images/moonlightmain12.png', 'is_main' => false],
                ['path' => '/images/moonlightmain13.png', 'is_main' => false],
            ]);
        } else {
            $this->command->warn('Категория "keyboards" не найдена. Убедитесь, что CategorySeeder запущен.');
        }      
        

        $headphoneCategory = Category::where('slug', 'headphones')->first();

        if ($headphoneCategory) {
            $product5 = Product::create([
                'category_id' => $headphoneCategory->id,
                'name' => 'Hachiroku night',
                'slug' => 'night', // Сделайте slug уникальным для продукта
                'description' => 'Складной микрофон с увеличенным звукоснимающим капсюлем и расширенным частотным диапазоном. Проводное подключение гарантирует полную совместимость микрофона с любыми устройствами',
                'price' => 7499,
                'feature' => 'Тип подключения: Беспроводная
                Формат: 98%
                Размеры: 392x141x44мм
                Вес: 1010г
                Наличие Hot-Swap: да',                
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $product5->images()->createMany([
                ['path' => '/images/nightmain1.png', 'is_main' => true],
                ['path' => '/images/night1.png', 'is_main' => false],
                ['path' => '/images/night12.png', 'is_main' => false],
                ['path' => '/images/night13.png', 'is_main' => false],
            ]);
            $product6 = Product::create([
                'category_id' => $headphoneCategory->id,
                'name' => 'Hachiroku loud',
                'slug' => 'loud', // Сделайте slug уникальным для продукта
                'description' => 'Беспроводная гарнитура премиум-класса с высокой точностью звука в средних и высоких частотах, Съемный микрофон с увеличенным звукоснимающим капсюлем и расширенным частотным диапазоном.',
                'price' => 8999,
                'feature' => 'Тип подключения: Беспроводная
                Формат: 98%
                Размеры: 392x141x44мм
                Вес: 1010г
                Наличие Hot-Swap: да',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $product6->images()->createMany([
                ['path' => '/images/loudmain.png', 'is_main' => true],
                ['path' => '/images/loud1.png', 'is_main' => false],
                ['path' => '/images/loud12.png', 'is_main' => false],
                ['path' => '/images/loud13.png', 'is_main' => false],
            ]);
        } else {
            $this->command->warn('Категория "keyboards" не найдена. Убедитесь, что CategorySeeder запущен.');
        }      

        $carpetCategory = Category::where('slug', 'carpets')->first();

        if ($headphoneCategory) {
            $product7 = Product::create([
                'category_id' => $carpetCategory->id,
                'name' => 'Hachiroku mousepad-red',
                'slug' => 'mousepad-red', // Сделайте slug уникальным для продукта
                'description' => 'Тканевый коврик outlines выполнен из высококачественного полиэстера толщиной 4 мм. Плотное плетение нитей с мелкой фактурой гарантирует оптимальное сочетание скорости и контроля. Коврик уверенно фиксируется на столе благодаря цепкому прорезиненному основанию.',
                'price' => 2499,
                'feature' => 'Тип подключения: Беспроводная
                Формат: 98%
                Размеры: 392x141x44мм
                Вес: 1010г
                Наличие Hot-Swap: да',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $product7->images()->createMany([
                ['path' => '/images/mousepadredmain1.png', 'is_main' => true],
                ['path' => '/images/mousepadred1.png', 'is_main' => false],
                ['path' => '/images/mousepadred12.png', 'is_main' => false],
                ['path' => '/images/mousepadred13.png', 'is_main' => false],
            ]);
            $product8 = Product::create([
                 'category_id' => $carpetCategory->id,
                'name' => 'Hachiroku mousepad-blue',
                'slug' => 'mousepad-blue', // Сделайте slug уникальным для продукта
                'description' => 'Тканевый коврик outlines выполнен из высококачественного полиэстера толщиной 4 мм. Плотное плетение нитей с мелкой фактурой гарантирует оптимальное сочетание скорости и контроля. Коврик уверенно фиксируется на столе благодаря цепкому прорезиненному основанию.',
                'price' => 2499,
                'feature' => 'Тип подключения: Беспроводная
                Формат: 98%
                Размеры: 392x141x44мм
                Вес: 1010г
                Наличие Hot-Swap: да',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $product8->images()->createMany([
                ['path' => '/images/mousepad-bluemain.png', 'is_main' => true],
                ['path' => '/images/mouseblue1.png', 'is_main' => false],
                ['path' => '/images/mouseblue12.png', 'is_main' => false],
                ['path' => '/images/mouseblue13.png', 'is_main' => false],
            ]);
        } else {
            $this->command->warn('Категория "keyboards" не найдена. Убедитесь, что CategorySeeder запущен.');
        }     
     
    }
        
}
