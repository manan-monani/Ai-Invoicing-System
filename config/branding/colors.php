<?php

// Static defaults for fallback
$primary = '#4f7fa6'; // Soft sky blue
$primary_light = '#e9f1f7'; // Misty blue tint
$primary_dark_text = '#b9d3e6'; // Readable in dark mode

$sidebar_rail_bg = '#2f4f66'; // Deep slate blue
$sidebar_rail_bg_dark = '#213746'; // Dark slate blue

$sidebar_icon_color = '#ffffffff';
$sidebar_icon_color_dark = '#ffffffff';

$secondary = '#6a747d'; // Muted steel
$success = '#4a9c88';
$danger = '#c96b6b';
$warning = '#c9a35a';
$info = '#4f85a6';

$toast_success = '#4a9c88';
$toast_error = '#c96b6b';
$toast_warning = '#c9a35a';

return [
    'primary' => $primary,
    'primary_light' => $primary_light,
    'primary_dark_text' => $primary_dark_text,
    'secondary' => $secondary,
    'success' => $success,
    'danger' => $danger,
    'warning' => $warning,
    'info' => $info,

    'toast_success' => $toast_success,
    'toast_error' => $toast_error,
    'toast_warning' => $toast_warning,

    'admin' => [
        'sidebar_bg' => '#ffffff',
        'sidebar_bg_dark' => '#0f172a', // Slate 900
        'sidebar_border' => '#e2e8f0',
        'sidebar_border_dark' => '#1e293b',

        'sidebar_rail_bg' => $sidebar_rail_bg,
        'sidebar_rail_bg_dark' => $sidebar_rail_bg_dark,

        'header_bg' => '#ffffffcc', // White / 80%
        'header_bg_dark' => '#0f172acc',
        'sidebar_icon_color' => $sidebar_icon_color,
        'sidebar_icon_color_dark' => $sidebar_icon_color_dark,

        'active_item_bg' => $primary_light,
        'active_item_text' => $primary,
        'active_item_border' => $primary,

        'active_item_bg_dark' => $primary.'20', // ~12% opacity
        'active_item_text_dark' => $primary_dark_text,
        'active_item_border_dark' => $primary_dark_text,

        'content_bg' => '#f4f6f8',
        'content_bg_dark' => '#0f1418',

        'card_bg' => '#ffffff',
        'card_bg_dark' => '#0e151a',
        'card_border' => '#e1e6eb',
        'card_border_dark' => '#1f2a33',
    ],

    // Mapping for CSS Variables in app.blade.php
    // Format: 'CSS_VAR' => ['key' => 'DB_KEY', 'default' => 'DEFAULT_VALUE']
    'css_vars' => [
        '--brand-primary' => ['key' => 'primary', 'default' => $primary],
        '--brand-secondary' => ['key' => 'secondary', 'default' => $secondary],
        '--brand-success' => ['key' => 'success', 'default' => $success],
        '--brand-danger' => ['key' => 'danger', 'default' => $danger],
        '--brand-warning' => ['key' => 'warning', 'default' => $warning],
        '--brand-info' => ['key' => 'info', 'default' => $info],

        '--toast-success' => ['key' => 'toast_success', 'default' => $toast_success],
        '--toast-error' => ['key' => 'toast_error', 'default' => $toast_error],
        '--toast-warning' => ['key' => 'toast_warning', 'default' => $toast_warning],

        '--admin-sidebar-bg' => ['key' => 'admin_sidebar_bg', 'default' => '#ffffff'],
        '--admin-sidebar-bg-dark' => ['key' => 'admin_sidebar_bg_dark', 'default' => '#0f172a'],
        '--admin-sidebar-border' => ['key' => 'admin_sidebar_border', 'default' => '#e2e8f0'],
        '--admin-sidebar-border-dark' => ['key' => 'admin_sidebar_border_dark', 'default' => '#1e293b'],

        '--admin-sidebar-rail-bg' => ['key' => 'sidebar_rail_bg', 'default' => $sidebar_rail_bg],
        '--admin-sidebar-rail-bg-dark' => ['key' => 'sidebar_rail_bg_dark', 'default' => $sidebar_rail_bg_dark],

        '--admin-sidebar-icon' => ['key' => 'sidebar_icon_color', 'default' => $sidebar_icon_color],
        '--admin-sidebar-icon-dark' => ['key' => 'sidebar_icon_color_dark', 'default' => $sidebar_icon_color_dark],

        '--admin-header-bg' => ['key' => 'admin_header_bg', 'default' => '#ffffffcc'],
        '--admin-header-bg-dark' => ['key' => 'admin_header_bg_dark', 'default' => '#0f172acc'],

        '--admin-active-item-bg' => ['key' => 'primary_light', 'default' => $primary_light],
        '--admin-active-item-text' => ['key' => null, 'default' => 'var(--brand-primary)'],
        '--admin-active-item-border' => ['key' => null, 'default' => 'var(--brand-primary)'],

        '--admin-active-item-bg-dark' => ['key' => null, 'default' => 'color-mix(in srgb, var(--brand-primary), transparent 85%)'],
        '--admin-active-item-text-dark' => ['key' => 'primary_dark_text', 'default' => $primary_dark_text],
        '--admin-active-item-border-dark' => ['key' => null, 'default' => 'color-mix(in srgb, var(--brand-primary), white 20%)'],

        '--admin-content-bg' => ['key' => 'admin_content_bg', 'default' => '#f8fafc'],
        '--admin-content-bg-dark' => ['key' => 'admin_content_bg_dark', 'default' => '#020617'],

        '--admin-card-bg' => ['key' => 'admin_card_bg', 'default' => '#ffffff'],
        '--admin-card-bg-dark' => ['key' => 'admin_card_bg_dark', 'default' => '#0f172a'],
        '--admin-card-border' => ['key' => 'admin_card_border', 'default' => '#f1f5f9'],
        '--admin-card-border-dark' => ['key' => 'admin_card_border_dark', 'default' => '#1e293b'],
    ],
];
