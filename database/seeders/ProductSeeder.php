<?php
namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['title' => 'سیگنال‌های VIP معاملات', 'description' => 'شامل تایم دقیق ورود، تارگت‌ها و مدیریت ریسک.', 'is_active' => true],
            ['title' => 'ربات‌های هوشمند ترید', 'description' => 'ابزارهای خودکار معاملاتی مبتنی بر استراتژی‌های تست‌شده.', 'is_active' => true],
            ['title' => 'دوره‌ها و پکیج‌های آموزشی معامله‌گری', 'description' => 'آموزش استراتژی‌های معاملاتی و پرایس اکشن.', 'is_active' => true],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['title' => $product['title']],
                $product
            );
        }
    }
}
