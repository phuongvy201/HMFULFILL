<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('assets/images/hm icon.png') }}" type="image/x-icon">
    <title>@yield('title', 'HM Admin Dashboard')</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CKEditor CDN -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @yield('head')
</head>

<body
    x-data="{ 
        page: 'ecommerce', 
        loaded: true, 
        darkMode: $persist(false),
        stickyMenu: false, 
        sidebarToggle: false, 
        scrollTop: false, 
        sidebarOpen: false 
    }"
    x-init="$watch('darkMode', value => localStorage.setItem('darkMode', value))"
    :class="{'dark bg-gray-900': darkMode === true}">
    <!-- ===== Preloader Start ===== -->
    @include('partials.admin.preloader')
    <!-- ===== Preloader End ===== -->
    <div class="flex h-screen overflow-hidden">
        <!-- ===== Sidebar Start ===== -->
        @include('partials.sidebar')
        <!-- ===== Sidebar End ===== -->
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Small Device Overlay Start -->
            @include('partials.admin.overlay')
            <!-- Small Device Overlay End -->
            <!-- ===== Header Start ===== -->
            @include('partials.admin.header')
            <!-- ===== Header End ===== -->
            <!-- ===== Main Content Start ===== -->
            <main>
                @yield('content-admin')
            </main>
            <!-- ===== Main Content End ===== -->
        </div>
    </div>
    @vite('resources/js/index.js')
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>

</html>