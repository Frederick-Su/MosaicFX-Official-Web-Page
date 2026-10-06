<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', config('mosaic.name'))</title>
    <meta name="description" content="@yield('description')">
    <meta name="theme-color" content="#1B1030">
    <meta name="color-scheme" content="dark">
    <link rel="canonical" href="{{ url()->current() }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/brand/coin-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/apple-touch-icon.png') }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('mosaic.name') }}">
    <meta property="og:title" content="@yield('title', config('mosaic.name'))">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:url" content="{{ url()->current() }}">
    @if (file_exists(public_path('images/og.jpg')))
        <meta property="og:image" content="{{ asset('images/og.jpg') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:card" content="summary_large_image">
    @endif

    {{-- The three locked typefaces, from their official foundries (see resources/css/tokens/fonts.css). --}}
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.fontshare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://api.fontshare.com/v2/css?f[]=clash-display@600,700&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;1,9..144,400;1,9..144,600;1,9..144,700&amp;family=IBM+Plex+Mono:wght@400;500;600&amp;display=swap">

    <script>
        document.documentElement.classList.add('js');
        if (/[?&]still\b/.test(location.search)) document.documentElement.classList.add('is-still');
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
