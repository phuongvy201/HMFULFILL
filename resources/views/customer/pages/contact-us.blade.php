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
            <!-- Tên công ty -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">CÔNG TY TNHH HM FULFILL</h2>
                <p class="text-lg text-gray-600 font-medium">(HM FULFILL COMPANY LIMITED)</p>
            </div>

            <!-- Thông tin liên hệ -->
            <div class="space-y-2">
                <div class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <i
                        style="border: solid 2px #f7961d"
                        class="fa-solid fa-location-dot rounded-full mr-4 p-3 border-2 text-orange-500 flex-shrink-0"></i>
                    <span class="text-gray-700">
                        63/9Đ Ấp Chánh 1, Xã Tân Xuân, Huyện Hóc Môn, Thành phố Hồ Chí Minh, Việt Nam
                    </span>
                </div>

                <div class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <i
                        style="border: solid 2px #f7961d"
                        class="fa-solid fa-phone rounded-full mr-4 p-3 border-2 text-orange-500 flex-shrink-0"></i>
                    <a href="tel:+18563782798" class="text-gray-700 hover:text-orange-500 transition-colors">
                        0767.383.676
                    </a>
                </div>

                <div class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <i
                        style="border: solid 2px #f7961d"
                        class="fa-solid fa-envelope rounded-full mr-4 p-3 border-2 text-orange-500 flex-shrink-0"></i>
                    <a href="mailto:admin@hmfulfill.com" class="text-gray-700 hover:text-orange-500 transition-colors">
                        admin@hmfulfill.com
                    </a>
                </div>

                <div class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <i
                        style="border: solid 2px #f7961d"
                        class="fa-solid fa-building rounded-full mr-4 p-3 border-2 text-orange-500 flex-shrink-0"></i>
                    <span class="text-gray-700">
                        MST: 0318247249
                    </span>
                </div>
            </div>

            <!-- Support 24/7 & QR Codes -->
            <div class="mt-8 bg-gradient-to-r from-orange-50 to-orange-100 p-6 rounded-lg">
                <div class="text-center mb-6">
                    <h3 class="text-xl font-bold text-orange-600 mb-2">SUPPORT 24/7</h3>
                    <p class="text-gray-600">We are here to help you</p>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Zalo QR -->
                    <div class="text-center">
                        <div class="bg-white p-4 rounded-lg shadow-sm border mb-3">
                            <img src="{{ asset('assets/images/qrcode-zalo.jpg') }}"
                                alt="Zalo QR Code"
                                class="w-24 h-24 mx-auto"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display:none;" class="w-24 h-24 mx-auto bg-gray-200 flex items-center justify-center rounded">
                                <i class="fab fa-facebook-messenger text-blue-500 text-2xl"></i>
                            </div>
                        </div>
                        <h4 class="font-semibold text-blue-600 mb-1">Chat Zalo</h4>
                        <p class="text-sm text-gray-600">Scan QR for contact</p>
                    </div>

                    <!-- Telegram QR -->
                    <div class="text-center">
                        <div class="bg-white p-4 rounded-lg shadow-sm border mb-3">
                            <img src="{{ asset('assets/images/qrcode-tele.jpg') }}"
                                alt="Telegram QR Code"
                                class="w-24 h-24 mx-auto"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display:none;" class="w-24 h-24 mx-auto bg-gray-200 flex items-center justify-center rounded">
                                <i class="fab fa-telegram text-blue-400 text-2xl"></i>
                            </div>
                        </div>
                        <h4 class="font-semibold text-blue-600 mb-1">Chat Telegram</h4>
                        <p class="text-sm text-gray-600">Scan QR for contact</p>
                    </div>
                </div>

                <!-- Contact Methods -->
                <div class="mt-6 flex justify-center space-x-4">
                    <a href="https://zalo.me/0767383676" target="_blank"
                        class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        <i class="fab fa-facebook-messenger mr-2"></i>
                        Zalo Chat
                    </a>
                    <a href="https://t.me/hmfulfill" target="_blank"
                        class="flex items-center px-4 py-2 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition-colors">
                        <i class="fab fa-telegram mr-2"></i>
                        Telegram
                    </a>
                </div>
            </div>

            <!-- Giờ làm việc -->

        </div>

        <!-- Bản đồ -->
        <div class="w-full md:w-1/2 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Location on map</h3>
            <div class="relative w-full h-0" style="padding-bottom: 56.25%">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.8!2d106.6!3d10.8!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDQ4JzAwLjAiTiAxMDbCsDM2JzAwLjAiRQ!5e0!3m2!1svi!2s!4v1642783634594!5m2!1svi!2s"
                    class="absolute top-0 left-0 w-full h-full rounded-lg shadow-md"
                    style="border: 0"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

            <!-- Hướng dẫn đi lại -->

        </div>
    </div>
</section>
@endsection