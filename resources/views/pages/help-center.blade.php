@extends('layouts.app')

@section('title', 'Help Center')

@section('content')
<section class="help-center-section product-sans-regular">
    <!-- Hero Section -->
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <!-- Overlay tối -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Nội dung chính -->
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold font-sans">Help Center</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400 font-sans">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400 font-sans">Help Center</span>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="max-w-4xl mx-auto py-10">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold mb-4">How can we help you?</h2>
            <div class="relative">
                <input type="text" placeholder="Search for help..."
                    class="w-full px-6 py-3 border rounded-lg focus:ring-2 focus:ring-orange-400 focus:outline-none">
                <button class="absolute right-3 top-1/2 transform ">
                    <svg class="w-6 h-6 text-gray-400 " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Help Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Getting Started -->
            <div class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-orange-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Getting Started</h3>
                <p class="text-gray-600 mb-4">Learn about our services and how to begin using our platform.</p>
                <a href="#" class="text-orange-500 hover:text-orange-600">Learn more →</a>
            </div>

            <!-- Product Design -->
            <div class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-orange-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Product Design</h3>
                <p class="text-gray-600 mb-4">Guidelines for creating and uploading your product designs.</p>
                <a href="#" class="text-orange-500 hover:text-orange-600">Learn more →</a>
            </div>

            <!-- Order Management -->
            <div class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-orange-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Order Management</h3>
                <p class="text-gray-600 mb-4">Track and manage your orders efficiently.</p>
                <a href="#" class="text-orange-500 hover:text-orange-600">Learn more →</a>
            </div>

            <!-- Payment & Billing -->
            <div class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-orange-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Payment & Billing</h3>
                <p class="text-gray-600 mb-4">Information about payments, pricing, and billing processes.</p>
                <a href="#" class="text-orange-500 hover:text-orange-600">Learn more →</a>
            </div>

            <!-- Returns & Support -->
            <div class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-orange-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Returns & Support</h3>
                <p class="text-gray-600 mb-4">Learn about our return policy and how to get support.</p>
                <a href="#" class="text-orange-500 hover:text-orange-600">Learn more →</a>
            </div>

            <!-- FAQ -->
            <div class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">
                <div class="text-orange-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">FAQ</h3>
                <p class="text-gray-600 mb-4">Find answers to commonly asked questions.</p>
                <a href="#" class="text-orange-500 hover:text-orange-600">Learn more →</a>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="mt-12 text-center">
            <h2 class="text-2xl font-bold mb-4">Still need help?</h2>
            <p class="text-gray-600 mb-6">Our support team is always ready to assist you</p>
            <a href="/contact" class="bg-orange-500 text-white px-8 py-3 rounded-lg hover:bg-orange-600 transition-colors">
                Contact Support
            </a>
        </div>
    </div>
</section>
@endsection