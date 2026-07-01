<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'business_name' => 'ByteFix Tech Shop',
            'tagline' => 'Repairs, upgrades, and gear for every device.',
            'logo_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=BF&backgroundColor=4f7fa6',
            'favicon_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=B&backgroundColor=4f7fa6',
            'cover_url' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&q=80&w=2000',
            'contact_email' => 'hello@bytefixtech.com',
            'contact_phone' => '+1 (512) 555-0173',
            'contact_address' => '2400 Tech Ridge Blvd, Austin, TX 78753',
            'currency_symbol' => '$',
            'currency_position' => 'left',
            'language' => 'en',
            BusinessSetting::INVOICE_PREFIX => 'TS-',
            BusinessSetting::INVOICE_TERMS => 'Payment due on receipt. Repairs include a 30-day workmanship warranty. Parts warranty varies by manufacturer.',
            BusinessSetting::TAX_ENABLED => '0',
            BusinessSetting::TAX_MODE => 'none',
            BusinessSetting::DEFAULT_TAX_ID => '',
            'primary' => '#4f7fa6',
            'primary_light' => '#e9f1f7',
            'primary_dark_text' => '#b9d3e6',
            'secondary' => '#6a747d',
            'success' => '#4a9c88',
            'danger' => '#c96b6b',
            'warning' => '#c9a35a',
            'info' => '#4f85a6',
            'toast_success' => '#4a9c88',
            'toast_error' => '#c96b6b',
            'toast_warning' => '#c9a35a',
            'sidebar_rail_bg' => '#2f4f66',
            'sidebar_rail_bg_dark' => '#213746',
            'sidebar_icon_color' => '#ffffffff',
            'sidebar_icon_color_dark' => '#ffffffff',
        ];

        Model::withoutEvents(function () use ($settings) {
            foreach ($settings as $key => $value) {
                BusinessSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value ?? '']
                );
            }
        });
    }
}
