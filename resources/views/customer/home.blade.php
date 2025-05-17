@extends('layouts.app')

@section('title', 'Home')

@section('content')
<section class="hero">
    <div
        class="px-3 container max-w-screen-xl mx-auto pb-6 pt-10 lg:pt-0 flex flex-col lg:flex-row items-center">
        <!-- Phần nội dung bên trái -->
        <div class="lg:w-1/2 text-center lg:text-left">
            <h1 class="text-4xl font-bold text-gray-900">
                Start Selling Your Unique Print-On-Demand Products
            </h1>
            <p class="text-gray-600 mt-4 product-sans-regular">
                Our advanced printing and fulfillment system helps you create and
                sell POD products, delivering them directly to customers across the
                globe.
            </p>

            <button
                class="mt-6 mx-auto lg:mx-0 flex items-center outline-2 outline-offset-4 outline-blue-500 px-4 py-2 rounded-md shadow-lg product-sans-regular">
                Get started
                <svg
                    class="size-4 mx-2 animate-move"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M12.2929 4.29289C12.6834 3.90237 13.3166 3.90237 13.7071 4.29289L20.7071 11.2929C21.0976 11.6834 21.0976 12.3166 20.7071 12.7071L13.7071 19.7071C13.3166 20.0976 12.6834 20.0976 12.2929 19.7071C11.9024 19.3166 11.9024 18.6834 12.2929 18.2929L17.5858 13H4C3.44772 13 3 12.5523 3 12C3 11.4477 3.44772 11 4 11H17.5858L12.2929 5.70711C11.9024 5.31658 11.9024 4.68342 12.2929 4.29289Z"
                        fill="#000000" />
                </svg>
            </button>
        </div>

        <!-- Phần hình ảnh bên phải -->
        <div
            class="lg:w-1/2 mt-10 lg:mt-0 flex flex-wrap justify-center lg:justify-end space-x-4">
            <div class="relative h-[500px] overflow-hidden">
                <div class="flex flex-col animate-scroll">
                    <img
                        src="{{ asset('assets/images/webdecor.png') }}"
                        alt="Scrolling Image"
                        class="w-full" />
                    <img
                        src="{{ asset('assets/images/webdecor.png') }}"
                        alt="Scrolling Image"
                        class="w-full" />
                    <!-- Nhân đôi ảnh để tạo hiệu ứng lặp -->
                </div>
            </div>
        </div>
    </div>

    <!-- Nội dung hero section -->
</section>

