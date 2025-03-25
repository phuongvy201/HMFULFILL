@extends('layouts.app')

@section('title', 'US Product Catalog')

@section('content')
<section class="catalog-us-section">
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <!-- Overlay tối -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Nội dung chính -->
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold">US Product Catalog</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400">US Product Catalog</span>
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
                    <!-- Sản phẩm -->
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/tshirt.webp"
                                alt="" />
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
                                See Details
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/hoodie.jpg"
                                alt="" />
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
                                See Details
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/sweatshirt.jpg"
                                alt="" />
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
                                A comfortable, long-sleeved pullover, soft and warm, ideal
                                for layering.
                            </p>
                            <a
                                href="#"
                                class="inline-flex items-center px-3 button-read-more-catalog py-2 text-sm font-medium text-center text-white rounded-lg">
                                See Details
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/bella_canvas.jpg"
                                alt="" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Bella + Canvas
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A premium apparel brand known for soft, high-quality, and
                                stylish T-shirts.
                            </p>
                            <a
                                href="#"
                                class="inline-flex items-center px-3 button-read-more-catalog py-2 text-sm font-medium text-center text-white rounded-lg">
                                See Details
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/tank_top.jpg"
                                alt="Tank Top" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Tank Top
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A sleeveless, lightweight shirt, breathable, perfect for
                                summer or workouts.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/comfort_color.jpg"
                                alt="Comfort Colors" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Comfort Colors
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A garment-dyed clothing brand, offering vintage, soft, and
                                relaxed fits.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/tote_bag.jpg"
                                alt="Tote Bag" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Tote Bag
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A reusable, durable fabric bag, perfect for shopping or
                                carrying daily essentials.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/ornament.jpg"
                                alt="Ornament Wood" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Ornament Wood
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A decorative wooden ornament, great for home decor or
                                personalized gifts.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/colortone_wash.jpg"
                                alt="Colortone Wash" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Colortone Wash
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A unique tie-dye wash pattern, vibrant and stylish for
                                casual wear.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/crop_tshirt.jpg"
                                alt="Crop T-Shirt" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Crop T-Shirt
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A short, trendy T-shirt, perfect for layering or summer
                                outfits.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/micro_tank.jpg"
                                alt="Micro Rib Racer Tank" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Micro Rib Racer Tank
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A slim-fit, ribbed tank top, sporty and flattering.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/babytee.jpg"
                                alt="Baby Tee" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Baby Tee
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A fitted, cropped T-shirt, youthful, stylish, and trendy.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/recerback_tank_top.jpg"
                                alt="Racerback Tank Top" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Racerback Tank Top
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A sporty, sleeveless tank with a Y-shaped back for freedom
                                of movement.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/phonecase.jpg"
                                alt="Phone Case" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Phone Case
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A protective, stylish cover for smartphones, available in
                                various designs.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/magnet_car.jpg"
                                alt="Magnet Car" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Magnet Car
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A reusable car magnet, customizable for branding or
                                decoration.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/UV_sticker.jpg"
                                alt="UV Sticker" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    UV Sticker
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A waterproof, sun-resistant sticker, perfect for outdoor
                                use.
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
                    <div
                        class="flex-none w-[280px] md:w-[calc(50%-12px)] lg:w-[calc(33.33%-16px)] xl:w-[calc(25%-18px)] bg-white border border-gray-200 rounded-lg drop-shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <a href="#">
                            <img
                                class="rounded-t-lg h-80 mx-auto object-cover"
                                src="../assets/images/catalog/US/canvas.jpg"
                                alt="Canvas" />
                        </a>
                        <div class="p-5">
                            <a href="#">
                                <h5
                                    class="mb-2 card-title-catalog text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    Canvas
                                </h5>
                            </a>
                            <p
                                class="mb-3 font-normal text-gray-700 dark:text-gray-400 font-serif">
                                A durable fabric material used for bags, paintings, or
                                sturdy apparel.
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