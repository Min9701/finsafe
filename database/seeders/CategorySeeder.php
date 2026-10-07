<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Thu nhập
            ['name' => 'Lương', 'type' => 'income', 'is_default' => true],
            ['name' => 'Thưởng', 'type' => 'income', 'is_default' => true],
            ['name' => 'Đầu tư', 'type' => 'income', 'is_default' => true],
            ['name' => 'Thu nhập khác', 'type' => 'income', 'is_default' => true],

            // Chi tiêu
            ['name' => 'Ăn uống', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Di chuyển', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Mua sắm', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Giải trí', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Hóa đơn & Tiện ích', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Sức khỏe', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Giáo dục', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Chi tiêu khác', 'type' => 'expense', 'is_default' => true],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name'], 'is_default' => true],
                $category
            );
        }
    }
}
