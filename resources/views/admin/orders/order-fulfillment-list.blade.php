@extends('layouts.admin')

@section('title', 'Order Fulfillment List')

@section('content-admin')
<main>
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: `Order Fulfillment List`}">
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
        <!-- Breadcrumb End -->

        <div class="space-y-5 sm:space-y-6">

            <div
                class="p-5 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                <!-- ====== Table Six Start -->
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col gap-5 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                Order Fulfillment List
                            </h3>
                            <div class="flex items-center gap-2" x-data="{isModalOpen: false}">
                                <button
                                    class="px-4 py-2 text-sm font-medium  rounded-lg bg-white shadow-theme-xs hover:bg-gray-50 flex items-center gap-2 shadow-theme-xs ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]"
                                    @click="isModalOpen = !isModalOpen">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 4V16M16 10H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    Add File
                                </button>
                                <button id="delete-selected-files"
                                    class="px-4 py-2 text-sm font-medium  rounded-lg bg-white shadow-theme-xs hover:bg-gray-50 flex items-center gap-2 shadow-theme-xs ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                                    <svg class="cursor-pointer hover:fill-error-500 dark:hover:fill-error-500 fill-gray-700 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z" fill=""></path>
                                    </svg>
                                    Delete Selected
                                </button>
                                <!-- <button id="delete-selected-files"
                                    class="px-4 py-2 text-sm font-medium  rounded-lg bg-white shadow-theme-xs hover:bg-gray-50 flex items-center gap-2 shadow-theme-xs ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                                    <svg title="Upload" class="mx-2 cursor-pointer hover:fill-green-500 fill-gray-700" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" @click="uploadFile(26)">
                                        <path d="M5 20h14v-2H5v2zm7-18l-7 7h4v6h6v-6h4l-7-7z" fill="currentColor"></path>
                                    </svg>
                                    Upload Selected
                                </button> -->

                                <!-- Modal Upload -->
                                <div x-show="isModalOpen" id="uploadModal" class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999" style="display: none;">
                                    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
                                    <div @click.outside="isModalOpen = false" class="relative w-full max-w-[584px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
                                        <!-- close btn -->
                                        <button @click="isModalOpen = false" class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                                            <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""></path>
                                            </svg>
                                        </button>

                                        <form id="uploadForm" class="space-y-6" action="{{ route('fulfillment.upload') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">
                                                Add Fulfillment File
                                            </h4>

                                            <div class="space-y-4">
                                                <div class="flex items-center justify-center w-full">
                                                    <label class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                            <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                                            </svg>
                                                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">XLSX, XLS (MAX. 10MB)</p>
                                                        </div>
                                                        <input id="dropzone-file" type="file" class="hidden" name="file" accept=".xlsx,.xls" />
                                                        @if ($errors->has('file'))
                                                        <span class="text-red-500 text-sm mt-1">{{ $errors->first('file') }}</span>
                                                        @endif
                                                    </label>
                                                </div>

                                                <div class="flex items-center justify-end gap-3">
                                                    <button @click="isModalOpen = false" type="button" class="flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="flex justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                                                        Upload
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center">
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
                                                Error
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
                                                    <a href="{{ route('admin.order-fulfillment-detail', $file->id) }}" target="_blank" class="text-blue-500 hover:underline-none">
                                                        <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-400 flex items-center group relative">
                                                            {{ $file->file_name }}
                                                        </span>
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
                                        <div class="flex items-center">
                                            <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                                @if(is_array($file->error_logs))
                                                {{ implode(', ', $file->error_logs) }}
                                                @else
                                                {{ $file->error_logs }}
                                                @endif
                                            </p>
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
                                            @case('success')
                                            <p class="bg-success-50 text-theme-xs text-success-600 dark:bg-success-500/15 dark:text-success-500 rounded-full px-2 py-0.5 font-medium">
                                                {{ $file->status }}
                                            </p>
                                            @break
                                            @case('failed')
                                            <p class="bg-error-50 text-theme-xs text-error-600 dark:bg-error-500/15 dark:text-error-400 rounded-full px-2 py-0.5 font-medium">
                                                {{ $file->status }}
                                            </p>
                                            @break
                                            @case('processed')
                                            <p class="bg-info-50 text-theme-xs text-info-600 dark:bg-info-500/15 dark:text-info-400 rounded-full px-2 py-0.5 font-medium">
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
                </div>
                <!-- ====== Table Six End -->
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.getElementById('select-all-files').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.file-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('delete-selected-files').addEventListener('click', function() {
        const selectedIds = Array.from(document.querySelectorAll('.file-checkbox:checked')).map(cb => cb.value);

        if (selectedIds.length > 0) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Sửa URL và phương thức gửi request
                    fetch("{{ route('fulfillment.files.destroy') }} ", {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ids: selectedIds
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'The files have been deleted successfully.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message || 'Something went wrong!',
                                    'error'
                                );
                            }
                        });
                }
            });
        } else {
            Swal.fire(
                'Attention!',
                'Please select at least one file to delete.',
                'warning'
            );
        }
    });

    document.querySelectorAll('.delete-category').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('fulfillment.files.destroy', '') }}/" + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'The order has been deleted successfully.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message,
                                    'error'
                                );
                            }
                        });
                }
            });
        });
    });

    // Hàm xử lý xóa một item
    function deleteSingleFile(fileId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ route('fulfillment.files.destroy', '') }}/${fileId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Deleted!',
                                'The file has been deleted successfully.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                data.message || 'Something went wrong!',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            'Something went wrong!',
                            'error'
                        );
                    });
            }
        });
    }

    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const modal = document.getElementById('uploadModal');

        fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Đóng modal trước khi hiển thị thông báo
                    modal.style.display = 'none';
                    // Reset giá trị của form
                    this.reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Load lại trang sau khi hiển thị thông báo thành công
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Có lỗi xảy ra khi xử lý yêu cầu'
                });
            });
    });
</script>

@endsection