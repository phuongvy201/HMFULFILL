@extends('layouts.app')

@section('title', 'Products')

@section('content')
<section class="products product-sans-regular">
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
            class="col-span-12 md:col-span-4 lg:col-span-3 hidden md:block">
            <div class="w-64 mb-10">
                <div style="background: linear-gradient(to right, #f7961d, #f7961d)" class="relative text-white text-xl rounded-t-lg py-2 px-4">
                    <span class="">CATEGORIES</span>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-white"></div>
                </div>
                <div style="border: 1px solid #f7961d" class="border rounded-lg mt-2 p-4">
                    <ul class="space-y-2">
                        <li class="text-gray-500"><a href="/products">All</a></li>
                        @foreach ($categories as $category)
                        <li class="text-gray-500">
                            <a href="/products/{{ $category->slug }}">{{ $category->name }}</a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>


            <div class="w-64">
                <div style="background: linear-gradient(to right, #f7961d, #f7961d)"
                    class="relative text-white text-xl rounded-t-lg py-2 px-4">
                    <span>LOCATION</span>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-white"></div>
                </div>
                <div style="border: 1px solid #f7961d" class="border rounded-lg mt-2 p-4">
                    <ul class="space-y-2">
                        <!-- Lọc tất cả sản phẩm -->
                        <li class="text-gray-500">
                            <a href="/products">All</a>
                        </li>
                        <!-- Lọc theo quốc gia -->
                        <li class="text-gray-500">
                            <a href="/products?country=US">United States</a>
                        </li>
                        <li class="text-gray-500">
                            <a href="/products?country=UK">United Kingdom</a>
                        </li>
                        <li class="text-gray-500">
                            <a href="/products?country=VN">Vietnam</a>
                        </li>
                    </ul>
                </div>
            </div>

        </aside>

        <main class="col-span-12 md:col-span-8 lg:col-span-9">
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-4">


                <!-- Filter options cho mobile -->
                <div class="flex gap-4 w-full md:w-auto md:hidden">
                    <select
                        id="categorySelect"
                        onchange="handleCategoryChange(this.value)"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option selected disabled>Select Category</option>
                        <option value="all"> <a href="/products">All</a></option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" {{ request()->segment(2) == $category->slug ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>

                    <select
                        id="locationSelect"
                        onchange="handleLocationChange(this.value)"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option selected disabled>Select Location</option>
                        <option value="all" {{ !request('country') ? 'selected' : '' }}>All Locations</option>
                        <option value="US" {{ request('country') == 'US' ? 'selected' : '' }}>United States</option>
                        <option value="UK" {{ request('country') == 'UK' ? 'selected' : '' }}>United Kingdom</option>
                        <option value="VN" {{ request('country') == 'VN' ? 'selected' : '' }}>Vietnam</option>
                    </select>
                </div>

                <!-- Sort option -->
                <!-- <form class="w-full md:w-auto">
                    <select
                        id="countries"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected>Choose a country</option>
                        <option value="US">United States</option>
                        <option value="UK">United Kingdom</option>
                        <option value="VN">Vietnam</option>
                    </select>
                </form> -->
            </div>
            <div class="grid grid-cols-1">
                <form class="w-full mx-auto my-4 lg:my-0" action="{{ request()->url() }}" method="GET">
                    <label
                        for="default-search"
                        class="text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
                    <div class="relative w-full">
                        <input
                            type="search"
                            id="default-search"
                            name="search"
                            value="{{ request('search') }}"
                            class="block w-full pr-12 p-3 input-search text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Search products ..."
                            required />

                        <!-- Giữ lại các tham số lọc hiện tại -->
                        @if(request('country'))
                        <input type="hidden" name="country" value="{{ request('country') }}">
                        @endif

                        <button
                            type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-600 bg-gray-200 hover:text-white hover:bg-orange-500 font-medium rounded-lg text-sm px-3 py-2">
                            <svg
                                class="w-4 h-4  dark:text-gray-400"
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
                        </button>
                    </div>
                </form>

                <div
                    class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-2">
                    @foreach ($products as $product)
                    <div style="border: 1px solid rgb(222, 222, 222)" class="p-4 rounded-lg shadow-md overflow-hidden relative">
                        <a class="overflow-hidden" href="/product/{{ $product->slug }}">
                            <img
                                src="{{ asset($product->main_image->image_url) }}"
                                alt="{{ $product->name }}"
                                class="w-full h-48 object-cover transition-transform duration-300 transform hover:scale-105" />

                            <h3
                                style="color: #005366"
                                class="product-item mt-2 font-bold text-sm">
                                {{ $product->name }}
                            </h3>
                            <p>From: <span class="text-orange-500">${{ $product->base_price }}</span> </p>
                        </a>

                        <span class="mt-2 text-gray-500 text-xs">
                            Fulfillment:
                            @foreach ($product->fulfillment_locations as $location)
                            {{ $location->country_code }}@if (!$loop->last), @endif
                            @endforeach
                        </span>
                    </div>
                    @endforeach
                </div>

            </div>

            <!-- Pagination Wrapper -->
            <div class="mt-10 grid justify-center sm:flex sm:justify-end sm:items-center gap-1">
                <nav class="flex items-center gap-x-1" aria-label="Pagination">
                    <!-- Previous Page -->
                    @if ($products->onFirstPage())
                    <button disabled class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg opacity-50 pointer-events-none">
                        <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m15 18-6-6 6-6"></path>
                        </svg>
                        <span class="sr-only">Previous</span>
                    </button>
                    @else
                    <a href="{{ $products->previousPageUrl() }}" class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg hover:bg-gray-100">
                        <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m15 18-6-6 6-6"></path>
                        </svg>
                        <span class="sr-only">Previous</span>
                    </a>
                    @endif

                    <!-- Page Numbers -->
                    <div class="flex items-center gap-x-1">
                        @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                        @if ($page == $products->currentPage())
                        <button style="color: #f7961d" class="min-h-9.5 min-w-9.5 flex justify-center items-center bg-gray-200 py-2 px-3 text-sm rounded-lg" aria-current="page">
                            {{ $page }}
                        </button>
                        @else
                        <a href="{{ $url }}" class="min-h-9.5 min-w-9.5 flex justify-center items-center text-gray-800 hover:bg-gray-100 py-2 px-3 text-sm rounded-lg">
                            {{ $page }}
                        </a>
                        @endif
                        @endforeach
                    </div>

                    <!-- Next Page -->
                    @if ($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}" class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg text-gray-800 hover:bg-gray-100">
                        <span class="sr-only">Next</span>
                        <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </a>
                    @else
                    <button disabled class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg opacity-50 pointer-events-none">
                        <span class="sr-only">Next</span>
                        <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </button>
                    @endif
                </nav>
            </div>

            <!-- End Pagination Wrapper -->

            <!-- Pagination Wrapper -->

            <!-- End Pagination Wrapper -->
        </main>
    </div>
</section>

<script>
    function handleCategoryChange(value) {
        if (value === 'all') {
            window.location.href = '/products';
        } else {
            window.location.href = `/products/${value}`;
        }
    }

    function handleLocationChange(value) {
        const currentUrl = new URL(window.location.href);
        if (value === 'all') {
            // Xóa parameter country nếu có
            currentUrl.searchParams.delete('country');
        } else {
            currentUrl.searchParams.set('country', value);
        }
        window.location.href = currentUrl.toString();
    }
</script>
@endsection