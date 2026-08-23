<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php($meta = $page['props']['meta'] ?? [])
        <title inertia>{{ $meta['title'] ?? config('app.name', 'KABA') }}</title>

        <link rel="icon" type="image/png" href="/images/logo.png">

        {{-- SEO / Open Graph --}}
        <meta name="description" content="{{ $meta['description'] ?? '' }}">
        <meta property="og:site_name" content="KABA">
        <meta property="og:type" content="{{ $meta['type'] ?? 'website' }}">
        <meta property="og:title" content="{{ $meta['title'] ?? 'KABA' }}">
        <meta property="og:description" content="{{ $meta['description'] ?? '' }}">
        <meta property="og:image" content="{{ $meta['image'] ?? url('/images/logo.png') }}">
        <meta property="og:url" content="{{ $meta['url'] ?? url()->current() }}">
        <meta property="og:locale" content="fr_FR">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $meta['title'] ?? 'KABA' }}">
        <meta name="twitter:description" content="{{ $meta['description'] ?? '' }}">
        <meta name="twitter:image" content="{{ $meta['image'] ?? url('/images/logo.png') }}">

        <!-- Fonts : Poppins -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- FontAwesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
