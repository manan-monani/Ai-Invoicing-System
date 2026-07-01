<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ business_config('business_name', config('app.name', 'Laravel')) }}</title>

        @php
            $favicon = business_config('favicon_url');
            if ($favicon && !str_starts_with($favicon, 'http')) {
                $favicon = '/storage/' . $favicon;
            }
        @endphp
        <link rel="icon" href="{{ $favicon ?: '/favicon.ico' }}" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <style>
            :root {
                /* Dynamic Colors - Logic Controlled by config/branding/colors.php */
                @foreach(config('branding.colors.css_vars', []) as $key => $data)
                {{ $key }}: {{ $data['key'] ? business_config($data['key'], $data['default']) : $data['default'] }};
                @endforeach

                --font-primary: "{{ business_config('font_primary', config('branding.fonts.primary', 'Instrument Sans')) }}", sans-serif;
                --font-secondary: "{{ business_config('font_secondary', config('branding.fonts.secondary', 'Roboto')) }}", sans-serif;
            }
        </style>

        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
