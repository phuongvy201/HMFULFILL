@extends('layouts.app')

@section('title', 'Product Detail')

@section('content')
<section class="products">
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold">Mineral Wash T-Shirt DTG</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-gray-200">Apparels</span>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400">Mineral Wash T-Shirt DTG</span>
            </div>
        </div>
    </div>
    <div
        class="max-w-7xl  mx-auto bg-white p-6 mb-10 shadow-md rounded-lg product-sans-regular">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Hình ảnh sản phẩm -->

            <div id="gallery" class="relative w-full">
                <!-- Main image -->
                <div class="relative h-56 overflow-hidden rounded-lg md:h-96">
                    <img
                        id="main-image"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-1.jpg"
                        class="absolute block w-full h-full object-cover -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                        alt="" />
                </div>

                <!-- Thumbnails -->
                <div class="flex gap-4 mt-4 custom-scrollbar overflow-x-auto">
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-1.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition thumbnail-active"
                        alt="" />
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-2.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                        alt="" />
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-3.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                        alt="" />
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-4.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                        alt="" />
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-5.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                        alt="" />
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-3.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                        alt="" />
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-4.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                        alt="" />
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-5.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                        alt="" />
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-3.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                        alt="" />
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-4.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                        alt="" />
                    <img
                        onclick="showImage(this)"
                        src="https://flowbite.s3.amazonaws.com/docs/gallery/square/image-5.jpg"
                        class="w-20 h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                        alt="" />
                </div>
            </div>
            <!-- Thông tin sản phẩm -->
            <div>
                <h2 class="text-gray-600 text-sm uppercase">Apparels</h2>
                <h1 class="text-2xl font-bold text-gray-800 font-serif">
                    Mineral Wash T-Shirt DTG
                </h1>
                <p class="text-gray-500 text-sm font-medium mt-1">
                    <span class="bg-gray-200 px-2 py-1 rounded">SKU: LFUG32W12OZ</span>
                </p>
                <p style="color: #f7961d" class="text-2xl mt-2 roboto-bold">
                    Price: $9.50
                </p>
                <p class="text-sm text-gray-500">(Include ship price: ~$15.00)</p>

                <!-- Tùy chọn -->
                <div class="mt-4">
                    <label
                        style="color: #005366"
                        class="block text-gray-700 font-semibold">Color</label>
                    <select
                        id="countries"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected>Choose a color</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                        <option value="FR">France</option>
                        <option value="DE">Germany</option>
                    </select>
                </div>
                <div class="mt-4">
                    <label
                        style="color: #005366"
                        class="block text-gray-700 font-semibold">Capacity</label>
                    <select
                        id="countries"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected>Choose a capacity</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                        <option value="FR">France</option>
                        <option value="DE">Germany</option>
                    </select>
                </div>
                <div class="mt-4">
                    <label
                        style="color: #005366"
                        class="block text-gray-700 font-semibold">Size</label>
                    <select
                        id="countries"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected>Choose a size</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                        <option value="FR">France</option>
                        <option value="DE">Germany</option>
                    </select>
                </div>

                <!-- Vận chuyển -->
                <div class="mt-6">
                    <label
                        style="color: #005366"
                        class="block text-gray-700 font-semibold">Ship To</label>
                    <select
                        id="countries"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected>United States</option>
                        <option value="CA">Canada</option>
                        <option value="FR">France</option>
                        <option value="DE">Germany</option>
                    </select>
                </div>

                <div class="mt-4 shadow-md p-4 rounded-lg bg-white">
                    <!-- Standard Shipping -->
                    <label
                        class="flex justify-between items-center cursor-pointer p-3 bg-white rounded-lg hover:bg-gray-100">
                        <div class="flex items-center gap-3">
                            <input
                                type="radio"
                                name="shipping"
                                value="standard"
                                class="hidden peer" />
                            <div
                                class="w-4 h-4 border-2 border-gray-400 rounded-full flex items-center justify-center peer-checked:border-blue-500 peer-checked:bg-blue-500">
                                <div
                                    class="w-2.5 h-2.5 bg-white rounded-full hidden peer-checked:block"></div>
                            </div>
                            <img
                                class="h-5"
                                src="{{ asset('assets/images/icon/transport.png') }}"
                                alt="Standard Shipping" />
                            <div>
                                <span class="text-gray-800 font-medium">Standard Shipping</span>
                                <p class="text-gray-500 text-sm">3-9 business days</p>
                            </div>
                        </div>
                        <p class="text-lg">$5.50</p>
                    </label>

                    <!-- Ground Shipping -->
                    <label
                        class="flex justify-between items-center cursor-pointer p-3 bg-white rounded-lg hover:bg-gray-100 mt-3">
                        <div class="flex items-center gap-3">
                            <input
                                type="radio"
                                name="shipping"
                                value="ground"
                                class="hidden peer" />
                            <div
                                class="w-4 h-4 border-2 border-gray-400 rounded-full flex items-center justify-center peer-checked:border-blue-500 peer-checked:bg-blue-500">
                                <div
                                    class="w-2.5 h-2.5 bg-white rounded-full hidden peer-checked:block"></div>
                            </div>
                            <img
                                class="h-5"
                                src="{{ asset('assets/images/icon/travel.png') }}"
                                alt="Ground Shipping" />
                            <div>
                                <span class="text-gray-800 font-medium">Ground Shipping</span>
                                <p class="text-gray-500 text-sm">2-5 business days</p>
                            </div>
                        </div>
                        <p class="text-lg">$11.99</p>
                    </label>
                </div>

                <!-- Nút hành động -->

                <!-- Chia sẻ -->
            </div>
        </div>
    </div>
    <div
        class="max-w-5xl mx-auto p-4 border rounded-lg shadow-sm product-sans-regular mb-10">
        <!-- Tabs -->
        <div class="flex border-b">
            <button style="border-bottom: 2px solid #f7961d" class="px-4 py-2">
                Product Information
            </button>
            <button class="px-4 py-2 font-medium">Shipping Information</button>
            <button class="px-4 py-2 text-gray-800 font-medium">Note</button>
        </div>

        <!-- Content -->
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-800">
                ART UPLOAD FILE REQUIREMENTS
            </h2>
            <p class="mt-2 text-gray-600">
                Every image you upload to our website must meet these basic
                requirements or the software will not accept it. See below for
                additional requirements. When sending us your art file, please be
                sure to include the following information:
            </p>

            <ul class="mt-4 space-y-2 text-gray-700">
                <li><strong>Art Dimension:</strong> 4500 x 5100 pixels</li>
                <li><strong>Art Resolution:</strong> 300 DPI</li>
            </ul>

            <p class="mt-4 text-gray-600">
                High-resolution images must not contain watermarks, signatures, or
                photo borders. Additionally, colors used online are different from
                those used in print, so it’s important to make sure the colors in
                your images are set correctly.
            </p>

            <p class="mt-4 text-gray-600">Support CYMK colors.</p>

            <!-- Download Button -->
            <div class="mt-6">
                <button
                    class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-pink-500 to-orange-400 group-hover:from-pink-500 group-hover:to-orange-400 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-pink-200 dark:focus:ring-pink-800">
                    <span
                        class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-transparent group-hover:dark:bg-transparent">
                        Get Template
                    </span>
                </button>
            </div>
        </div>
    </div>
</section>
@endsection