@extends('layouts.admin')

@section('title', 'Submit Order Detail')

@section('content-admin')

<main>
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: `Order Details`}">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Order Details</h2>
                <nav>
                    <ol class="flex items-center gap-1.5">
                        <li>
                            <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="index.html">
                                Home
                                <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </a>
                        </li>
                        <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Order Details</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Breadcrumb End -->

        <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">
                        Order #{{ $order['order']['id'] }}
                    </h4>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                External ID
                            </p>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $order['order']['external_id'] }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                Status
                            </p>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $order['order']['status'] }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                Created At
                            </p>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ \Carbon\Carbon::parse($order['order']['created_at'])->format('Y-m-d H:i:s') }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                                Shipping Address
                            </p>
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $order['order']['shipping_address']['firstName'] ?? '' }}
                                {{ $order['order']['shipping_address']['lastName'] ?? '' }},
                                {{ $order['order']['shipping_address']['address1'] ?? '' }},
                                {{ $order['order']['shipping_address']['city'] ?? '' }},
                                {{ $order['order']['shipping_address']['country'] ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>

                <button @click="isProfileInfoModal = true" class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto">
                    <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.0911 2.78206C14.2125 1.90338 12.7878 1.90338 11.9092 2.78206L4.57524 10.116C4.26682 10.4244 4.0547 10.8158 3.96468 11.2426L3.31231 14.3352C3.25997 14.5833 3.33653 14.841 3.51583 15.0203C3.69512 15.1996 3.95286 15.2761 4.20096 15.2238L7.29355 14.5714C7.72031 14.4814 8.11172 14.2693 8.42013 13.9609L15.7541 6.62695C16.6327 5.74827 16.6327 4.32365 15.7541 3.44497L15.0911 2.78206ZM12.9698 3.84272C13.2627 3.54982 13.7376 3.54982 14.0305 3.84272L14.6934 4.50563C14.9863 4.79852 14.9863 5.2734 14.6934 5.56629L14.044 6.21573L12.3204 4.49215L12.9698 3.84272ZM11.2597 5.55281L5.6359 11.1766C5.53309 11.2794 5.46238 11.4099 5.43238 11.5522L5.01758 13.5185L6.98394 13.1037C7.1262 13.0737 7.25666 13.003 7.35947 12.9002L12.9833 7.27639L11.2597 5.55281Z" fill=""></path>
                    </svg>
                    Edit
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                    <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                        <form>
                            <div class="-mx-2.5 flex flex-wrap gap-y-5">
                                <div class="w-full px-2.5">
                                    <h4 class="pb-4 text-base font-medium text-gray-800 border-b border-gray-200 dark:border-gray-800 dark:text-white/90">
                                        Shipping Address
                                    </h4>
                                </div>

                                <div class="w-full px-2.5 xl:w-1/2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        First Name
                                    </label>
                                    <input type="text" placeholder="Enter first name" value="{{ $order['order']['shipping_address']['firstName'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>

                                <div class="w-full px-2.5 xl:w-1/2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Last Name
                                    </label>
                                    <input type="text" placeholder="Enter last name" value="{{ $order['order']['shipping_address']['lastName'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>

                                <div class="w-full px-2.5">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Company
                                    </label>
                                    <input type="text" placeholder="Enter company" value="{{ $order['order']['shipping_address']['company'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>

                                <div class="w-full px-2.5 xl:w-1/2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Address
                                    </label>
                                    <input type="text" placeholder="Enter address" value="{{ $order['order']['shipping_address']['address1'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>

                                <div class="w-full px-2.5 xl:w-1/2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Apt, suite, etc.
                                    </label>
                                    <input type="text" placeholder="Enter apt, suite, etc." value="{{ $order['order']['shipping_address']['address2'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>
                                <div class="w-full px-2.5">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        City
                                    </label>
                                    <input type="text" placeholder="Enter city" value="{{ $order['order']['shipping_address']['city'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>
                                <div class="w-full px-2.5 xl:w-1/3">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Country
                                    </label>
                                    <input type="text" placeholder="Enter country" value="{{ $order['order']['shipping_address']['country'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>
                                <div class="w-full px-2.5 xl:w-1/3">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        County/State
                                    </label>
                                    <input type="text" placeholder="Enter county/state" value="{{ $order['order']['shipping_address']['province'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>
                                <div class="w-full px-2.5 xl:w-1/3">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">

                                        Postcode/ZIP code
                                    </label>
                                    <input type="text" placeholder="Enter postcode/zip code" value="{{ $order['order']['shipping_address']['postalCode'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>

                                <div class="w-full px-2.5 xl:w-1/2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Phone
                                    </label>
                                    <input type="text" placeholder="Enter phone" value="{{ $order['order']['shipping_address']['phone1'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>

                                <div class="w-full px-2.5 xl:w-1/2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Phone
                                    </label>
                                    <input type="text" placeholder="Enter phone" value="{{ $order['order']['shipping_address']['phone2'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>
                                <div class="w-full px-2.5 xl:w-1/2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Alternative phone
                                    </label>
                                    <input type="text" placeholder="Enter alternative phone" value="{{ $order['order']['shipping_address']['phone2'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                        <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                            <form>
                                <div class="-mx-2.5 flex flex-wrap gap-y-5">
                                    <div class="w-full px-2.5">
                                        <h4 class="pb-4 text-base font-medium text-gray-800 border-b border-gray-200 dark:border-gray-800 dark:text-white/90">
                                            Billing Address
                                        </h4>
                                    </div>

                                    <div class="w-full px-2.5 xl:w-1/2">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            First Name
                                        </label>
                                        <input type="text" placeholder="Enter first name" value="{{ $order['order']['billing_address']['firstName'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>

                                    <div class="w-full px-2.5 xl:w-1/2">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Last Name
                                        </label>
                                        <input type="text" placeholder="Enter last name" value="{{ $order['order']['billing_address']['lastName'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>

                                    <div class="w-full px-2.5">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Company
                                        </label>
                                        <input type="text" placeholder="Enter company" value="{{ $order['order']['billing_address']['company'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>

                                    <div class="w-full px-2.5 xl:w-1/2">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Address
                                        </label>
                                        <input type="text" placeholder="Enter address" value="{{ $order['order']['billing_address']['address1'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>

                                    <div class="w-full px-2.5 xl:w-1/2">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Apt, suite, etc.
                                        </label>
                                        <input type="text" placeholder="Enter apt, suite, etc." value="{{ $order['order']['billing_address']['address2'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>
                                    <div class="w-full px-2.5">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            City
                                        </label>
                                        <input type="text" placeholder="Enter city" value="{{ $order['order']['billing_address']['city'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>
                                    <div class="w-full px-2.5 xl:w-1/3">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Country
                                        </label>
                                        <input type="text" placeholder="Enter country" value="{{ $order['order']['billing_address']['country'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>
                                    <div class="w-full px-2.5 xl:w-1/3">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            County/State
                                        </label>
                                        <input type="text" placeholder="Enter county/state" value="{{ $order['order']['billing_address']['province'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>
                                    <div class="w-full px-2.5 xl:w-1/3">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">

                                            Postcode/ZIP code
                                        </label>
                                        <input type="text" placeholder="Enter postcode/zip code" value="{{ $order['order']['billing_address']['postalCode'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>

                                    <div class="w-full px-2.5 xl:w-1/2">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Phone
                                        </label>
                                        <input type="text" placeholder="Enter phone" value="{{ $order['order']['billing_address']['phone1'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>

                                    <div class="w-full px-2.5 xl:w-1/2">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Phone
                                        </label>
                                        <input type="text" placeholder="Enter phone" value="{{ $order['order']['billing_address']['phone2'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>
                                    <div class="w-full px-2.5 xl:w-1/2">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Alternative phone
                                        </label>
                                        <input type="text" placeholder="Enter alternative phone" value="{{ $order['order']['billing_address']['phone2'] ?? '' }}" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>


                                </div>
                            </form>
                        </div>
                    </div>
                </div>


            </div>
        </div>
        <div class=" my-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    Order Items
                </h3>
            </div>
            <div class="border-t border-gray-100 p-5 dark:border-gray-800 sm:p-6">
                <!-- ====== Table Six Start -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="max-w-full overflow-x-auto custom-scrollbar">
                        <table class="w-full min-w-[1102px]">

                            <tbody>
                                @foreach ($order['order']['items'] as $item)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    {{-- Thông tin sản phẩm --}}
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex-1">
                                            <span class="block font-medium text-xl text-gray-800 text-theme-sm dark:text-white/90">
                                                {{ $item['title'] }}
                                            </span>

                                            <span class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                SKU: {{ $item['pn'] }}
                                            </span>

                                            {{-- Options --}}
                                            @if(isset($item['options']) && count($item['options']) > 0)
                                            <div class="mt-1">
                                                @foreach($item['options'] as $option)
                                                <span class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    {{ $option['type'] }}: {{ $option['value'] }}
                                                </span>
                                                @endforeach
                                            </div>
                                            @endif

                                            <span class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                Price: £{{ $item['price'] }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Mockup và Design --}}
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex gap-4">
                                            {{-- Mockup Image --}}
                                            @if(isset($item['mockups']) && count($item['mockups']) > 0)
                                            <div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Mockup:</p>
                                                @foreach($item['mockups'] as $mockup)
                                                <div class="mb-2">
                                                    <img src="{{ $mockup['src'] }}" alt="{{ $mockup['title'] }}" class="w-16 h-16 object-cover rounded">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $mockup['title'] }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif

                                            {{-- Designs --}}
                                            @if(isset($item['designs']) && count($item['designs']) > 0)
                                            <div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">Designs:</p>
                                                @foreach($item['designs'] as $design)
                                                <div class="mb-2">
                                                    <img src="{{ $design['src'] }}" alt="{{ $design['title'] }}" class="w-16 h-16 object-cover rounded">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $design['title'] }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Số lượng --}}
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                            Qty: {{ $item['quantity'] }}
                                        </p>
                                    </td>

                                    {{-- Giá --}}
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                            £{{ $item['price'] * $item['quantity'] }}
                                        </p>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- ====== Table Six End -->
            </div>
        </div>
    </div>
</main>


@endsection