<section class="features">
    <!-- Nội dung features section -->
    <div class="container max-w-screen-xl mx-auto px-3 py-10">
        <div class="flex-col py-10">
            <h1 class="text-4xl font-bold text-gray-900 text-center">
                The ultimate commerce platform for print-on-demand
            </h1>
            <p class="text-gray-600 mt-4 product-sans-regular text-center">
                Sell custom products online and in person with access to both global
                and local markets. Flexibly offer direct and wholesale printing
                services to meet diverse customer needs. Our system helps you manage
                orders effortlessly, optimizing the process from design to delivery.
                Seamlessly sell across all devices, expanding your brand and
                boosting sales effectively.
            </p>
        </div>
        <div
            id="default-carousel"
            class="relative w-full"
            data-carousel="slide">
            <!-- Carousel wrapper -->
            <div class="relative h-56 overflow-hidden rounded-lg md:h-96">
                <!-- Item 1 -->
                <div class="hidden duration-700 ease-in-out" data-carousel-item>
                    <img
                        src="https://s3.amazonaws.com/image.bluprinter/products/1742283714-il_794xN.6675634248_dti6.jpg"
                        class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                        alt="..." />
                </div>
                <!-- Item 2 -->
                <div class="hidden duration-700 ease-in-out" data-carousel-item>
                    <img
                        src="https://s3.amazonaws.com/image.bluprinter/products/1741694403-image_0.jpg"
                        class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                        alt="..." />
                </div>
                <!-- Item 3 -->
                <div class="hidden duration-700 ease-in-out" data-carousel-item>
                    <img
                        src="https://s3.amazonaws.com/image.bluprinter/products/1740650951-b2984fe7b54e47929c8a09ef5ffb8450~tplv-omjb5zjo8w-origin-jpeg.jpeg%3Fdr%3D10493%26from%3D1432613627%26idc%3Duseast5%26ps%3D933b5bde%26shcp%3D9794469a%26shp%3D5563f2fb%26t%3D555f072d"
                        class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                        alt="..." />
                </div>
                <!-- Item 4 -->
                <div class="hidden duration-700 ease-in-out" data-carousel-item>
                    <img
                        src="https://s3.amazonaws.com/image.bluprinter/products/1740650919-00ea4bb3caec4bcc8d04d50ef8ca5a43~tplv-omjb5zjo8w-origin-jpeg.jpeg%3Fdr%3D10493%26from%3D1432613627%26idc%3Duseast5%26ps%3D933b5bde%26shcp%3D9794469a%26shp%3D5563f2fb%26t%3D555f072d"
                        class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                        alt="..." />
                </div>
                <!-- Item 5 -->
                <div class="hidden duration-700 ease-in-out" data-carousel-item>
                    <img
                        src="https://i.etsystatic.com/37049241/r/il/16847a/6545116858/il_794xN.6545116858_9i6p.jpg"
                        class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                        alt="..." />
                </div>
            </div>
            <!-- Slider indicators -->
            <div
                class="absolute z-30 flex -translate-x-1/2 bottom-5 left-1/2 space-x-3 rtl:space-x-reverse">
                <button
                    type="button"
                    class="w-3 h-3 rounded-full"
                    aria-current="true"
                    aria-label="Slide 1"
                    data-carousel-slide-to="0"></button>
                <button
                    type="button"
                    class="w-3 h-3 rounded-full"
                    aria-current="false"
                    aria-label="Slide 2"
                    data-carousel-slide-to="1"></button>
                <button
                    type="button"
                    class="w-3 h-3 rounded-full"
                    aria-current="false"
                    aria-label="Slide 3"
                    data-carousel-slide-to="2"></button>
                <button
                    type="button"
                    class="w-3 h-3 rounded-full"
                    aria-current="false"
                    aria-label="Slide 4"
                    data-carousel-slide-to="3"></button>
                <button
                    type="button"
                    class="w-3 h-3 rounded-full"
                    aria-current="false"
                    aria-label="Slide 5"
                    data-carousel-slide-to="4"></button>
            </div>
            <!-- Slider controls -->
            <button
                type="button"
                class="absolute top-0 start-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                data-carousel-prev>
                <span
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                    <svg
                        class="w-4 h-4 text-white dark:text-gray-800 rtl:rotate-180"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 6 10">
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 1 1 5l4 4" />
                    </svg>
                    <span class="sr-only">Previous</span>
                </span>
            </button>
            <button
                type="button"
                class="absolute top-0 end-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                data-carousel-next>
                <span
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                    <svg
                        class="w-4 h-4 text-white dark:text-gray-800 rtl:rotate-180"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 6 10">
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                    <span class="sr-only">Next</span>
                </span>
            </button>
        </div>
        <div class="flex flex-col lg:flex-row py-10 justify-center">
            <!-- Item 1 -->
            <div class="flex-1 mb-8 lg:mb-0">
                <div class="flex flex-row lg:flex-col items-center">
                    <img
                        class="h-auto w-1/4 rounded-lg"
                        src="{{ asset('assets/images/boxes.png') }}"
                        alt="image description" />
                    <figcaption
                        class="ml-4 lg:ml-0 lg:mt-4 text-sm text-neutral-800 dark:text-gray-400 lg:text-center product-sans-regular">
                        Since 2013, we have successfully delivered more than
                        <b>102M+</b> products to customers around the world, ensuring
                        quality and reliability in every order.
                    </figcaption>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="flex-1 mb-8 lg:mb-0">
                <div class="flex flex-row-reverse lg:flex-col items-center">
                    <img
                        class="h-auto w-1/4 rounded-lg"
                        src="{{ asset('assets/images/long-sleeves.png') }}"
                        alt="image description" />
                    <figcaption
                        class="mr-4 lg:mr-0 lg:mt-4 text-sm text-neutral-800 dark:text-gray-400 lg:text-center product-sans-regular">
                        Our commitment to quality ensures that <b>99%</b> of customers
                        are satisfied with the products they receive.
                    </figcaption>
                </div>
            </div>

            <!-- Item 3 -->
            <div class="flex-1 mb-8 lg:mb-0">
                <div class="flex flex-row lg:flex-col items-center">
                    <img
                        class="h-auto w-1/4 rounded-lg"
                        src="{{ asset('assets/images/printer.png') }}"
                        alt="image description" />
                    <figcaption
                        class="ml-4 lg:ml-0 lg:mt-4 text-sm text-neutral-800 dark:text-gray-400 lg:text-center product-sans-regular">
                        Every month, we successfully fulfill over <b>1M+</b> orders,
                        delivering excellence at scale.
                    </figcaption>
                </div>
            </div>
        </div>
        <div class="flex-col pb-10">
            <h1 class="text-4xl font-bold text-neutral-800 text-center">
                The perfect POD solution for your business
            </h1>
            <p class="text-gray-600 mt-4 product-sans-regular text-center">
                Turn your creative ideas into real products without worrying about
                production or shipping. With the POD platform, you can easily
                customize over 100 products such as T-shirts, mugs, hoodies,
                posters, and more. Orders are processed automatically, from printing
                to delivery to customers worldwide. All you need to do is focus on
                design and marketing—we'll take care of the rest!
            </p>
        </div>
        <div
            class="w-full flex justify-start lg:justify-center overflow-x-auto scrollbar-hide">
            <!-- Container cho scroll trên mobile/tablet -->
            @foreach ($categories as $category)
            <a href="/products/{{ $category->slug }}"
                type="button"
               
                class="home-category-button font-medium rounded-full text-sm px-5 md:py-2.5 me-2 mb-2 mx-2">
                {{ $category->name }}
            </a>
            @endforeach

        </div>
        <div class="w-full">
            <!-- Container cho scroll trên mobile/tablet và grid trên desktop -->

            <div
                class="flex lg:grid lg:grid-cols-4 lg:gap-4 overflow-x-auto lg:overflow-x-hidden space-x-4 lg:space-x-0 pb-4 scrollbar-hide">
                <!-- Item 1 -->
                @foreach ($products as $product)
                <div class="flex-none w-[280px] lg:w-auto p-2">
                    <div
                        class="w-full bg-white border border-gray-200 shadow-md rounded-lg">
                        <a href="/product/{{ $product->slug }}">
                            <img
                                class="p-5 rounded-t-lg"
                                src="{{ asset($product->main_image->image_url) }}"
                                alt="product image" />
                        </a>
                        <div class="px-5 pb-6">
                                <a href="/product/{{ $product->slug }}">
                                <h5
                                    class="product-sans-regular tracking-tight text-gray-900 dark:text-white">
                                    {{ $product->name }}
                                </h5>
                            </a>
                            <div class="flex items-center justify-between mt-2">
                                <span
                                    style="color: #005366"
                                    class="product-sans-regular font-bold text-gray-900 dark:text-white">
                                    <small class="font-thin">From: </small>{{ $product->base_price }} $
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
        <div class="w-full text-center">
            <a
                href="/products"
            
                class="link-see-all py-2 px-5 text-base product-sans-regular text-center rounded-lg">
                See All
            </a>
        </div>
        <div class="flex-col py-10">
            <h1 class="text-4xl font-bold text-gray-900 text-center">
                Seamlessly connect HMFULFILL with your favorite platform or
                marketplace.
            </h1>
            <div class="grid grid-cols-4 gap-4 py-10 bg-white">
                <div
                    class="flex items-center justify-center border rounded-lg p-2 shadow-md">
                    <img
                        src="https://image.printdoors.com/statics/img/shopify.67e3df8f.png"
                        alt="Shopify"
                        class="h-auto lg:h-24" />
                </div>
                <div
                    class="flex items-center justify-center border rounded-lg p-2 shadow-md">
                    <img
                        src="https://image.printdoors.com/statics/img/etsy.431beca9.png"
                        alt="Etsy"
                        class="h-auto lg:h-24" />
                </div>
                <div
                    class="flex items-center justify-center border rounded-lg p-2 shadow-md">
                    <img
                        src="https://image.printdoors.com/statics/img/wooCommerce.74a1ee4a.png"
                        alt="WooCommerce"
                        class="h-auto lg:h-24" />
                </div>
                <div
                    class="flex items-center justify-center border rounded-lg p-2 shadow-md">
                    <img
                        src="data:image/png;base64,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"
                        alt="Shopplazza"
                        class="h-auto lg:h-24" />
                </div>
            </div>
        </div>
        <div class="flex-col pb-6">
            <h1 class="text-4xl font-bold text-gray-900 text-center">
                What Makes <span style="color: #f7961d">HMFULFILL</span> the
                <span style="color: #005366">Best Choice?</span>
            </h1>
            <p class="text-gray-600 mt-4 product-sans-regular text-center">
                At PrintDoors, we believe that everyone deserves access to a diverse
                range of printing solutions that are affordable yet high-quality. We
                are committed to delivering exceptional printing services that meet
                personal, business, and creative needs, allowing customers to
                explore the world of printing without worrying about cost or
                quality.
            </p>
            <div class="container mx-auto px-4 py-12">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Phần hình ảnh bên trái -->
                    <div class="lg:w-1/2 flex flex-col items-center justify-center">
                        <img
                            src="data:image/jpeg;base64,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"
                            alt="Team Meeting"
                            class="rounded-lg w-full h-auto shadow-lg" />
                    </div>

                    <!-- Phần features bên phải -->
                    <div class="lg:w-1/2 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Quality -->
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center mb-4">
                                <div
                                    class="w-10 h-10 bg-blue-700 rounded-lg flex items-center justify-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-l font-semibold ml-3 text-blue-700">
                                    Quality
                                </h3>
                            </div>
                            <p class="text-gray-600 product-sans-regular">
                                Quality-first HM FULFILL can ensure that users' products are
                                manufactured to high standards, with attention to detail and
                                quality control measures in place.
                            </p>
                        </div>

                        <!-- Reliability -->
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center mb-4">
                                <div
                                    class="w-10 h-10 bg-blue-700 rounded-lg flex items-center justify-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-l font-semibold ml-3 text-blue-700">
                                    Reliability
                                </h3>
                            </div>
                            <p class="text-gray-600 product-sans-regular">
                                HM FULFILL can ensure that users' orders are produced accurately
                                and shipped on time.
                            </p>
                        </div>

                        <!-- Cost-effectiveness -->
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center mb-4">
                                <div
                                    class="w-10 h-10 bg-blue-700 rounded-lg flex items-center justify-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-l font-semibold ml-3 text-blue-700">
                                    Cost-effectiveness
                                </h3>
                            </div>
                            <p class="text-gray-600 product-sans-regular">
                                HM FULFILL supports users to save costs so that they can
                                maximize profits and reduce financial risks.
                            </p>
                        </div>

                        <!-- Convenience -->
                        <div class="p-6 bg-white rounded-lg shadow-md">
                            <div class="flex items-center mb-4">
                                <div
                                    class="w-10 h-10 bg-blue-700 rounded-lg flex items-center justify-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <h3 class="text-l font-semibold ml-3 text-blue-700">
                                    Convenience
                                </h3>
                            </div>
                            <p class="text-gray-600 product-sans-regular">
                                HM FULFILL aims to be convenient to use so that it can make the
                                experience of creating and selling products more seamless
                                and enjoyable for users.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-col pb-6">
            <div class="container mx-auto">
                <h2 class="text-3xl font-bold text-center mb-8">Blog & Insights</h2>

                <!-- Thêm lớp overflow-x-hidden để ẩn thanh cuộn -->
                <div class="overflow-x-auto md:overflow-visible whitespace-nowrap scrollbar-none">
                    <div class="flex md:grid md:grid-cols-3 gap-6">
                        <!-- Blog Card 1 -->
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-[80%] md:min-w-0">
                            <img src="https://api.bluprinter.com/images/posts/1737360042_main-post-image.png"
                                alt="Blog Image" class="w-full h-48 object-cover" />
                            <div class="p-4">
                                <h3 class="text-lg font-semibold">
                                    <a class="link-blog" href="#">Print-On-Demand Style: 8 Money Making T-Shirt Trends for 2023</a>
                                </h3>
                                <p class="text-gray-600 text-sm mt-2 product-sans-regular">
                                    As a print-on-demand seller, there is a very small window for being one of the first ones to an emerging trend...
                                </p>
                            </div>
                        </div>

                        <!-- Blog Card 2 -->
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-[80%] md:min-w-0">
                            <img src="https://api.bluprinter.com/images/posts/1737360042_main-post-image.png"
                                alt="Blog Image" class="w-full h-48 object-cover" />
                            <div class="p-4">
                                <h3 class="text-lg font-semibold">
                                    <a class="link-blog" href="#">Print-On-Demand Style: 8 Money Making T-Shirt Trends for 2023</a>
                                </h3>
                                <p class="text-gray-600 text-sm mt-2 product-sans-regular">
                                    As a print-on-demand seller, there is a very small window for being one of the first ones to an emerging trend...
                                </p>
                            </div>
                        </div>

                        <!-- Blog Card 3 -->
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden min-w-[80%] md:min-w-0">
                            <img src="https://api.bluprinter.com/images/posts/1737360042_main-post-image.png"
                                alt="Blog Image" class="w-full h-48 object-cover" />
                            <div class="p-4">
                                <h3 class="text-lg font-semibold">
                                    <a class="link-blog" href="#">Print-On-Demand Style: 8 Money Making T-Shirt Trends for 2023</a>
                                </h3>
                                <p class="text-gray-600 text-sm mt-2 product-sans-regular">
                                    As a print-on-demand seller, there is a very small window for being one of the first ones to an emerging trend...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<section class="products">
    <!-- Nội dung products section -->
</section>
@endsection