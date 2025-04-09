<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta
        name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="icon" href="{{ asset('assets/images/hm icon.png') }}" type="image/x-icon">
    <title>
        HM Admin
    </title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CKEditor CDN -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>


</head>

<body
    x-data="{ 
        page: 'ecommerce', 
        loaded: true, 
        darkMode: $persist(false),  // Sử dụng $persist
        stickyMenu: false, 
        sidebarToggle: false, 
        scrollTop: false, 
        sidebarOpen: false 
    }"
    x-init="
        $watch('darkMode', value => localStorage.setItem('darkMode', value))"
    :class="{'dark bg-gray-900': darkMode === true}">
    <!-- ===== Preloader Start ===== -->
    @include('partials.admin.preloader')
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
        <!-- ===== Sidebar Start ===== -->
        @include('partials.admin.sidebar')
        <!-- ===== Sidebar End ===== -->

        <!-- ===== Content Area Start ===== -->
        <div
            class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
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
        <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->
    @vite('resources/js/index.js')
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>