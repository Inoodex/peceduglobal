<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $setting = \App\Models\Setting::first();
            $siteName = !empty($setting->site_name) ? $setting->site_name : config('app.name', 'PecEduGlobal');
            $favicon = !empty($setting->favicon) ? $setting->favicon : '/favicon.ico';
        @endphp
        <title>{{ $siteName }}</title>
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <script>
            window.siteName = "{{ $siteName }}";
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-bg-light dark:bg-bg-dark transition-colors duration-300 font-['Public_Sans',sans-serif]">
        <div id="app"></div>
    </body>
</html>
