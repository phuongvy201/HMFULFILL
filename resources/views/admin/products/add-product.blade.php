@extends('layouts.admin')

@section('title', 'Add Product')

@section('content-admin')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <!-- Phần Breadcrumb -->
    <div x-data="{ pageName: `Add Product`}">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2
                class="text-xl font-semibold text-gray-800 dark:text-white/90"
                x-text="pageName"></h2>

            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                            href="index.html">
                            Home
                            <svg
                                class="stroke-current"
                                width="17"
                                height="16"
                                viewBox="0 0 17 16"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                                    stroke=""
                                    stroke-width="1.2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </a>
                    </li>
                    <li
                        class="text-sm text-gray-800 dark:text-white/90"
                        x-text="pageName"></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <!-- Cột trái - Thông tin sản phẩm -->
        <div class="space-y-6">
            <!-- Card thông tin cơ bản -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Product Information
                    </h3>
                </div>
                <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                    <form>
                        <div class="-mx-2.5 flex flex-wrap gap-y-5">
                            <div class="w-full px-2.5">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Product Name
                                </label>
                                <input id="name" name="name" type="text" placeholder="Enter product name" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                            <div class="w-full px-2.5">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Description
                                </label>
                                <textarea id="editor" name="description" placeholder="Enter product description" rows="6" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                            </div>
                            <div class="w-full px-2.5">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Category
                                </label>
                                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                    <select class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                        <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                            Select Category
                                        </option>
                                        <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                            Marketing
                                        </option>
                                        <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                            Template
                                        </option>
                                        <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                            Development
                                        </option>
                                    </select>
                                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </span>
                                </div>

                            </div>
                            <div class="w-full px-2.5">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Template URL
                                    </label>
                                    <div class="relative">
                                        <span class="absolute top-1/2 left-0 inline-flex h-11 -translate-y-1/2 items-center justify-center border-r border-gray-200 py-3 pr-3 pl-3.5 text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                            https://
                                        </span>
                                        <input type="url" placeholder="hmfulfill.com" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[90px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" data-listener-added_317ee790="true">
                                    </div>
                                </div>

                            </div>

                            <div class="w-full px-2.5 xl:w-1/2 ">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Base Price
                                </label>
                                <input type="number" placeholder="Enter product base price" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Status
                                </label>
                                <select class="w-full px-4 py-3 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg appearance-none dark:bg-dark-900 h-11 bg-none shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-500">
                                        -- Status --
                                    </option>
                                    <option value="1" class="text-gray-500">
                                        -- Active --
                                    </option>
                                    <option value="2" class="text-gray-500">
                                        -- Inactive --
                                    </option>
                                </select>
                            </div>


                        </div>
                    </form>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Area Fulfillment
                    </h3>
                </div>
                <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                    <!-- Elements -->
                    <div class="flex flex-col items-start gap-8">
                        <div x-data="{ checkboxToggle: false }">
                            <label for="checkboxLabelOne" class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                                <div class="relative">
                                    <input type="checkbox" id="checkboxLabelOne" class="sr-only" @change="checkboxToggle = !checkboxToggle">
                                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="f hover:border-brand-500 dark:hover:border-brand-500 mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] bg-transparent border-gray-300 dark:border-gray-700">
                                        <span :class="checkboxToggle ? '' : 'opacity-0'" class="opacity-0">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                United States
                            </label>
                        </div>

                        <div x-data="{ checkboxToggle: true }">
                            <label for="checkboxLabelTwo" class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                                <div class="relative">
                                    <input type="checkbox" id="checkboxLabelTwo" class="sr-only" @change="checkboxToggle = !checkboxToggle">
                                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="hover:border-brand-500 dark:hover:border-brand-500 mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] bg-transparent border-gray-300 dark:border-gray-700">
                                        <span :class="checkboxToggle ? '' : 'opacity-0'" class="opacity-0">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                United Kingdom
                            </label>
                        </div>
                        <div x-data="{ checkboxToggle: true }">
                            <label for="checkboxLabelTwo" class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                                <div class="relative">
                                    <input type="checkbox" id="checkboxLabelTwo" class="sr-only" @change="checkboxToggle = !checkboxToggle">
                                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="hover:border-brand-500 dark:hover:border-brand-500 mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] bg-transparent border-gray-300 dark:border-gray-700">
                                        <span :class="checkboxToggle ? '' : 'opacity-0'" class="opacity-0">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                Vietnam
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Product Media
                    </h3>
                </div>
                <div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
                    <form class="dropzone hover:border-brand-500! dark:hover:border-brand-500! rounded-xl border border-dashed! border-gray-300! bg-gray-50 p-7 lg:p-10 dark:border-gray-700! dark:bg-gray-900 dz-clickable" id="demo-upload" action="/upload">
                        <div class="dz-message m-0!">
                            <div class="mb-[22px] flex justify-center">
                                <div class="flex h-[68px] w-[68px] items-center justify-center rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="fill-current" width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5019 3.91699C14.2852 3.91699 14.0899 4.00891 13.953 4.15589L8.57363 9.53186C8.28065 9.82466 8.2805 10.2995 8.5733 10.5925C8.8661 10.8855 9.34097 10.8857 9.63396 10.5929L13.7519 6.47752V18.667C13.7519 19.0812 14.0877 19.417 14.5019 19.417C14.9161 19.417 15.2519 19.0812 15.2519 18.667V6.48234L19.3653 10.5929C19.6583 10.8857 20.1332 10.8855 20.426 10.5925C20.7188 10.2995 20.7186 9.82463 20.4256 9.53184L15.0838 4.19378C14.9463 4.02488 14.7367 3.91699 14.5019 3.91699ZM5.91626 18.667C5.91626 18.2528 5.58047 17.917 5.16626 17.917C4.75205 17.917 4.41626 18.2528 4.41626 18.667V21.8337C4.41626 23.0763 5.42362 24.0837 6.66626 24.0837H22.3339C23.5766 24.0837 24.5839 23.0763 24.5839 21.8337V18.667C24.5839 18.2528 24.2482 17.917 23.8339 17.917C23.4197 17.917 23.0839 18.2528 23.0839 18.667V21.8337C23.0839 22.2479 22.7482 22.5837 22.3339 22.5837H6.66626C6.25205 22.5837 5.91626 22.2479 5.91626 21.8337V18.667Z" fill=""></path>
                                    </svg>
                                </div>
                            </div>

                            <h4 class="text-theme-xl mb-3 font-semibold text-gray-800 dark:text-white/90">
                                Drag &amp; Drop File Here
                            </h4>
                            <span class="mx-auto mb-5 block w-full max-w-[290px] text-sm text-gray-700 dark:text-gray-400">
                                Drag and drop your PNG, JPG, WebP, SVG images here or
                                browse
                            </span>

                            <span class="text-theme-sm text-brand-500 font-medium underline">
                                Browse File
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Product Variants
                    </h3>
                </div>
                <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                    <form>
                        <button id="addVariantBtn" type="button"
                            class="mb-4 inline-flex items-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M10 2C10.5523 2 11 2.44772 11 3V8H16C16.5523 8 17 8.44772 17 9C17 9.55228 16.5523 10 16 10H11V15C11 15.5523 10.5523 16 10 16C9.44772 16 9 15.5523 9 15V10H4C3.44772 10 3 9.55228 3 9C3 8.44772 3.44772 8 4 8H9V3C9 2.44772 9.44772 2 10 2Z"
                                    fill="currentColor" />
                            </svg>
                            Add Variant
                        </button>

                        <!-- Khu vực chứa các variant -->
                        <div id="variantContainer"></div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <div class="my-6 w-full h-screen rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                List of Variant
            </h3>
            <div class="border-t border-gray-100 p-6 dark:border-gray-800">
                <div x-data="{isModalOpen: false}">
                    <button class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600" @click="isModalOpen = !isModalOpen">
                        Open Modal
                    </button>

                    <div x-show="isModalOpen" class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999" style="display: none;">
                        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
                        <div @click.outside="isModalOpen = false" class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
                            <!-- close btn -->
                            <button @click="isModalOpen = false" class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""></path>
                                </svg>
                            </button>

                            <div>
                                <h4 class="font-semibold text-gray-800 mb-7 text-title-sm dark:text-white/90">
                                    Modal Heading
                                </h4>
                                <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque
                                    euismod est quis mauris lacinia pharetra. Sed a ligula ac odio
                                    condimentum aliquet a nec nulla. Aliquam bibendum ex sit amet ipsum
                                    rutrum feugiat ultrices enim quam.
                                </p>
                                <p class="mt-5 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque
                                    euismod est quis mauris lacinia pharetra. Sed a ligula ac odio.
                                </p>

                                <div class="flex items-center justify-end w-full gap-3 mt-8">
                                    <button @click="isModalOpen = false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                                        Close
                                    </button>
                                    <button type="button" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Breadcrumb End -->
        </div>
        <div class="border-t border-gray-100 p-5 dark:border-gray-800 sm:p-6">
            <!-- DataTable Three -->
            <div x-data="dataTableThree()" class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex flex-col gap-2 px-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="px-6 py-5">
                            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                                List by
                            </h3>
                        </div>
                        <div class=" border-gray-00 border-l p-3 dark:border-gray-800 sm:p-6">
                            <div class="custom-scrollbar max-w-full px-2 overflow-x-auto pb-3 xsm:pb-0">
                                <div class="min-w-[309px]">
                                    <div class="inline-flex flex-wrap gap-2 items-center shadow-theme-xs">


                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="max-w-full overflow-x-auto">
                    <div class="min-w-[1102px]">
                        <!-- table header start -->
                        <div class="grid grid-cols-12 border-t border-gray-200 dark:border-gray-800">
                            <div class="col-span-3 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex w-full cursor-pointer items-center justify-between" @click="sortBy('user')">
                                    <div class="flex items-center gap-3">
                                        <div x-data="{ checkboxToggle: false }">
                                            <label class="flex cursor-pointer select-none items-center text-sm font-medium text-gray-700 dark:text-gray-400">
                                                <span class="relative">
                                                    <input type="checkbox" class="sr-only" @change="checkboxToggle = !checkboxToggle">
                                                    <span :class="checkboxToggle ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="flex h-4 w-4 items-center justify-center rounded-sm border-[1.25px] bg-transparent border-gray-300 dark:border-gray-700">
                                                        <span :class="checkboxToggle ? '' : 'opacity-0'" class="opacity-0">
                                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M10 3L4.5 8.5L2 6" stroke="white" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            </svg>
                                                        </span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>

                                        <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                                            Options
                                        </p>
                                    </div>


                                </div>
                            </div>
                            <div class="col-span-3 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex w-full cursor-pointer items-center justify-between" @click="sortBy('position')">
                                    <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                                        Gallery
                                    </p>


                                </div>
                            </div>
                            <div class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex w-full cursor-pointer items-center justify-between" @click="sortBy('office')">
                                    <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                                        SKU
                                    </p>

                                </div>
                            </div>
                            <div class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex w-full cursor-pointer items-center justify-between" @click="sortBy('age')">
                                    <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                                        Price
                                    </p>

                                </div>
                            </div>

                            <div class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex w-full cursor-pointer items-center justify-between">
                                    <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">
                                        Action
                                    </p>

                                </div>
                            </div>
                        </div>
                        <!-- table header end -->

                        <!-- table body start -->


                        <!-- table body end -->
                    </div>
                </div>

            </div>


            <!-- DataTable Three -->
        </div>
    </div>
</div>

<script>
    let variants = [];
    let variantCombinations = [];
    let selectedOptions = [];

    // Hàm tạo tổ hợp các variants
    function generateCombinations() {
        if (variants.length === 0) return [];

        // Lấy các options từ mỗi variant
        const optionsArray = variants.map(v => v.options);

        // Hàm đệ quy để tạo tổ hợp
        function combine(current, arrays) {
            if (arrays.length === 0) {
                return [current];
            }

            const results = [];
            const currentArray = arrays[0];
            const remainingArrays = arrays.slice(1);

            for (const item of currentArray) {
                results.push(...combine([...current, item], remainingArrays));
            }

            return results;
        }

        return combine([], optionsArray);
    }

    // Cập nhật hàm updateVariantsList để hiển thị cả trong bảng
    function updateVariantsList() {
        const variantsList = document.querySelector('.inline-flex.flex-wrap.gap-2');
        variantsList.innerHTML = '';

        // Hiển thị các tags
        variants.forEach(variant => {
            variant.options.forEach(option => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-800 border border-gray-300 rounded-md hover:text-white hover:bg-blue-700 dark:bg-white/[0.03] dark:text-gray-200 dark:border-gray-700 dark:hover:bg-white/[0.03]';
                button.textContent = option;

                // Thêm sự kiện click cho button
                button.addEventListener('click', () => {
                    handleOptionClick(button, option);
                });

                variantsList.appendChild(button);
            });
        });

        // Tạo các tổ hợp variant và cập nhật bảng
        variantCombinations = generateCombinations();
        const tableBody = document.querySelector('[x-data="dataTableThree()"]');
        if (!tableBody) return;

        // Xóa các hàng cũ
        const existingRows = tableBody.querySelectorAll('.variant-row');
        existingRows.forEach(row => row.remove());

        // Thêm các hàng mới
        variantCombinations.forEach((combination, index) => {
            const row = document.createElement('div');
            row.className = 'variant-row grid grid-cols-12 border-t border-gray-100 dark:border-gray-800';

            row.innerHTML = `
                <div class="col-span-3 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                    <div class="flex gap-3">
                        <div class="mt-1">
                            <label class="flex cursor-pointer select-none items-center text-sm font-medium text-gray-700 dark:text-gray-400">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-brand-500 rounded border-gray-300 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900" data-combination="${combination.join(' / ')}">
                            </label>
                        </div>
                        <div>
                            <p class="block text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                ${combination.join(' / ')}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-span-3 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                    <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                    <div>
                    
                        <input type="text" id="sku" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  required />
        </div>
                </div>
                <div class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                   <input type="number" id="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                </div>
                <div class="col-span-2 flex items-center px-4 py-3">
                 
                                        <button class="delete-category inline-flex items-center justify-center w-8 h-8 text-red-500 hover:text-red-600 hover:bg-red-100 rounded-full dark:text-red-400 dark:hover:bg-red-900" >
                                            <svg class="cursor-pointer hover:fill-error-500 dark:hover:fill-error-500 fill-gray-700 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z" fill=""></path>
                                            </svg>
                                        </button>
                </div>
            `;

            tableBody.appendChild(row);
        });

        // Cập nhật trạng thái hiển thị bảng
        const tableContainer = document.querySelector('[x-data]');
        if (tableContainer && tableContainer.__x) {
            tableContainer.__x.$data.hasVariants = variants.length > 0;
        }
    }

    // Định nghĩa removeOption trong global scope
    window.removeOption = function(element, variantId, value) {
        // Xóa tag
        element.parentElement.remove();

        // Cập nhật mảng variants
        const variant = variants.find(v => v.id === variantId);
        if (variant) {
            variant.options = variant.options.filter(opt => opt !== value);
            if (variant.options.length === 0) {
                variants = variants.filter(v => v.id !== variantId);
            }
        }

        updateVariantsList();
    }

    document.getElementById('addVariantBtn').addEventListener('click', function() {
        const variantContainer = document.getElementById('variantContainer');

        // Tạo div mới cho variant
        const newVariant = document.createElement('div');
        newVariant.className = "-mx-2.5 flex flex-wrap gap-y-5 border-b pb-4 mb-4";

        // Tạo ID duy nhất cho variant mới
        const variantId = Date.now();

        newVariant.innerHTML = `
            <div class="w-full px-2.5 xl:w-1/2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Variant Type
                </label>
                <div class="relative z-20 bg-transparent">
                    <select class="variantType w-full px-4 py-3 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg appearance-none dark:bg-dark-900 h-11 bg-none shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" data-variant-id="${variantId}">
                        <option value="">Choose a variant</option>
                        <option value="size">Size</option>
                        <option value="color">Color</option>
                    </select>
                    <span class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="w-full px-2.5 xl:w-1/2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Options
                </label>
                <input type="text" class="optionInput dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" data-variant-id="${variantId}">
                <div class="optionTags flex flex-wrap gap-2 mt-2"></div>
            </div>
        `;

        variantContainer.appendChild(newVariant);
        updateVariantsList();
    });

    document.addEventListener('keypress', function(e) {
        if (e.target.classList.contains('optionInput') && e.key === 'Enter') {
            e.preventDefault();
            const value = e.target.value.trim();
            const variantId = e.target.dataset.variantId;
            const variantType = document.querySelector(`.variantType[data-variant-id="${variantId}"]`).value;

            if (value && variantType) {
                // Thêm tag vào container
                const tag = document.createElement('div');
                tag.className = 'flex items-center gap-1 px-2 py-1 text-sm bg-blue-100 text-blue-600 rounded';
                tag.innerHTML = `${value} <span class="cursor-pointer text-gray-500" onclick="window.removeOption(this, '${variantId}', '${value}')">x</span>`;
                e.target.nextElementSibling.appendChild(tag);

                // Cập nhật mảng variants
                const existingVariant = variants.find(v => v.id === variantId);
                if (existingVariant) {
                    existingVariant.options.push(value);
                } else {
                    variants.push({
                        id: variantId,
                        type: variantType,
                        options: [value]
                    });
                }

                e.target.value = '';
                updateVariantsList();
            }
        }
    });

    // Cập nhật hàm handleOptionClick
    function handleOptionClick(button, option) {
        // Tìm variant chứa option được chọn
        const selectedVariant = variants.find(variant =>
            variant.options.includes(option)
        );

        if (selectedVariant) {
            // Toggle trạng thái chọn của option
            const isSelected = selectedOptions.includes(option);
            if (isSelected) {
                selectedOptions = selectedOptions.filter(opt => opt !== option);
            } else {
                selectedOptions.push(option);
            }

            // Cập nhật style của button để hiển thị trạng thái chọn
            button.classList.toggle('bg-brand-500', !isSelected);
            button.classList.toggle('text-white', !isSelected);

            // Tìm tất cả các combination chứa bất kỳ option đã chọn nào
            const matchingCombinations = variantCombinations.filter(combo =>
                selectedOptions.some(opt => combo.includes(opt))
            );

            // Toggle tất cả các checkbox tương ứng
            variantCombinations.forEach(combo => {
                const checkbox = document.querySelector(`input[type="checkbox"][data-combination="${combo.join(' / ')}"]`);
                if (checkbox) {
                    checkbox.checked = matchingCombinations.some(match =>
                        match.join(' / ') === combo.join(' / ')
                    );
                }
            });
        }
    }
</script>

@endsection