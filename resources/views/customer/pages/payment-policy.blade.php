@extends('layouts.app')

@section('title', 'Payment Policy')

@section('content')
<section class="about-us-section product-sans-regular">
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <!-- Overlay tối -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Nội dung chính -->
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold">Payment Policy</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400">Payment Policy</span>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto py-10 mt-10">
        <h1 class="text-3xl mb-4 font-semibold product-sans-bold">Policy</h1>

        <p class="mt-2">
            We require customers to pay in advance 100% of the order amount
            including shipping costs if any.
        </p>

        <h2 style="color: #005366" class="text-xl font-semibold mt-6">
            Payment methods
        </h2>
        <p class="mt-2">
            We accept payment from customers through a secure payment gateway
            Paypal, COD, banking...
        </p>
        <p class="mt-2">
            For more information, please visit the Paypal website.
        </p>

        <h2 style="color: #005366" class="text-xl font-semibold mt-6">
            Contact Us
        </h2>
        <p class="mt-2">
            You can contact us via Mail:
            <a
                style="color: #f7961d"
                href="mailto:sales@lenful.com"
                class="text-blue-500">admin@hmfulfill.com</a>
        </p>
    </div>
</section>
@endsection