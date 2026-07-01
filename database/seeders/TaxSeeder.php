<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $taxes = [
            ['name' => 'Sales Tax 8.25%', 'rate' => 8.25, 'type' => 'percentage', 'is_active' => true],
            ['name' => 'Service Tax 6%', 'rate' => 6.00, 'type' => 'percentage', 'is_active' => true],
            ['name' => 'City Repair Tax 2%', 'rate' => 2.00, 'type' => 'percentage', 'is_active' => true],
            ['name' => 'E-Waste Recycling Fee', 'rate' => 3.50, 'type' => 'fixed', 'is_active' => true],
            ['name' => 'Processing Fee', 'rate' => 2.00, 'type' => 'fixed', 'is_active' => true],
        ];

        Model::withoutEvents(function () use ($taxes) {
            foreach ($taxes as $tax) {
                Tax::updateOrCreate(
                    ['name' => $tax['name']],
                    $tax
                );
            }
        });
    }
}
