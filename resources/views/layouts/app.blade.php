<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="icon" href="{{ asset('assets/images/hm icon.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/product.css') }}" />

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link
        rel="stylesheet"
        href="{{ asset('assets/fonts/font-awesome/css/all.min.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="{{ asset('assets/fonts/font-awesome/js/all.min.js') }}"></script>
    @vite('resources/css/app.css')
</head>

<body>
    <header class="header container mx-auto">
        <div
            class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto px-3">
            <a
                href="/"
                class="flex items-center space-x-3 rtl:space-x-reverse">
                <img
                    src="{{ asset('assets/images/logo.png') }}"
                    class="h-32"
                    alt="Flowbite Logo" />
            </a>
            <div class="flex lg:order-2 space-x-3 lg:space-x-0 rtl:space-x-reverse">
                <a
                    href="/signin"
                    type="button"
                    style="background-color: #f7961d"
                    class="button-sign-in text-white font-medium rounded-lg text-sm px-6 py-3 text-center transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                    Sign in
                </a>
                <button
                    data-collapse-toggle="navbar-cta"
                    type="button"
                    class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
                    aria-controls="navbar-cta"
                    aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg
                        class="w-5 h-5"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 17 14">
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M1 1h15M1 7h15M1 13h15" />
                    </svg>
                </button>
            </div>
            <div
                class="flex flex-col items-center justify-center hidden w-full lg:flex lg:w-auto lg:order-1 rounded-md lg:rounded-full border-gray-100 border-2 shadow-xl lg:px-8 lg:py-3"
                id="navbar-cta">
                <ul
                    class="flex flex-col w-full items-center justify-center font-medium p-4 lg:p-0 mt-4 bg-gray-50 lg:space-x-8 rtl:space-x-reverse lg:flex-row lg:mt-0 lg:border-0 lg:bg-white">
                    <li class="w-full lg:w-auto flex justify-center mx-auto">
                        <a
                            href="#"
                            class="navigation-link py-2 px-3 text-gray-700 rounded-sm hover:bg-gray-100 lg:hover:bg-transparent"
                            aria-current="page">How it works</a>
                    </li>
                    <li class="w-full lg:w-auto flex justify-center mx-auto">
                        <a
                            href="/products"
                            class="navigation-link py-2 px-3 text-gray-700 rounded-sm hover:bg-gray-100 lg:hover:bg-transparent">All products</a>
                    </li>
                    <li class="w-full lg:w-auto flex justify-center mx-auto">
                        <a
                            href="#"
                            class="navigation-link py-2 px-3 text-gray-700 rounded-sm hover:bg-gray-100 lg:hover:bg-transparent">SKU</a>
                    </li>
                    <li class="w-full lg:w-auto flex justify-center mx-auto">
                        <a
                            href="#"
                            class="navigation-link py-2 px-3 text-gray-700 rounded-sm hover:bg-gray-100 lg:hover:bg-transparent">Blogs</a>
                    </li>
                    <li class="w-full lg:w-auto flex justify-center mx-auto">
                        <a
                            href="/pages/contact-us"
                            class="navigation-link py-2 px-3 text-gray-700 rounded-sm hover:bg-gray-100 lg:hover:bg-transparent">Contact Us</a>
                    </li>
                    <li class="w-full lg:w-auto flex justify-center mx-auto">
                        <a
                            href="/pages/help-center"
                            class="navigation-link py-2 px-3 text-gray-700 rounded-sm hover:bg-gray-100 lg:hover:bg-transparent">Help Center</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="bg-orange-50 py-12 px-4 text-center">
            <h1 class="text-4xl font-bold text-neutral-800">
                BE IN TOUCH WITH US
            </h1>
            <p class="text-gray-600 mt-2 product-sans-regular">
                You'll also get the latest discounts, news and gift ideas sent to your
                inbox every week.
            </p>

            <div class="mt-6 flex justify-center items-center gap-2 mb-12">
                <input
                    type="email"
                    placeholder="Enter your email address..."
                    style="box-shadow: #005366"
                    class="px-4 py-3 w-60 lg:w-80 border rounded-lg focus:ring-2 focus:ring-orange-400 focus:outline-none product-sans-regular" />
                <button
                    class="button-signup text-white px-6 py-3 rounded-lg product-sans-regular">
                    Get Started
                </button>
            </div>
        </div>
        <footer class="bg-white text-gray-700 text-sm">
            <div class="container mx-auto px-6 py-8">
                <div class="mx-auto w-full max-w-screen-xl">
                    <div class="grid grid-cols-2 gap-8 px-4 py-6 lg:py-8 md:grid-cols-4">
                        <div>
                            <a href="/"><img src="{{ asset('assets/images/logo.png') }}" class="h-20 md:h-24 lg:h-32 max-w-full" alt="Logo" /></a>
                            <div class="flex flex-wrap gap-3 mt-2">
                                <a href="#" class="text-pink-500 icon-social">
                                    <img src="{{ asset('assets/images/icon/instagram (2).png') }}" alt="Instagram" class="w-8 md:w-10 lg:w-12 h-auto" />
                                </a>

                                <a href="#" class="text-blue-500 icon-social">
                                    <img src="{{ asset('assets/images/icon/facebook (1).png') }}" alt="Facebook" class="w-8 md:w-10 lg:w-12 h-auto" />
                                </a>
                                <a href="#" class="text-green-500 icon-social">
                                    <img src="{{ asset('assets/images/icon/whatsapp.png') }}" alt="WhatsApp" class="w-8 md:w-10 lg:w-12 h-auto" />
                                </a>
                                <a href="#" class="text-red-500 icon-social">
                                    <img src="{{ asset('assets/images/icon/youtube (1).png') }}" alt="YouTube" class="w-6 md:w-8 lg:w-10 h-auto" />
                                </a>
                                <a href="#" class="text-black icon-social">
                                    <img src="{{ asset('assets/images/icon/tiktok (1).png') }}" alt="TikTok" class="w-6 md:w-8 lg:w-10 h-auto" />
                                </a>
                                <a href="#" class="text-blue-400 icon-social">
                                    <img src="{{ asset('assets/images/icon/twitter (2).png') }}" alt="Twitter" class="w-6 md:w-8 lg:w-10 h-auto" />
                                </a>
                            </div>
                        </div>
                        <div>
                            <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">HM Fulfill</h2>
                            <ul class="text-gray-500 dark:text-gray-400 font-medium policy-link">
                                <li class="mb-4"><a href="#" class="hover:text-orange-500">About Us</a></li>
                                <li class="mb-4"><a href="/pages/catalog-us" class="hover:text-orange-500">Catalog US</a></li>
                                <li class="mb-4"><a href="/pages/catalog-uk" class="hover:text-orange-500">Catalog UK</a></li>
                                <li class="mb-4"><a href="/pages/catalog-vn" class="hover:text-orange-500">Catalog VN</a></li>
                            </ul>
                        </div>
                        <div>
                            <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Support</h2>
                            <ul class="text-gray-500 dark:text-gray-400 font-medium policy-link">
                                <li class="mb-4"><a href="/pages/contact-us" class="hover:text-orange-500">Contact Us</a></li>
                                <li class="mb-4"><a href="#" class="hover:text-orange-500">FAQs</a></li>
                                <li class="mb-4"><a href="#" class="hover:text-orange-500">How it works</a></li>
                                <li class="mb-4"><a href="#" class="hover:text-orange-500">Help Center</a></li>
                            </ul>
                        </div>
                        <div>
                            <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Policies</h2>
                            <ul class="text-gray-500 dark:text-gray-400 font-medium policy-link">
                                <li class="mb-4"><a href="/pages/shipping-policy" class="hover:text-orange-500">Shipping Policy</a></li>
                                <li class="mb-4"><a href="/pages/payment-policy" class="hover:text-orange-500">Payment Policy</a></li>
                                <li class="mb-4"><a href="/pages/return-refund-policy" class="hover:text-orange-500">Return & Refund Policy</a></li>
                                <li class="mb-4"><a href="/pages/term-condition" class="hover:text-orange-500">Terms & Conditions</a></li>
                                <li class="mb-4"><a href="/pages/privacy-policy" class="hover:text-orange-500">Privacy Policy</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </footer>


        </div>
        <div class="px-4 py-6 bg-gray-100 dark:bg-gray-700 md:flex md:items-center md:justify-between text-center">
            <span class="text-sm text-gray-500 dark:text-gray-300 sm:text-center mx-auto">
                © 2025 <a href="/">HM Fulfill</a>. All Rights Reserved.
            </span>

        </div>
    </footer>
</body>

</html>