@extends('layouts.app')

@section('title', 'UK Product Catalog')

@section('content')
<section class="catalog-us-section">
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <!-- Overlay tối -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Nội dung chính -->
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold">UK Product Catalog</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400">UK Product Catalog</span>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto bg-white py-10">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Bộ lọc sản phẩm -->
            <!-- <aside class="w-full md:w-1/4 bg-gray-50 p-4 rounded-lg shadow-sm">
                    <h2 class="text-xl font-semibold mb-4">Filters</h2>
                    <div class="mb-4">
                        <form class="max-w-sm mx-auto">
                            <label for="countries" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Collection</label>
                            <select id="countries" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option selected>All</option>
                                <option value="US">Accessories</option>
                                <option value="CA">Bags</option>
                                <option value="FR">Beauty & Personal Care</option>
                                <option value="DE">Home & Kitchen</option>
                            </select>
                        </form>
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Price Range</label>
                        <input type="range" min="0" max="1000" class="w-full mt-1">
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Brand</label>
                        <input type="text" class="w-full p-2 border border-gray-300 rounded mt-1" placeholder="Search brand...">
                    </div>
                    <button class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Apply Filters</button>
                </aside> -->

            <!-- Danh sách sản phẩm -->
            <div class="overflow-x-auto hide-scrollbar px-4 md:px-0">
                <div
                    class="flex flex-nowrap md:flex-wrap gap-6 min-w-full pb-4 md:pb-0">
                    <!-- Sản phẩm: T-shirt -->
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg"
                                src="../assets/images/catalog/US/tshirt.webp"
                                alt="Ornament Wood" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    T-shirt
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A T-shirt is a casual, short-sleeved shirt made of cotton,
                                comfortable, breathable, and versatile.
                            </p>
                            <a
                                href="#"
                                class="inline-flex items-center px-3 button-read-more-catalog py-2 text-sm font-medium text-center text-white rounded-lg">
                                See details
                                <svg
                                    class="rtl:rotate-180 w-3.5 h-3.5 ms-2"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 14 10">
                                    <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M1 5h12m0 0L9 1m4 4L9 9" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Sản phẩm: Hoodie -->
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/hoodie.jpg"
                                alt="Acrylic" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Hoodie
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A warm, hooded sweatshirt, stylish, cozy, and perfect for
                                casual or chilly weather.
                            </p>
                            <a
                                href="#"
                                class="inline-flex items-center px-3 button-read-more-catalog py-2 text-sm font-medium text-center text-white rounded-lg">
                                See details
                                <svg
                                    class="rtl:rotate-180 w-3.5 h-3.5 ms-2"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 14 10">
                                    <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M1 5h12m0 0L9 1m4 4L9 9" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Sản phẩm: Sweatshirt -->
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/sweatshirt.jpg"
                                alt="Sweatshirt" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Sweatshirt
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A sweatshirt is a casual, comfortable, and versatile shirt
                                made of cotton, breathable, and perfect for active or chilly
                                weather.
                            </p>
                            <a
                                href="#"
                                class="inline-flex items-center px-3 button-read-more-catalog py-2 text-sm font-medium text-center text-white rounded-lg">
                                See details
                                <svg
                                    class="rtl:rotate-180 w-3.5 h-3.5 ms-2"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 14 10">
                                    <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M1 5h12m0 0L9 1m4 4L9 9" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Sản phẩm: Mug -->
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/UK/mug.jpg"
                                alt="Mug" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Mug
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A handcrafted wood decoration, rustic, durable, and perfect
                                for personalization.
                            </p>
                            <a
                                href="#"
                                class="inline-flex items-center px-3 button-read-more-catalog py-2 text-sm font-medium text-center text-white rounded-lg">
                                See details
                                <svg
                                    class="rtl:rotate-180 w-3.5 h-3.5 ms-2"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 14 10">
                                    <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M1 5h12m0 0L9 1m4 4L9 9" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection