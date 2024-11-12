<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ProductFactoryクラスで定義した内容にもとづいてダミーデータを20件生成し、productsテーブルに追加する
        Product::factory()->count(20)->create();
    }
}
