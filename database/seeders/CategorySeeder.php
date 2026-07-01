<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $taxes = Tax::query()->where('is_active', true)->get()->keyBy('name');

        $categories = [
            ['name' => 'Phone Repairs', 'tax' => 'Service Tax 6%', 'discount' => null],
            ['name' => 'Laptop Repairs', 'tax' => 'Service Tax 6%', 'discount' => null],
            ['name' => 'Tablet Repairs', 'tax' => 'Service Tax 6%', 'discount' => null],
            ['name' => 'PC Services', 'tax' => 'Service Tax 6%', 'discount' => null],
            ['name' => 'Upgrades & Parts', 'tax' => 'Sales Tax 8.25%', 'discount' => null],
            ['name' => 'Accessories & Peripherals', 'tax' => 'Sales Tax 8.25%', 'discount' => null],
            ['name' => 'Networking & Setup', 'tax' => 'Service Tax 6%', 'discount' => null],
            ['name' => 'Data Services', 'tax' => 'Service Tax 6%', 'discount' => null],
            ['name' => 'Software & Security', 'tax' => 'Sales Tax 8.25%', 'discount' => null],
            ['name' => 'Protection Plans', 'tax' => 'City Repair Tax 2%', 'discount' => ['amount' => 5, 'percentage' => true]],
        ];

        Model::withoutEvents(function () use ($categories, $taxes) {
            foreach ($categories as $category) {
                $taxId = null;
                if ($category['tax'] !== null && $taxes->has($category['tax'])) {
                    $taxId = $taxes[$category['tax']]->id;
                }

                $discount = $category['discount'];

                Category::updateOrCreate(
                    ['name' => $category['name']],
                    [
                        'tax_id' => $taxId,
                        'is_active' => true,
                        'has_discount' => (bool) $discount,
                        'discount_amount' => $discount['amount'] ?? null,
                        'discount_is_percentage' => $discount['percentage'] ?? false,
                    ]
                );
            }
        });
    }
}
