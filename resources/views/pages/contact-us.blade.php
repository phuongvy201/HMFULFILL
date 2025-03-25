@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<section class="about-us-section">
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <!-- Overlay tối -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Nội dung chính -->
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold">Contact Us</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400">Contact Us</span>
            </div>
        </div>
    </div>

    <div
        class="max-w-6xl mx-auto p-6 bg-white flex flex-wrap md:flex-nowrap my-10">
        <!-- Thông tin liên hệ -->
        <div class="w-full md:w-1/2 p-6">
            <div class="flex items-center mt-2 p-2 rounded-lg">
                <i
                    style="border: solid 2px #f7961d"
                    class="fa-solid fa-location-dot rounded-full mr-2 p-2 border-2 text-orange-500"></i>
                24 Thạnh Xuân 14 Street, Thạnh Xuân Ward, District 12, Ho Chi Minh
                City.
            </div>
            <div class="flex items-center mt-2 p-2 rounded-lg">
                <i
                    style="border: solid 2px #f7961d"
                    class="fa-solid fa-phone rounded-full mr-2 p-2 border-2 text-orange-500"></i>
                +18563782798
            </div>
            <div class="flex items-center mt-2 p-2 rounded-lg">
                <i
                    style="border: solid 2px #f7961d"
                    class="fa-solid fa-envelope rounded-full mr-2 p-2 border-2 text-orange-500"></i>
                admin@bluprinter.com
            </div>
            <!-- Form liên hệ -->
            <!-- <h3 class="text-lg font-semibold mt-6">Liên hệ</h3>
                <form class="mt-4">
                    <input type="text" placeholder="Họ và tên" class="w-full p-2 border rounded-md mb-3">
                    <input type="email" placeholder="Email" class="w-full p-2 border rounded-md mb-3">
                    <textarea placeholder="Nội dung" class="w-full p-2 border rounded-md mb-3"></textarea>
                    <button class="bg-orange-500 text-white py-2 px-4 rounded-md w-full">Gửi liên hệ</button>
                </form> -->
        </div>

        <!-- Bản đồ -->
        <div class="w-full md:w-1/2 p-6">
            <div class="relative w-full h-0" style="padding-bottom: 56.25%">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.3025035011055!2d106.66257997451831!3d10.864580857562595!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317529cf786eb007%3A0xf7c713ae0ca59a6e!2zMjQgxJAuVGjhuqFuaCBYdcOibiAxNCwgVGjhuqFuaCBYdcOibiwgUXXhuq1uIDEyLCBI4buTIENow60gTWluaCwgVmnhu4d0IE5hbQ!5e0!3m2!1svi!2s!4v1742783634594!5m2!1svi!2s"
                    class="absolute top-0 left-0 w-full h-full"
                    style="border: 0"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</section>
@endsection