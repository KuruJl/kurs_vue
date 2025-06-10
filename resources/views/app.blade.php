<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        <script>
            window.appName = "{{ config('app.name', 'Laravel') }}";
        </script>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
        <link href="https://fonts.cdnfonts.com/css/norwester" rel="stylesheet">
        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
        <style>
            .font-norwester { font-family: 'Norwester', sans-serif; }
            .font-rubik-light { font-family: 'Rubik', sans-serif; font-weight: 300; }
            .font-rubik-regular { font-family: 'Rubik', sans-serif; font-weight: 400; }
            .font-rubik-medium { font-family: 'Rubik', sans-serif; font-weight: 500; }
            .font-rubik-semibold { font-family: 'Rubik', sans-serif; font-weight: 600; }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#011F41]">
        @inertia
    </body>
</html>
