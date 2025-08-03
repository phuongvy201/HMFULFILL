@extends('layouts.admin')

@section('title', 'Orders Customer Received')

@section('content-admin')
<main x-data="statusHandler()">
    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: `Orders Customer Received`}">
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
            <button id="delete-selected-files"
                class="px-4 py-2 text-sm font-medium  rounded-lg bg-white shadow-theme-xs hover:bg-gray-50 flex items-center gap-2 shadow-theme-xs ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                <svg class="cursor-pointer hover:fill-error-500 dark:hover:fill-error-500 fill-gray-700 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z" fill=""></path>
                </svg>
                Delete Selected
            </button>
            <div
                class="p-5 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                <!-- ====== Table Six Start -->
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex flex-col gap-5 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">


                        <div class="flex items-center">
                            <form id="filterForm" class="flex gap-4">
                                <input type="text" name="file_id" placeholder="File ID" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 placeholder:text-gray-400">

                                <input type="text" name="customer" placeholder="Customer" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800 placeholder:text-gray-400">

                                <select name="warehouse" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800">
                                    <option value="">Warehouse</option>
                                    <option value="US">US</option>
                                    <option value="UK">UK</option>
                                    <option value="VN">VN</option>
                                    <!-- Thêm các warehouse khác nếu cần -->
                                </select>

                                <select name="status" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 text-sm text-gray-800">
                                    <option value="">Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="processed">Processed</option>
                                    <option value="failed">Failed</option>
                                    <!-- Thêm các trạng thái khác nếu cần -->
                                </select>

                                <div class="relative">
                                    <input
                                        type="date"
                                        name="date"
                                        id="datePicker"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 px-4 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-brand-500/10 focus:outline-none cursor-pointer"
                                        onclick="this.showPicker()">
                                    <span class="absolute top-1/2 right-3 -translate-y-1/2 pointer-events-none">
                                        <svg class="fill-gray-700" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.33317 0.0830078C4.74738 0.0830078 5.08317 0.418794 5.08317 0.833008V1.24967H8.9165V0.833008C8.9165 0.418794 9.25229 0.0830078 9.6665 0.0830078C10.0807 0.0830078 10.4165 0.418794 10.4165 0.833008V1.24967L11.3332 1.24967C12.2997 1.24967 13.0832 2.03318 13.0832 2.99967V4.99967V11.6663C13.0832 12.6328 12.2997 13.4163 11.3332 13.4163H2.6665C1.70001 13.4163 0.916504 12.6328 0.916504 11.6663V4.99967V2.99967C0.916504 2.03318 1.70001 1.24967 2.6665 1.24967L3.58317 1.24967V0.833008C3.58317 0.418794 3.91896 0.0830078 4.33317 0.0830078ZM4.33317 2.74967H2.6665C2.52843 2.74967 2.4165 2.8616 2.4165 2.99967V4.24967H11.5832V2.99967C11.5832 2.8616 11.4712 2.74967 11.3332 2.74967H9.6665H4.33317ZM11.5832 5.74967H2.4165V11.6663C2.4165 11.8044 2.52843 11.9163 2.6665 11.9163H11.3332C11.4712 11.9163 11.5832 11.8044 11.5832 11.6663V5.74967Z" fill="" />
                                        </svg>
                                    </span>
                                </div>

                                <button type="submit" class="flex justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                                    Filter
                                </button>
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
                                            <span class="block mx-2 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                                Warehouse
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
                                                Customer
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
                                            <input type="checkbox" class="file-checkbox h-5 w-5 rounded-md border-gray-300 cursor-pointer" value="{{ $file->id }}">
                                            <span class="ml-3 block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $file->id }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex items-center">
                                                <p class="text-gray-700 text-sm dark:text-gray-400">
                                                    {{ $file->warehouse }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-3 whitespace-nowrap max-w-[200px]">
                                        <div class="flex items-center">
                                            <div x-data="{checked: false}" class="flex items-center gap-3">
                                                <div class="flex items-center relative group">
                                                    <a href="/admin/order-fulfillment-detail/{{ $file->id }}" target="_blank" class="text-blue-500 hover:underline-none">
                                                        <span
                                                            class="text-xs font-medium text-gray-700 dark:text-gray-400 flex items-center max-w-[150px] truncate"
                                                            title="{{ $file->file_name }}">
                                                            {{ $file->file_name }}
                                                        </span>
                                                    </a>


                                                    <!-- Tooltip hiển thị tên đầy đủ -->
                                                    <div class="absolute bottom-full mb-1 left-0 z-10 w-max max-w-xs bg-gray-800 text-white text-xs rounded-md px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-normal">
                                                        {{ $file->file_name }}
                                                    </div>

                                                    <a href="{{ $file->file_path }}" target="_blank" class="text-blue-500 hover:underline-none ml-2">
                                                        <svg title="Download file" class="cursor-pointer hover:fill-success-500 dark:hover:fill-success-500 fill-gray-700 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10 2C10.4142 2 10.75 2.33579 10.75 2.75V11.6893L13.7197 8.71967C14.0126 8.42678 14.4874 8.42678 14.7803 8.71967C15.0732 9.01256 15.0732 9.48744 14.7803 9.78033L10.5303 14.0303C10.2374 14.3232 9.76256 14.3232 9.46967 14.0303L5.21967 9.78033C4.92678 9.48744 4.92678 9.01256 5.21967 8.71967C5.51256 8.42678 5.98744 8.42678 6.28033 8.71967L9.25 11.6893V2.75C9.25 2.33579 9.58579 2 10 2ZM3.5 16.25C3.5 15.8358 3.83579 15.5 4.25 15.5H15.75C16.1642 15.5 16.5 15.8358 16.5 16.25C16.5 16.6642 16.1642 17 15.75 17H4.25C3.83579 17 3.5 16.6642 3.5 16.25Z" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>


                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div x-data="{checked: false}" class="flex items-center gap-3">

                                                <div class="flex items-center">
                                                    <p class="text-gray-700 text-sm dark:text-gray-400">
                                                        {{ $file->user->first_name }} {{ $file->user->last_name }}
                                                    </p>
                                                </div>

                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($file->error_logs && is_array($file->error_logs))
                                            <div x-data="{ showErrorModal: false }">
                                                <a href="#" @click.prevent="showErrorModal = true" class="text-red-600 hover:underline text-sm">
                                                    🔴 Your file is error, click to view details
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
                                        @switch($file->status)
                                        @case('pending')
                                        <p class="bg-warning-50 text-theme-xs text-warning-600 dark:bg-warning-500/15 dark:text-warning-400 rounded-full px-2 py-0.5 font-medium cursor-pointer hover:bg-warning-100 transition-colors"
                                            @click="changeStatus({{ $file->id }}, '{{ $file->status }}')"
                                            title="Click để đổi thành processed">
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
                    <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
                        <div class="">
                            @if ($files->hasPages())
                            <!-- Hiển thị các liên kết phân trang -->
                            {{ $files->links() }}
                            @else
                            <p class="text-gray-500 dark:text-gray-400">No data to paginate.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- ====== Table Six End -->
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Đảm bảo DOM đã load trước khi chạy script
    document.addEventListener('DOMContentLoaded', function() {

        // Kiểm tra element tồn tại trước khi add event listener
        const selectAllCheckbox = document.getElementById('select-all-files');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.file-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        const deleteSelectedBtn = document.getElementById('delete-selected-files');
        if (deleteSelectedBtn) {
            deleteSelectedBtn.addEventListener('click', function() {
                const selectedIds = Array.from(document.querySelectorAll('.file-checkbox:checked')).map(cb => cb.value);

                if (selectedIds.length > 0) {
                    Swal.fire({
                        title: 'Bạn có chắc chắn?',
                        text: "Bạn sẽ không thể hoàn tác hành động này!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Có, xóa nó!',
                        cancelButtonText: 'Hủy'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch("/admin/fulfillment/files/destroy", {
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
                                            'Đã xóa!',
                                            'Các tệp đã được xóa thành công.',
                                            'success'
                                        ).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire(
                                            'Lỗi!',
                                            data.message || 'Có lỗi xảy ra!',
                                            'error'
                                        );
                                    }
                                });
                        }
                    });
                } else {
                    Swal.fire(
                        'Chú ý!',
                        'Vui lòng chọn ít nhất một tệp để xóa.',
                        'warning'
                    );
                }
            });
        }

        // Kiểm tra form upload tồn tại
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
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
                            if (modal) modal.style.display = 'none';
                            this.reset();

                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: 'Có lỗi xảy ra khi xử lý yêu cầu'
                        });
                    });
            });
        }

        const datePicker = document.getElementById('datePicker');
        if (datePicker) {
            datePicker.addEventListener('click', function() {
                this.showPicker();
            });

            const icon = datePicker.nextElementSibling;
            if (icon) {
                icon.addEventListener('click', function() {
                    datePicker.showPicker();
                });
            }
        }
    });

    // Hàm xử lý xóa một item
    function deleteSingleFile(fileId) {
        Swal.fire({
            title: 'Bạn có chắc chắn?',
            text: "Bạn sẽ không thể hoàn tác hành động này!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Có, xóa nó!',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/fulfillment/files/destroy/${fileId}`, {
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
                                'Đã xóa!',
                                'Tệp đã được xóa thành công.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Lỗi!',
                                data.message || 'Có lỗi xảy ra!',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Lỗi!',
                            'Có lỗi xảy ra!',
                            'error'
                        );
                    });
            }
        });
    }

    // Function Alpine.js cho status handler
    function statusHandler() {
        return {
            async changeStatus(fileId, currentStatus) {
                if (currentStatus !== 'pending') {
                    await Swal.fire({
                        icon: 'info',
                        title: 'Thông báo',
                        text: 'Chỉ có thể đổi status từ "on hold" sang "processed"!',
                    });
                    return;
                }

                const result = await Swal.fire({
                    title: 'Xác nhận đổi status',
                    text: "Bạn có chắc muốn đổi status từ 'on hold' sang 'processed'?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Có, đổi ngay!',
                    cancelButtonText: 'Hủy'
                });

                if (result.isConfirmed) {
                    await this.submitStatusChange(fileId);
                }
            },

            async submitStatusChange(fileId) {
                Swal.fire({
                    title: 'Đang xử lý...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(`/admin/fulfillment/files/${fileId}/update-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status: 'processed'
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        location.reload();
                    } else {
                        throw new Error(data.message || 'Có lỗi xảy ra');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: error.message || 'Có lỗi xảy ra khi gửi request'
                    });
                }
            }
        }
    }
</script>

@endsection