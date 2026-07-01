<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        Model::withoutEvents(function () {
            Brand::updateOrCreate(
                ['name' => 'ByteFix Tech Shop'],
                [
                    'logo' => 'https://api.dicebear.com/7.x/initials/svg?seed=BF&backgroundColor=4f7fa6',
                    'settings' => [
                        'primary_color' => '#4f7fa6',
                        'secondary_color' => '#6a747d',
                        'font_primary' => 'Instrument Sans',
                    ],
                ]
            );

            Brand::updateOrCreate(
                ['name' => 'CircuitCare Electronics'],
                [
                    'logo' => 'https://api.dicebear.com/7.x/initials/svg?seed=CC&backgroundColor=4f7fa6',
                    'settings' => [
                        'primary_color' => '#4f7fa6',
                        'secondary_color' => '#6a747d',
                        'font_primary' => 'Instrument Sans',
                    ],
                ]
            );
        });
    }
}
