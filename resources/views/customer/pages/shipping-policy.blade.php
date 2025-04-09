@extends('layouts.app')

@section('title', 'Shipping Policy')

@section('content')
<section class="about-us-section product-sans-regular">
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <!-- Overlay tối -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Nội dung chính -->
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold">Shipping Policy</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400">Shipping Policy</span>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto py-10 mt-10">
        <h1 class="text-3xl mb-4 font-semibold product-sans-bold">
            Shipping Policy
        </h1>

        <h2 style="color: #005366" class="text-xl font-semibold mt-6">
            Processing Time & Price Shipping
        </h2>
        <p class="mt-2">
            Shipping costs vary depending on the shipping destination. You can get
            a calculation of your exact shipping charges by adding items to your
            cart, proceeding to checkout, and entering your mailing address. The
            shipping cost will automatically update and display in your total
            costs.
        </p>

        <h2 style="color: #005366" class="text-xl font-semibold mt-6">
            Shipping & Handling
        </h2>
        <p class="mt-2">
            We process orders on business days which are Monday through Friday.
        </p>
        <ul class="list-disc ml-6 mt-2">
            <li>
                Processing Time:
                <span style="color: #f7961d">2 – 7 days*</span>
            </li>
            <li>
                Shipping Rates:
                <ul class="list-inside">
                    <li>
                        Standard Shipping:
                        <span style="color: #f7961d">First item: $4.99</span>. Per
                        additional item:
                        <span style="color: #f7961d">$2.99</span>
                    </li>
                    <li>
                        Express Shipping:
                        <span style="color: #f7961d">First item: $9.99</span>. Per
                        additional item:
                        <span style="color: #f7961d">$6.99</span>
                    </li>
                </ul>
            </li>
            <li>
                Estimated Delivery:
                <span style="color: #f7961d">12 – 20 days*</span>
                (excluding Sundays, may vary on holidays)
            </li>
            <li>
                Express Shipping:
                <span style="color: #f7961d">7-10 business days*</span>
            </li>
        </ul>

        <h2 style="color: #005366" class="text-xl font-semibold mt-6">
            About Late Deliveries
        </h2>
        <p class="mt-2">
            Our shipping partners do their best to ensure on-time delivery.
            However, occasional delays may occur due to:
        </p>
        <ul class="list-disc ml-6 mt-2">
            <li>Incorrect address</li>
            <li>Missing apartment, building, or unit number</li>
            <li>Severe weather conditions</li>
            <li>International customs procedures</li>
        </ul>

        <h2 style="color: #005366" class="text-xl font-semibold mt-6">
            Order Tracking
        </h2>
        <p class="mt-2">
            You will receive a confirmation email with a tracking link. Please
            allow 2-4 days for the carrier to scan your package into their system.
        </p>

        <h2 style="color: #005366" class="text-xl font-semibold mt-6">FAQ</h2>
        <div class="mt-4">
            <h3 class="font-semibold">Why haven't I received all my items?</h3>
            <p class="mt-1">
                Some items may be shipped separately for faster delivery. If you
                receive one item first, the others are on the way.
            </p>
        </div>

        <div class="mt-4">
            <h3 class="font-semibold">How do I contact you?</h3>
            <p class="mt-1">
                For inquiries, email us at
                <a
                    style="color: #f7961d"
                    href="mailto:sales@lenful.com"
                    class="text-blue-500">admin@hmfulfill.com</a>
            </p>
        </div>

        <div class="mt-4">
            <h3 class="font-semibold">
                Do you ship to PO Boxes or Military APO/FPO addresses?
            </h3>
            <p class="mt-1">
                Yes, we can ship to PO Boxes and Military APO/FPO addresses. APO
                shipments may take up to 45 additional days.
            </p>
        </div>
    </div>
</section>
@endsection