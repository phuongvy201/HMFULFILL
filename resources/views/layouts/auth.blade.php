<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @vite('resources/css/app.css')
    <link rel="icon" href="{{ asset('assets/images/hm icon.png') }}" type="image/x-icon">

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link
        rel="stylesheet"
        href="{{ asset('assets/fonts/font-awesome/css/all.min.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="{{ asset('assets/fonts/font-awesome/js/all.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />

</head>

<body class="product-sans-regular">
    @yield('content-auth')
</body>

</html>