@extends('layouts.customer')

@section('title', 'Order List')

@section('content-customer')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `File Import`}">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2
                class="text-xl font-semibold text-gray-800 dark:text-white/90"
                x-text="pageName"></h2>

            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                            href="/customer/orders/order-list">
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
    <!-- Breadcrumb End -->

    <div class="flex justify-end space-x-1">
        <div class="border-t border-gray-100 dark:border-gray-800">
            <div x-data="{isModalOpen: false, isLoading: false}">
                <button class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"
                    @click="isModalOpen = true">
                    Order Import File
                </button>
                <button id="deleteSelectedFiles" class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-red-500 shadow-theme-xs hover:bg-red-600">
                    <div class="flex items-center">
                        Delete Selected Files
                    </div>
                </button>
                <!-- Modal -->
                <div x-show="isModalOpen" x-cloak class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999">
                    <!-- Overlay -->
                    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>

                    <!-- Modal content -->
                    <div @click.outside="isModalOpen = false" class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
                        <!-- Close button -->
                        <button @click="isModalOpen = false" class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
                            </svg>
                        </button>

                        <!-- Modal body -->
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-7 text-title-sm dark:text-white/90">
                                Import Order from File Excel
                            </h4>
                            <p class="text-sm leading-6 text-gray-500 dark:text-gray-400 mb-5">
                                File must be in .xlsx or .xls format and not exceed 10MB. Please ensure the file contains all required columns such as External ID, First Name, Address 1, City, County, Postcode, Country, Quantity and Part Number. All currency values will be converted to USD for calculation purposes.
                            </p>

                            <div class="mb-5 space-y-4 sm:space-y-0 sm:flex sm:flex-wrap sm:gap-6 text-sm text-gray-600 dark:text-gray-300">
                                <div>
                                    <p class="mb-1">📄 Download template sample file:</p>
                                    <a href="https://hmfulfill.com/uploads/fulfillment/TemplateImportOrder.xlsx" download class="flex items-center text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Template Import Order.xlsx
                                    </a>
                                </div>

                                <div>
                                    <p class="mb-1">🔍 See valid SKU / Part Number list:</p>
                                    <a href="{{ route('products.list') }}" class="flex items-center text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        View SKU List
                                    </a>
                                </div>

                                <div>
                                    <p class="mb-1">📘 Download import instruction guide:</p>
                                    <a href="https://hmfulfill.com/uploads/fulfillment/OrderImportGuide.docx" download class="flex items-center text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Order Import Guide
                                    </a>
                                </div>
                            </div>


                            <form action="{{ route('customer.order-upload') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                                @csrf
                                <div class="mb-5">
                                    <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Excel file</label>
                                    <input type="file" name="file" id="file" accept=".xlsx,.xls" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                    @error('file')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mb-5">
                                    <label for="warehouse" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Warehouse</label>
                                    <select name="warehouse" id="warehouse" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                        <option value="UK">UK</option>
                                    </select>
                                    @error('warehouse')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                @if(session('error'))
                                <div class="mb-5 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                                    {{ session('error') }}
                                </div>
                                @endif

                                <div class="flex items-center justify-end w-full gap-3 mt-8 flex-col sm:flex-row">
                                    <button @click="isModalOpen = false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                                        Close
                                    </button>
                                    <button type="submit" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 sm:w-auto" @click="isLoading = true">
                                        <span x-show="!isLoading">Upload</span>
                                        <span x-show="isLoading" class="flex items-center">
                                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Uploading...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-gray-100 p-5 dark:border-gray-800 sm:p-6">
        <!-- Table Four -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-5 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Recent File Import
                    </h3>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <form>
                        <div class="relative">
                            <span class="absolute -translate-y-1/2 pointer-events-none top-1/2 left-4">
                                <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                                </svg>
                            </span>
                            <input type="text" placeholder="Search..." class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                    </form>
                    <div>
                        <button class="text-theme-sm shadow-theme-xs inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                            <svg class="stroke-current fill-white dark:fill-gray-800" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5"></path>
                                <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5"></path>
                            </svg>

                            Filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <!-- table header start -->
                    <thead class="border-gray-100 border-y bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <input type="checkbox" id="select-all-files" class="h-5 w-5 rounded-md border-gray-300 cursor-pointer">
                                    <span class="block mx-2 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        File ID
                                    </span>
                                </div>
                            </th>

                            <th class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        File Name
                                    </p>
                                </div>
                            </th>
                            <th class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Status
                                    </p>
                                </div>
                            </th>
                            <th class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Error
                                    </p>
                                </div>
                            </th>

                            <th class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Created At
                                    </p>
                                </div>
                            </th>
                            <th class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Updated At
                                    </p>
                                </div>
                            </th>
                            <!-- <th class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center justify-center">
                                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                Action
                                            </p>
                                        </div>
                                    </th> -->
                        </tr>
                    </thead>
                    <!-- table header end -->

                    <!-- table body start -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($files as $file)
                        <tr>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <input type="checkbox" class="file-checkbox h-5 w-5 rounded-lg border-gray-300 cursor-pointer" value="{{ $file->id }}">
                                    <span class="ml-3 block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $file->id }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div x-data="{checked: false}" class="flex items-center gap-3">

                                        <div class="flex items-center">
                                            <a href="{{ route('customer.file-detail', $file->id) }}" class="text-theme-sm font-medium text-gray-700 dark:text-gray-400 flex items-center group relative" title="{{ $file->file_name }}">
                                                {{ \Illuminate\Support\Str::limit($file->file_name, 30) }}
                                            </a>


                                            <a href="{{ $file->file_path }}" target="_blank" class="text-blue-500 hover:underline-none ml-2">
                                                <svg title="Download file" class="cursor-pointer hover:fill-success-500 dark:hover:fill-success-500 fill-gray-700 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10 2C10.4142 2 10.75 2.33579 10.75 2.75V11.6893L13.7197 8.71967C14.0126 8.42678 14.4874 8.42678 14.7803 8.71967C15.0732 9.01256 15.0732 9.48744 14.7803 9.78033L10.5303 14.0303C10.2374 14.3232 9.76256 14.3232 9.46967 14.0303L5.21967 9.78033C4.92678 9.48744 4.92678 9.01256 5.21967 8.71967C5.51256 8.42678 5.98744 8.42678 6.28033 8.71967L9.25 11.6893V2.75C9.25 2.33579 9.58579 2 10 2ZM3.5 16.25C3.5 15.8358 3.83579 15.5 4.25 15.5H15.75C16.1642 15.5 16.5 15.8358 16.5 16.25C16.5 16.6642 16.1642 17 15.75 17H4.25C3.83579 17 3.5 16.6642 3.5 16.25Z" fill="" />
                                                </svg>
                                            </a>
                                            <!-- Tooltip -->
                                            <span class="absolute left-1/2 -translate-x-1/2 bottom-8 bg-gray-800 text-white text-xs rounded-md px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                Download file
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div style="text-transform: capitalize;" class="flex items-center">
                                    @switch($file->status)
                                    @case('pending')
                                    <p class="bg-warning-50 text-theme-xs text-warning-600 dark:bg-warning-500/15 dark:text-warning-400 rounded-full px-2 py-0.5 font-medium">
                                        {{ $file->status }}
                                    </p>
                                    @break
                                    @case('failed')
                                    <p class="bg-error-50 text-theme-xs text-error-600 dark:bg-error-500/15 dark:text-error-400 rounded-full px-2 py-0.5 font-medium">
                                        {{ $file->status }}
                                    </p>
                                    @break
                                    @case('processed')
                                    <p class="bg-success-50 text-theme-xs text-success-600 dark:bg-success-500/15 dark:text-success-400 rounded-full px-2 py-0.5 font-medium">
                                        {{ $file->status }}
                                    </p>
                                    @break
                                    @default
                                    <p class="bg-gray-50 text-theme-xs text-gray-600 dark:bg-gray-500/15 dark:text-gray-400 rounded-full px-2 py-0.5 font-medium">
                                        {{ $file->status }}
                                    </p>
                                    @endswitch
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if(!empty($file->error_logs) && (is_array($file->error_logs) || is_object($file->error_logs)))
                                    <div x-data="{ showErrorModal: false }">
                                        <a href="#" @click.prevent="showErrorModal = true" class="text-red-600 hover:underline text-sm">
                                            🔴 Your file has errors, click to view details
                                        </a>

                                        <!-- Modal lỗi -->
                                        <template x-teleport="body">
                                            <div x-show="showErrorModal"
                                                x-transition
                                                class="fixed inset-0 flex items-center justify-center p-5 z-50">
                                                <!-- Overlay -->
                                                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showErrorModal = false"></div>

                                                <!-- Nội dung modal -->
                                                <div class="relative bg-white dark:bg-gray-800 rounded-xl p-6 max-w-xl w-full shadow-lg z-50"
                                                    @click.outside="showErrorModal = false">
                                                    <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Error Details</h2>
                                                    <div class="max-h-[300px] overflow-y-auto text-sm space-y-2">
                                                        @foreach($file->error_logs as $row => $messages)
                                                        <div class="text-red-600 dark:text-red-400">
                                                            <strong>Row {{$row}}:</strong>
                                                            <ul class="list-disc ml-6">
                                                                @if(is_array($messages))
                                                                @foreach($messages as $message)
                                                                <li>
                                                                    @if($message === "Insufficient balance in wallet")
                                                                    ⚠️ Insufficient balance in wallet. Please top up your wallet to proceed.
                                                                    @else
                                                                    {{$message}}
                                                                    @endif
                                                                </li>
                                                                @endforeach
                                                                @else
                                                                <li>{{ $messages }}</li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="mt-5 flex justify-end">
                                                        <button @click="showErrorModal = false"
                                                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    @else
                                    <span class="text-gray-700 text-sm dark:text-gray-400">
                                        ✅ No errors
                                    </span>
                                    @endif
                                </div>
                            </td>



                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                        {{ $file->created_at }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                        {{ $file->updated_at }}
                                    </p>
                                </div>
                            </td>
                            <!-- <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">

                                            <div class="relative group">
                                                <svg title="Delete" class="mx-2 cursor-pointer hover:fill-error-500 dark:hover:fill-error-500 fill-gray-700 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" data-id="{{ $file->id }}" @click="deleteSingleFile({{ $file->id }})">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z" fill=""></path>
                                                </svg>
                                                <span class="absolute left-1/2 -translate-x-1/2 bottom-8 bg-gray-800 text-white text-xs rounded-md px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    Delete
                                                </span>
                                            </div>
                                            <div class="relative group">
                                                <svg title="Edit" class="mx-2 cursor-pointer hover:fill-blue-500 fill-gray-700" width="20" height="20"
                                                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                                    @click="editFile({{ $file->id }})">
                                                    <path d="M4 21h4l11-11-4-4L4 17v4zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 4 4 1.83-1.83z" fill="currentColor" />
                                                </svg>
                                                <span class="absolute left-1/2 -translate-x-1/2 bottom-8 bg-gray-800 text-white text-xs rounded-md px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    Edit
                                                </span>
                                            </div>
                                            <div class="relative group">
                                                <svg title="Upload" class="mx-2 cursor-pointer hover:fill-green-500 fill-gray-700" width="20" height="20"
                                                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                                    @click="uploadFile({{ $file->id }})">
                                                    <path d="M5 20h14v-2H5v2zm7-18l-7 7h4v6h6v-6h4l-7-7z" fill="currentColor" />
                                                </svg>
                                                <span class="absolute left-1/2 -translate-x-1/2 bottom-8 bg-gray-800 text-white text-xs rounded-md px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    Upload
                                                </span>
                                            </div>

                                        </div>
                                    </td> -->

                        </tr>
                        @endforeach

                    </tbody>
                    <!-- table body end -->
                </table>

            </div>
            <div class="mt-4 px-6 py-3 border-t border-gray-100 dark:border-gray-800">
                {{ $files->links() }}
            </div>
        </div>
        <!-- Table Four -->
    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButton = document.getElementById('deleteSelectedFiles');
        const selectAllCheckbox = document.getElementById('select-all-files');
        const fileCheckboxes = document.querySelectorAll('.file-checkbox');
        const selectedCountSpan = document.getElementById('selectedCount');

        // Hàm cập nhật trạng thái nút xóa và số lượng item đã chọn
        function updateDeleteButton() {
            const selectedFiles = document.querySelectorAll('.file-checkbox:checked');
            const count = selectedFiles.length;
            selectedCountSpan.textContent = count;
            deleteButton.style.display = count > 0 ? 'block' : 'none';
        }

        // Xử lý sự kiện cho checkbox "Chọn tất cả"
        selectAllCheckbox.addEventListener('change', function() {
            fileCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateDeleteButton();
        });

        // Xử lý sự kiện cho từng checkbox
        fileCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(fileCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
                updateDeleteButton();
            });
        });

        // Xử lý sự kiện xóa
        deleteButton.addEventListener('click', function() {
            const selectedFiles = Array.from(document.querySelectorAll('.file-checkbox:checked')).map(checkbox => checkbox.value);

            if (selectedFiles.length === 0) {
                Swal.fire({
                    title: 'Notification',
                    text: 'Please select at least one file to delete',
                    icon: 'warning'
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure you want to delete the selected files?',
                text: `Are you sure you want to delete ${selectedFiles.length} files selected?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/customer/delete-files', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                ids: selectedFiles
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success',
                                    text: 'Files deleted successfully',
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'An error occurred while deleting the file: ' + data.message,
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: 'An error occurred while deleting the file: ' + error.message,
                                icon: 'error'
                            });
                        });
                }
            });
        });

        // Khởi tạo trạng thái ban đầu của nút xóa
        updateDeleteButton();

        // Xử lý xóa một file
        function deleteSingleFile(fileId) {
            Swal.fire({
                title: 'Are you sure you want to delete this file?',
                text: 'Are you sure you want to delete this file?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/customer/delete-files', { // Sửa đường dẫn API
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json' // Thêm header Accept
                            },
                            body: JSON.stringify({
                                ids: [fileId]
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success',
                                    text: 'File deleted successfully',
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'An error occurred while deleting the file: ' + data.message,
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error',
                                text: 'An error occurred while deleting the file. Please try again later.',
                                icon: 'error'
                            });
                        });
                }
            });
        }

        // Thêm hàm vào window object để có thể gọi từ onclick
        window.deleteSingleFile = deleteSingleFile;
    });
</script>
@endsection