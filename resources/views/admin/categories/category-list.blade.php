@extends('layouts.admin')

@section('content-admin')

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `Category List`}">
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
                    <div>
                        <div class="flex items-center gap-5">
                            <a href="{{ route('admin.categories.create') }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                                <svg
                                    class="fill-current"
                                    width="20"
                                    height="20"
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        fill-rule="evenodd"
                                        clip-rule="evenodd"
                                        d="M10 2C10.5523 2 11 2.44772 11 3V8H16C16.5523 8 17 8.44772 17 9C17 9.55228 16.5523 10 16 10H11V15C11 15.5523 10.5523 16 10 16C9.44772 16 9 15.5523 9 15V10H4C3.44772 10 3 9.55228 3 9C3 8.44772 3.44772 8 4 8H9V3C9 2.44772 9.44772 2 10 2Z"
                                        fill="currentColor" />
                                </svg>
                                Add Category
                            </a>
                            <button id="delete-selected"
                                class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                                <svg class="cursor-pointer hover:fill-error-500 dark:hover:fill-error-500 fill-gray-700 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z" fill=""></path>
                                </svg>
                                Delete Selected
                            </button>



                        </div>

                    </div>

                    <!-- <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <form method="GET" action="{{ route('admin.categories') }}">
                            <div class="relative">
                                <span class="absolute -translate-y-1/2 pointer-events-none top-1/2 left-4">
                                    <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                                    </svg>
                                </span>
                                <input type="text" name="search" placeholder="Tìm kiếm..." value="{{ request('search') }}" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            </div>
                        </form>

                    </div> -->
                </div>

                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="min-w-full">
                        <!-- table header start -->
                        <thead class="border-gray-100 border-y bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                            id="select-all"
                                            class="h-5 w-5 rounded-md border-gray-300 cursor-pointer">
                                        <label for="select-all" class="ml-3 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            ID
                                        </label>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Name
                                        </p>
                                    </div>
                                </th>
                                <th class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Parent Category
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
                                <th class="px-6 py-3  whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                            Action
                                        </p>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <!-- table header end -->

                        <!-- table body start -->
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($categories as $category)
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                            class="category-checkbox h-5 w-5 rounded-lg border-gray-300 cursor-pointer"
                                            value="{{ $category->id }}">
                                        <span class="ml-3 block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ $category->id }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex items-center gap-3">
                                            <div>
                                                <span class="text-theme-sm mb-0.5 block font-medium text-gray-700 dark:text-gray-400">
                                                    {{ $category->name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400 ">
                                            {{ $category->parent ? $category->parent->name : 'No parent' }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ $category->created_at}}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ $category->updated_at}}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="edit-category inline-flex items-center justify-center w-8 h-8 text-blue-500 hover:text-blue-600 hover:bg-blue-100 rounded-full dark:text-blue-400 dark:hover:bg-blue-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <button class="delete-category inline-flex items-center justify-center w-8 h-8 text-red-500 hover:text-red-600 hover:bg-red-100 rounded-full dark:text-red-400 dark:hover:bg-red-900" data-id="{{ $category->id }}">
                                            <svg class="cursor-pointer hover:fill-error-500 dark:hover:fill-error-500 fill-gray-700 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z" fill=""></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
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


{{-- Thêm CDN của SweetAlert2 trước thẻ đóng </body> --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('delete-selected').addEventListener('click', function() {
        const selectedIds = Array.from(document.querySelectorAll('.category-checkbox:checked')).map(cb => cb.value);
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
                    fetch("{{ route('admin.categories.destroy', '') }}/" + selectedIds.join(','), {
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
                                    'The categories have been deleted successfully.',
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
        } else {
            Swal.fire(
                'Attention!',
                'Please select at least one category to delete.',
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
                    fetch("{{ route('admin.categories.destroy', '') }}/" + id, {
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
                                    'The category has been deleted successfully.',
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
</script>

@endsection