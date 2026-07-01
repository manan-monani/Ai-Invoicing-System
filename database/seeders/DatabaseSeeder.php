<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            TaxSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            ItemSeeder::class,
            PaymentMethodSeeder::class,
            BusinessSettingSeeder::class,
            UserSeeder::class,
            InvoiceSeeder::class,
            NotificationSeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
