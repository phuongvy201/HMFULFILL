@extends('layouts.app')

@section('title', 'Products')

@section('content')
<section class="products">
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold">Products</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400">Products</span>
            </div>
        </div>
    </div>
    <div
        class=" grid grid-cols-12 gap-6 max-w-7xl mx-auto px-3 py-10 mt-10 product-sans-regular">
        <!-- Categories & Location sidebar - ẩn trên mobile -->
        <aside
            class="mt-14 col-span-12 md:col-span-4 lg:col-span-3 hidden md:block">
            <div class="w-64 mb-10">
                <div
                    style="background: linear-gradient(to right, #f7961d, #f7961d)"
                    class="relative text-white text-xl rounded-t-lg py-2 px-4">
                    <span>CATEGORIES</span>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-white"></div>
                </div>
                <div
                    style="border: 1px solid #f7961d"
                    class="border rounded-lg mt-2 p-4">
                    <ul class="space-y-2">
                        <li class="text-gray-500"><a href="/product/mineral-wash-t-shirt-dtg">All</a></li>
                        <li class="text-gray-500"><a href="/product/mineral-wash-t-shirt-dtg">Accessories</a></li>
                        <li class="text-gray-500"><a href="/product/mineral-wash-t-shirt-dtg">Bags</a></li>
                        <li class="text-gray-500"><a href="/product/mineral-wash-t-shirt-dtg">Beauty & personal care</a></li>
                        <li class="text-gray-500 flex justify-between items-center">
                    </ul>
                </div>
            </div>
            <div class="w-64">
                <div
                    style="background: linear-gradient(to right, #f7961d, #f7961d)"
                    class="relative text-white text-xl rounded-t-lg py-2 px-4">
                    <span>LOCATION</span>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-white"></div>
                </div>
                <div
                    style="border: 1px solid #f7961d"
                    class="border rounded-lg mt-2 p-4">
                    <ul class="space-y-2">
                        <li class="text-gray-500"><a href="">All</a></li>
                        <li class="text-gray-500"><a href="">United States</a></li>
                        <li class="text-gray-500"><a href="">United Kingdom</a></li>
                        <li class="text-gray-500"><a href="">Vietnam</a></li>
                        <li class="text-gray-500"><a href="">France</a></li>
                        <li class="text-gray-500"><a href="">Germany</a></li>
                    </ul>
                </div>
            </div>
        </aside>

        <main class="col-span-12 md:col-span-8 lg:col-span-9">
            <div
                class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                <h2 class="mx-auto text-2xl text-orange-500 font-serif">
                    All Products
                </h2>

                <!-- Filter options cho mobile -->
                <div class="flex gap-4 w-full md:w-auto md:hidden">
                    <select
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option selected disabled>Select Category</option>
                        <option value="all">All</option>
                        <option value="accessories">Accessories</option>
                        <option value="bags">Bags</option>
                        <option value="beauty">Beauty & Personal Care</option>
                        <option value="home">Home & Kitchen</option>
                    </select>

                    <select
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option selected disabled>Select Location</option>
                        <option value="all">All</option>
                        <option value="us">United States</option>
                        <option value="uk">United Kingdom</option>
                        <option value="vn">Vietnam</option>
                    </select>
                </div>

                <!-- Sort option -->
                <form class="w-full md:w-auto">
                    <select
                        id="countries"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected>Choose a country</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                        <option value="FR">France</option>
                        <option value="DE">Germany</option>
                    </select>
                </form>
            </div>
            <div class="grid grid-cols-1">
                <form class="w-full mx-auto">
                    <label
                        for="default-search"
                        class="text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg
                                class="w-4 h-4 text-gray-500 dark:text-gray-400"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 20 20">
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                        </div>
                        <input
                            type="search"
                            id="default-search"
                            class="block w-full p-3 pl-10   input-search  text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Search Mockups, Logos..."
                            required />
                        <button
                            type="submit"
                            class="text-gray-600 absolute right-2 bottom-1 bg-gray-300 hover:bg-orange-500 box-shadow-none font-medium rounded-lg text-sm px-4 py-2">
                            Search
                        </button>
                    </div>
                </form>
            </div>
            <div
                class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-2">
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1742283704-il_794xN.5935748001_ts0h.jpg"
                            alt="Bàn Aillen"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Mens Upgraded Military Tactical Shorts
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1742283695-il_794xN.6707014116_m28v.jpg"
                            alt="Bàn ARABICA"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1740650877-il_1588xN.6497327242_n1ud.jpg"
                            alt="Bàn Binas"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1740650761-il_794xN.6584446556_56y4.jpg"
                            alt="Bàn Aillen 01"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1742283704-il_794xN.5935748001_ts0h.jpg"
                            alt="Bàn Aillen"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1742283695-il_794xN.6707014116_m28v.jpg"
                            alt="Bàn ARABICA"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1740650877-il_1588xN.6497327242_n1ud.jpg"
                            alt="Bàn Binas"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1740650761-il_794xN.6584446556_56y4.jpg"
                            alt="Bàn Aillen 01"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1742283704-il_794xN.5935748001_ts0h.jpg"
                            alt="Bàn Aillen"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1742283695-il_794xN.6707014116_m28v.jpg"
                            alt="Bàn ARABICA"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1740650877-il_1588xN.6497327242_n1ud.jpg"
                            alt="Bàn Binas"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1740650761-il_794xN.6584446556_56y4.jpg"
                            alt="Bàn Aillen 01"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1742283704-il_794xN.5935748001_ts0h.jpg"
                            alt="Bàn Aillen"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1742283695-il_794xN.6707014116_m28v.jpg"
                            alt="Bàn ARABICA"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1740650877-il_1588xN.6497327242_n1ud.jpg"
                            alt="Bàn Binas"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            Pullover Tops with Elbow Patches Sweatshirt
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
                <div class="border p-4 rounded-lg shadow-md">
                    <a href="/product/mineral-wash-t-shirt-dtg">
                        <img
                            src="https://s3.amazonaws.com/image.bluprinter/products/1740650761-il_794xN.6584446556_56y4.jpg"
                            alt="Bàn Aillen 01"
                            class="w-full h-48 object-cover" />
                        <h3
                            style="color: #005366"
                            class="product-item mt-2 font-bold text-sm">
                            <a href="">Pullover Tops with Elbow Patches Sweatshirt</a>
                        </h3>
                        <p class="text-orange-500">13.20$</p>
                    </a>
                </div>
            </div>
            <!-- Pagination Wrapper -->
            <div
                class="mt-10 grid justify-center sm:flex sm:justify-end sm:items-center gap-1">
                <!-- Pagination Wrapper -->
                <nav class="flex items-center gap-x-1" aria-label="Pagination">
                    <button
                        type="button"
                        class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex jusify-center items-center gap-x-2 text-sm rounded-lg hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none"
                        aria-label="Previous">
                        <svg
                            class="shrink-0 size-3.5"
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"></path>
                        </svg>
                        <span class="sr-only">Previous</span>
                    </button>
                    <div class="flex items-center gap-x-1">
                        <button
                            style="color: #f7961d"
                            type="button"
                            class="min-h-9.5 min-w-9.5 flex justify-center items-center bg-gray-200 py-2 px-3 text-sm rounded-lg focus:outline-hidden focus:bg-gray-300 disabled:opacity-50 disabled:pointer-events-none"
                            aria-current="page">
                            1
                        </button>
                        <button
                            type="button"
                            hover
                            class="min-h-9.5 min-w-9.5 flex justify-center items-center text-gray-800 hover:bg-gray-100 py-2 px-3 text-sm rounded-lg focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none">
                            2
                        </button>
                        <button
                            type="button"
                            class="min-h-9.5 min-w-9.5 flex justify-center items-center text-gray-800 hover:bg-gray-100 py-2 px-3 text-sm rounded-lg focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none">
                            3
                        </button>
                        <div class="hs-tooltip inline-block">
                            <button
                                type="button"
                                class="hs-tooltip-toggle group min-h-9.5 min-w-9.5 flex justify-center items-center text-gray-400 hover:text-blue-600 p-2 text-sm rounded-lg focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none">
                                <span class="group-hover:hidden text-xs">•••</span>
                                <svg
                                    class="group-hover:block hidden shrink-0 size-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="m6 17 5-5-5-5"></path>
                                    <path d="m13 17 5-5-5-5"></path>
                                </svg>
                                <span
                                    class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded-md shadow-2xs"
                                    role="tooltip">
                                    Next 4 pages
                                </span>
                            </button>
                        </div>
                        <button
                            type="button"
                            class="min-h-9.5 min-w-9.5 flex justify-center items-center text-gray-800 hover:bg-gray-100 py-2 px-3 text-sm rounded-lg focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none">
                            8
                        </button>
                    </div>
                    <button
                        type="button"
                        class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex jusify-center items-center gap-x-2 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none"
                        aria-label="Next">
                        <span class="sr-only">Next</span>
                        <svg
                            class="shrink-0 size-3.5"
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </button>
                </nav>
                <!-- End Pagination -->
            </div>
            <!-- End Pagination Wrapper -->

            <!-- Pagination Wrapper -->

            <!-- End Pagination Wrapper -->
        </main>
    </div>
</section>
@endsection