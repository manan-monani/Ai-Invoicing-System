<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Cash',
                'is_default' => true,
                'is_active' => true,
            ],
        ];

        Model::withoutEvents(function () use ($methods) {
            foreach ($methods as $method) {
                PaymentMethod::updateOrCreate(
                    ['name' => $method['name']],
                    $method
                );
            }
        });
    }
}
