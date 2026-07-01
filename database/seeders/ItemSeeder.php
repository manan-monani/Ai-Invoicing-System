<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();
        $catalog = [
            'Phone Repairs' => [
                [
                    'name' => 'iPhone Screen Replacement',
                    'description' => 'OEM-quality screen replacement with full touch calibration.',
                    'unit' => 'pcs',
                    'price' => [129, 249],
                ],
                [
                    'name' => 'Android Screen Replacement',
                    'description' => 'Screen replacement for Samsung, Google, and other Android devices.',
                    'unit' => 'pcs',
                    'price' => [119, 229],
                ],
                [
                    'name' => 'Phone Battery Replacement',
                    'description' => 'Battery swap with health check and post-repair diagnostics.',
                    'unit' => 'pcs',
                    'price' => [79, 149],
                ],
                [
                    'name' => 'Charging Port Repair',
                    'description' => 'Port cleaning or replacement to restore reliable charging.',
                    'unit' => 'pcs',
                    'price' => [89, 159],
                ],
                [
                    'name' => 'Camera Module Replacement',
                    'description' => 'Rear or front camera replacement with focus calibration.',
                    'unit' => 'pcs',
                    'price' => [89, 169],
                ],
                [
                    'name' => 'Water Damage Cleanup',
                    'description' => 'Board cleaning and corrosion treatment for liquid exposure.',
                    'unit' => 'pcs',
                    'price' => [99, 199],
                ],
            ],
            'Laptop Repairs' => [
                [
                    'name' => 'Laptop Screen Replacement',
                    'description' => 'Panel replacement with hinge inspection and display testing.',
                    'unit' => 'pcs',
                    'price' => [149, 279],
                ],
                [
                    'name' => 'Laptop Battery Replacement',
                    'description' => 'Battery replacement with charging cycle verification.',
                    'unit' => 'pcs',
                    'price' => [109, 189],
                ],
                [
                    'name' => 'Keyboard Replacement',
                    'description' => 'Keyboard swap with full key and backlight testing.',
                    'unit' => 'pcs',
                    'price' => [99, 169],
                ],
                [
                    'name' => 'Hinge Repair',
                    'description' => 'Reinforced hinge repair to restore smooth opening.',
                    'unit' => 'pcs',
                    'price' => [89, 159],
                ],
                [
                    'name' => 'Fan and Cooling Service',
                    'description' => 'Fan cleaning, thermal paste refresh, and temperature testing.',
                    'unit' => 'pcs',
                    'price' => [79, 139],
                ],
                [
                    'name' => 'Liquid Spill Cleanup',
                    'description' => 'Internal cleanup and corrosion mitigation after spills.',
                    'unit' => 'pcs',
                    'price' => [119, 219],
                ],
            ],
            'Tablet Repairs' => [
                [
                    'name' => 'Tablet Screen Replacement',
                    'description' => 'Digitizer and glass replacement with touch testing.',
                    'unit' => 'pcs',
                    'price' => [139, 249],
                ],
                [
                    'name' => 'Tablet Battery Replacement',
                    'description' => 'Battery swap with charge cycle verification.',
                    'unit' => 'pcs',
                    'price' => [99, 169],
                ],
                [
                    'name' => 'Charging Port Repair (Tablet)',
                    'description' => 'Charging port repair or replacement for reliable power.',
                    'unit' => 'pcs',
                    'price' => [89, 149],
                ],
                [
                    'name' => 'Button Repair',
                    'description' => 'Power or volume button replacement and testing.',
                    'unit' => 'pcs',
                    'price' => [69, 129],
                ],
                [
                    'name' => 'Speaker Repair',
                    'description' => 'Speaker replacement with audio calibration.',
                    'unit' => 'pcs',
                    'price' => [79, 139],
                ],
            ],
            'PC Services' => [
                [
                    'name' => 'PC Diagnostic',
                    'description' => 'Hardware and software diagnostics with repair estimate.',
                    'unit' => 'hour',
                    'price' => [50, 90],
                ],
                [
                    'name' => 'Virus and Malware Removal',
                    'description' => 'Deep clean, threat removal, and security hardening.',
                    'unit' => 'hour',
                    'price' => [70, 140],
                ],
                [
                    'name' => 'OS Installation and Setup',
                    'description' => 'Fresh OS install with drivers, updates, and setup.',
                    'unit' => 'hour',
                    'price' => [80, 150],
                ],
                [
                    'name' => 'Performance Tune-Up',
                    'description' => 'Startup optimization, cleanup, and speed improvements.',
                    'unit' => 'hour',
                    'price' => [60, 120],
                ],
                [
                    'name' => 'System Cleanup and Thermal Paste',
                    'description' => 'Full dust cleaning and thermal paste replacement.',
                    'unit' => 'hour',
                    'price' => [70, 130],
                ],
                [
                    'name' => 'Driver and Software Troubleshooting',
                    'description' => 'Resolve crashes, driver conflicts, and app errors.',
                    'unit' => 'hour',
                    'price' => [50, 100],
                ],
            ],
            'Upgrades & Parts' => [
                [
                    'name' => 'SSD Upgrade 1TB',
                    'description' => '1TB SSD with installation and cloning support.',
                    'unit' => 'pcs',
                    'price' => [120, 220],
                ],
                [
                    'name' => 'RAM Upgrade 16GB',
                    'description' => '16GB RAM kit with installation and testing.',
                    'unit' => 'pcs',
                    'price' => [80, 160],
                ],
                [
                    'name' => 'Graphics Card Upgrade (Mid-range)',
                    'description' => 'Mid-range GPU with installation and stress testing.',
                    'unit' => 'pcs',
                    'price' => [250, 550],
                ],
                [
                    'name' => 'Power Supply Replacement 650W',
                    'description' => '650W PSU replacement with cable management.',
                    'unit' => 'pcs',
                    'price' => [90, 160],
                ],
                [
                    'name' => 'Wi-Fi 6 Card Upgrade',
                    'description' => 'Wi-Fi 6 card with driver setup and testing.',
                    'unit' => 'pcs',
                    'price' => [45, 95],
                ],
                [
                    'name' => 'Laptop Storage Upgrade 512GB',
                    'description' => '512GB SSD upgrade with OS migration.',
                    'unit' => 'pcs',
                    'price' => [90, 170],
                ],
            ],
            'Accessories & Peripherals' => [
                [
                    'name' => 'Wireless Mouse',
                    'description' => 'Ergonomic wireless mouse with adjustable DPI.',
                    'unit' => 'pcs',
                    'price' => [20, 45],
                ],
                [
                    'name' => 'Mechanical Keyboard',
                    'description' => 'Mechanical keyboard with backlight and tactile switches.',
                    'unit' => 'pcs',
                    'price' => [60, 140],
                ],
                [
                    'name' => 'USB-C Charger 65W',
                    'description' => 'Fast charger compatible with laptops and phones.',
                    'unit' => 'pcs',
                    'price' => [25, 55],
                ],
                [
                    'name' => 'USB-C Cable 2m',
                    'description' => 'Durable braided USB-C cable for charging and data.',
                    'unit' => 'pcs',
                    'price' => [10, 20],
                ],
                [
                    'name' => 'HDMI Cable 2m',
                    'description' => 'High-speed HDMI cable for 4K displays.',
                    'unit' => 'pcs',
                    'price' => [10, 20],
                ],
                [
                    'name' => 'Laptop Sleeve 15-inch',
                    'description' => 'Padded sleeve with water-resistant finish.',
                    'unit' => 'pcs',
                    'price' => [18, 35],
                ],
                [
                    'name' => 'External Hard Drive 1TB',
                    'description' => 'Portable drive with USB 3.0 connectivity.',
                    'unit' => 'pcs',
                    'price' => [55, 110],
                ],
                [
                    'name' => 'Bluetooth Headset',
                    'description' => 'Noise-isolating headset with built-in mic.',
                    'unit' => 'pcs',
                    'price' => [35, 90],
                ],
            ],
            'Networking & Setup' => [
                [
                    'name' => 'Wi-Fi Router Setup',
                    'description' => 'Router setup with secure password configuration.',
                    'unit' => 'hour',
                    'price' => [70, 130],
                ],
                [
                    'name' => 'Mesh Wi-Fi Installation',
                    'description' => 'Mesh system install with coverage optimization.',
                    'unit' => 'hour',
                    'price' => [120, 260],
                ],
                [
                    'name' => 'Network Troubleshooting',
                    'description' => 'Diagnose slow speeds, dropouts, and interference.',
                    'unit' => 'hour',
                    'price' => [60, 120],
                ],
                [
                    'name' => 'Printer Setup and Configuration',
                    'description' => 'Printer setup, drivers, and wireless printing.',
                    'unit' => 'hour',
                    'price' => [50, 100],
                ],
                [
                    'name' => 'Smart Home Device Setup',
                    'description' => 'Connect and configure smart home devices.',
                    'unit' => 'hour',
                    'price' => [60, 120],
                ],
            ],
            'Data Services' => [
                [
                    'name' => 'Data Backup Setup',
                    'description' => 'Local or cloud backup configuration and verification.',
                    'unit' => 'hour',
                    'price' => [80, 150],
                ],
                [
                    'name' => 'Data Recovery (Basic)',
                    'description' => 'Basic recovery for deleted files or corrupted media.',
                    'unit' => 'hour',
                    'price' => [120, 300],
                ],
                [
                    'name' => 'Data Transfer to New Device',
                    'description' => 'Move files and settings to a new laptop or phone.',
                    'unit' => 'hour',
                    'price' => [60, 120],
                ],
                [
                    'name' => 'Cloud Sync Setup',
                    'description' => 'Configure cloud storage and sync policies.',
                    'unit' => 'hour',
                    'price' => [70, 130],
                ],
                [
                    'name' => 'Email Migration',
                    'description' => 'Move email data to a new provider or device.',
                    'unit' => 'hour',
                    'price' => [90, 160],
                ],
            ],
            'Software & Security' => [
                [
                    'name' => 'Antivirus Installation',
                    'description' => 'Install and configure antivirus with scheduled scans.',
                    'unit' => 'pcs',
                    'price' => [30, 70],
                ],
                [
                    'name' => 'OS Upgrade (Windows or macOS)',
                    'description' => 'Upgrade OS with compatibility checks and backups.',
                    'unit' => 'pcs',
                    'price' => [80, 160],
                ],
                [
                    'name' => 'Office Suite Installation',
                    'description' => 'Install office apps and configure licensing.',
                    'unit' => 'pcs',
                    'price' => [40, 90],
                ],
                [
                    'name' => 'Parental Controls Setup',
                    'description' => 'Configure screen time, filters, and device limits.',
                    'unit' => 'pcs',
                    'price' => [40, 80],
                ],
                [
                    'name' => 'Password Manager Setup',
                    'description' => 'Install and configure password manager with vault setup.',
                    'unit' => 'pcs',
                    'price' => [35, 75],
                ],
            ],
            'Protection Plans' => [
                [
                    'name' => '30-Day Repair Warranty',
                    'description' => 'Workmanship coverage for repair services.',
                    'unit' => 'pcs',
                    'price' => [15, 35],
                ],
                [
                    'name' => '1-Year Extended Warranty',
                    'description' => 'Extended coverage for parts and labor.',
                    'unit' => 'pcs',
                    'price' => [79, 149],
                ],
                [
                    'name' => 'Accidental Damage Protection',
                    'description' => 'Coverage for drops and spills within policy terms.',
                    'unit' => 'pcs',
                    'price' => [99, 199],
                ],
                [
                    'name' => 'Priority Support Plan',
                    'description' => 'Front-of-line support and expedited service.',
                    'unit' => 'pcs',
                    'price' => [49, 99],
                ],
            ],
        ];

        $categories = Category::query()->get()->keyBy('name');

        Model::withoutEvents(function () use ($catalog, $categories, $faker) {
            foreach ($catalog as $categoryName => $items) {
                $category = $categories->get($categoryName);
                if (! $category) {
                    continue;
                }

                foreach ($items as $itemData) {
                    $unitPrice = $faker->randomFloat(2, $itemData['price'][0], $itemData['price'][1]);
                    $costPrice = round($unitPrice * $faker->randomFloat(2, 0.45, 0.75), 2);
                    $hasDiscount = $faker->boolean(12);
                    $discountIsPercentage = $hasDiscount ? $faker->boolean(70) : false;
                    $skuPrefix = strtoupper(Str::of($categoryName)->replaceMatches('/[^A-Za-z]/', '')->limit(3, '')->toString());

                    Item::updateOrCreate(
                        ['name' => $itemData['name'], 'category_id' => $category->id],
                        [
                            'sku' => $skuPrefix.'-'.strtoupper($faker->bothify('####')),
                            'description' => $itemData['description'],
                            'unit' => $itemData['unit'],
                            'tax_id' => $category->tax_id,
                            'unit_price' => $unitPrice,
                            'cost_price' => min($costPrice, $unitPrice),
                            'has_discount' => $hasDiscount,
                            'discount_amount' => $hasDiscount
                                ? ($discountIsPercentage ? $faker->numberBetween(5, 20) : $faker->randomFloat(2, 5, 60))
                                : null,
                            'discount_is_percentage' => $hasDiscount ? $discountIsPercentage : false,
                            'is_active' => $faker->boolean(94),
                        ]
                    );
                }
            }
        });
    }
}
