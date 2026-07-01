<?php

namespace App\Constants;

class Permissions
{
    // Section: System
    public const SECTION_SYSTEM = 'system_section';

    public const DASHBOARD_VIEW = 'dashboard_view';

    public const ACTIVITY_LOG = 'activity_log';

    public const TAX_SETUP = 'tax_setup';

    public const PAYMENT_METHODS = 'payment_methods';

    // Section: Account
    public const SECTION_ACCOUNT = 'account_section';

    public const EMPLOYEE_DIRECTORY = 'employee_directory';

    public const CUSTOMER_DIRECTORY = 'customer_directory';

    public const ACCESS_ROLES = 'access_roles';

    public const BUSINESS_BRANDING = 'business_branding';

    public const BUSINESS_LOGIC = 'business_logic';

    // Section: Catalog
    public const SECTION_CATALOG = 'catalog_section';

    public const INVOICES = 'invoices';

    public const CATALOG_ITEMS = 'catalog_items';

    public const CATALOG_CATEGORIES = 'catalog_categories';

    public const CATALOG_TAXES = 'catalog_taxes';

    public static function getAll(): array
    {
        return [
            self::SECTION_CATALOG => [
                'label' => 'catalog',
                'icon' => 'Boxes',
                'sub_modules' => [
                    self::DASHBOARD_VIEW => [
                        'label' => 'dashboard',
                        'description' => 'View system health and metrics',
                        'route' => 'admin.dashboard',
                        'icon' => 'LayoutDashboard',
                    ],
                    self::INVOICES => [
                        'label' => 'invoices',
                        'description' => 'Manage invoices and payments',
                        'route' => 'admin.invoices.index',
                        'icon' => 'FileText',
                    ],
                    self::CATALOG_ITEMS => [
                        'label' => 'items',
                        'description' => 'Manage catalog items and pricing',
                        'route' => 'admin.catalog.items.index',
                        'icon' => 'Package',
                    ],
                    self::CATALOG_CATEGORIES => [
                        'label' => 'categories',
                        'description' => 'Manage item categories',
                        'route' => 'admin.catalog.categories.index',
                        'icon' => 'Layers',
                    ],
                    self::CATALOG_TAXES => [
                        'label' => 'taxes',
                        'description' => 'Manage tax rates and rules',
                        'route' => 'admin.catalog.taxes.index',
                        'icon' => 'Scale',
                    ],
                ],
            ],
            self::SECTION_ACCOUNT => [
                'label' => 'account', // Using 'account' as section label key, or management_section? Sidebar uses management_section for title, but account for rail tooltip.
                // Sidebar uses 'account' for rail tooltip, 'management_section' for Tier 2 title.
                // Permissions structure has label 'account'. I'll stick to 'account' and handle Tier 2 title logic in frontend or mapping.
                'icon' => 'Users',
                'sub_modules' => [
                    self::EMPLOYEE_DIRECTORY => [
                        'label' => 'employees',
                        'description' => 'Manage employee accounts',
                        'route' => 'admin.users.employees.index',
                        'icon' => 'Users',
                    ],
                    self::CUSTOMER_DIRECTORY => [
                        'label' => 'customers',
                        'description' => 'Manage customer accounts',
                        'route' => 'admin.users.customers.index',
                        'icon' => 'User',
                    ],
                    self::ACCESS_ROLES => [
                        'label' => 'access_roles',
                        'description' => 'Configure roles and security boundaries',
                        'route' => 'admin.roles.index',
                        'icon' => 'ShieldCheck',
                    ],
                ],
            ],
            self::SECTION_SYSTEM => [
                'label' => 'system_section',
                'icon' => 'LayoutDashboard',
                'sub_modules' => [
                    self::BUSINESS_BRANDING => [
                        'label' => 'business_branding',
                        'description' => 'Manage organization visual identity',
                        'route' => 'admin.business.branding',
                        'icon' => 'Palette',
                    ],
                    self::BUSINESS_LOGIC => [
                        'label' => 'business_logic',
                        'description' => 'Manage business settings (Currency, Timezone, Country)',
                        'route' => 'admin.business.settings.index',
                        'icon' => 'Settings',
                    ],
                    self::TAX_SETUP => [
                        'label' => 'tax_setup',
                        'description' => 'Configure global tax behavior',
                        'route' => 'admin.system.tax-settings.index',
                        'icon' => 'Receipt',
                    ],
                    self::PAYMENT_METHODS => [
                        'label' => 'payment_methods',
                        'description' => 'Manage payment methods and defaults',
                        'route' => 'admin.system.payment-methods.index',
                        'icon' => 'CreditCard',
                    ],
                    self::ACTIVITY_LOG => [
                        'label' => 'activity_log',
                        'description' => 'View system activity logs and audit trail',
                        'route' => 'admin.activity_logs.index',
                        'icon' => 'History',
                    ],
                ],
            ],
        ];
    }
}
