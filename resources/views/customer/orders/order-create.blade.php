@extends('layouts.customer')

@section('title', 'Create Order')

@section('content-customer')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `Create Order`}">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Create Order</h2>
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
                    <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Create Order</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- ====== Form Layouts Section Start -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    Example Form
                </h3>
            </div>
            <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                <form>
                    <div class="-mx-2.5 flex flex-wrap gap-y-5">
                        <div class="w-full px-2.5">
                            <h4 class="pb-4 text-base font-medium text-gray-800 border-b border-gray-200 dark:border-gray-800 dark:text-white/90">
                                Personal Info
                            </h4>
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                First Name
                            </label>
                            <input type="text" placeholder="Enter first name" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Last Name
                            </label>
                            <input type="text" placeholder="Enter last name" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        </div>

                        <div class="w-full px-2.5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Gender
                            </label>
                            <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                <select class="w-full px-4 py-3 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg appearance-none dark:bg-dark-900 h-11 bg-none shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" :class="isOptionSelected &amp;&amp; 'text-gray-500 dark:text-gray-400'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        Male
                                    </option>
                                    <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        Female
                                    </option>
                                    <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        Others
                                    </option>
                                </select>
                                <span class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="w-full px-2.5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Date of Birth
                            </label>

                            <div class="relative">
                                <div class="flatpickr-wrapper"><input type="text" placeholder="Select date" class="dark:bg-dark-900 datepickerTwo h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 flatpickr-input" \="" readonly="readonly">
                                    <div class="flatpickr-calendar animate static null" tabindex="-1">
                                        <div class="flatpickr-months"><span class="flatpickr-prev-month"><svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M15.25 6L9 12.25L15.25 18.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg></span>
                                            <div class="flatpickr-month">
                                                <div class="flatpickr-current-month"><span class="cur-month">May </span>
                                                    <div class="numInputWrapper"><input class="numInput cur-year" type="number" tabindex="-1" aria-label="Year"><span class="arrowUp"></span><span class="arrowDown"></span></div>
                                                </div>
                                            </div><span class="flatpickr-next-month"><svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M8.75 19L15 12.75L8.75 6.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg></span>
                                        </div>
                                        <div class="flatpickr-innerContainer">
                                            <div class="flatpickr-rContainer">
                                                <div class="flatpickr-weekdays">
                                                    <div class="flatpickr-weekdaycontainer">
                                                        <span class="flatpickr-weekday">
                                                            Sun</span><span class="flatpickr-weekday">Mon</span><span class="flatpickr-weekday">Tue</span><span class="flatpickr-weekday">Wed</span><span class="flatpickr-weekday">Thu</span><span class="flatpickr-weekday">Fri</span><span class="flatpickr-weekday">Sat
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flatpickr-days" tabindex="-1">
                                                    <div class="dayContainer"><span class="flatpickr-day prevMonthDay" aria-label="April 27, 2025" tabindex="-1">27</span><span class="flatpickr-day prevMonthDay" aria-label="April 28, 2025" tabindex="-1">28</span><span class="flatpickr-day prevMonthDay" aria-label="April 29, 2025" tabindex="-1">29</span><span class="flatpickr-day prevMonthDay" aria-label="April 30, 2025" tabindex="-1">30</span><span class="flatpickr-day" aria-label="May 1, 2025" tabindex="-1">1</span><span class="flatpickr-day" aria-label="May 2, 2025" tabindex="-1">2</span><span class="flatpickr-day" aria-label="May 3, 2025" tabindex="-1">3</span><span class="flatpickr-day" aria-label="May 4, 2025" tabindex="-1">4</span><span class="flatpickr-day" aria-label="May 5, 2025" tabindex="-1">5</span><span class="flatpickr-day" aria-label="May 6, 2025" tabindex="-1">6</span><span class="flatpickr-day" aria-label="May 7, 2025" tabindex="-1">7</span><span class="flatpickr-day" aria-label="May 8, 2025" tabindex="-1">8</span><span class="flatpickr-day" aria-label="May 9, 2025" tabindex="-1">9</span><span class="flatpickr-day" aria-label="May 10, 2025" tabindex="-1">10</span><span class="flatpickr-day" aria-label="May 11, 2025" tabindex="-1">11</span><span class="flatpickr-day" aria-label="May 12, 2025" tabindex="-1">12</span><span class="flatpickr-day" aria-label="May 13, 2025" tabindex="-1">13</span><span class="flatpickr-day" aria-label="May 14, 2025" tabindex="-1">14</span><span class="flatpickr-day" aria-label="May 15, 2025" tabindex="-1">15</span><span class="flatpickr-day" aria-label="May 16, 2025" tabindex="-1">16</span><span class="flatpickr-day" aria-label="May 17, 2025" tabindex="-1">17</span><span class="flatpickr-day" aria-label="May 18, 2025" tabindex="-1">18</span><span class="flatpickr-day" aria-label="May 19, 2025" tabindex="-1">19</span><span class="flatpickr-day" aria-label="May 20, 2025" tabindex="-1">20</span><span class="flatpickr-day" aria-label="May 21, 2025" tabindex="-1">21</span><span class="flatpickr-day" aria-label="May 22, 2025" tabindex="-1">22</span><span class="flatpickr-day" aria-label="May 23, 2025" tabindex="-1">23</span><span class="flatpickr-day" aria-label="May 24, 2025" tabindex="-1">24</span><span class="flatpickr-day" aria-label="May 25, 2025" tabindex="-1">25</span><span class="flatpickr-day" aria-label="May 26, 2025" tabindex="-1">26</span><span class="flatpickr-day" aria-label="May 27, 2025" tabindex="-1">27</span><span class="flatpickr-day today" aria-label="May 28, 2025" aria-current="date" tabindex="-1">28</span><span class="flatpickr-day" aria-label="May 29, 2025" tabindex="-1">29</span><span class="flatpickr-day" aria-label="May 30, 2025" tabindex="-1">30</span><span class="flatpickr-day" aria-label="May 31, 2025" tabindex="-1">31</span><span class="flatpickr-day nextMonthDay" aria-label="June 1, 2025" tabindex="-1">1</span><span class="flatpickr-day nextMonthDay" aria-label="June 2, 2025" tabindex="-1">2</span><span class="flatpickr-day nextMonthDay" aria-label="June 3, 2025" tabindex="-1">3</span><span class="flatpickr-day nextMonthDay" aria-label="June 4, 2025" tabindex="-1">4</span><span class="flatpickr-day nextMonthDay" aria-label="June 5, 2025" tabindex="-1">5</span><span class="flatpickr-day nextMonthDay" aria-label="June 6, 2025" tabindex="-1">6</span><span class="flatpickr-day nextMonthDay" aria-label="June 7, 2025" tabindex="-1">7</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span class="absolute text-gray-500 -translate-y-1/2 right-3 top-1/2 dark:text-gray-400">
                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="w-full px-2.5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Category
                            </label>
                            <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                <select class="w-full px-4 py-3 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg appearance-none dark:bg-dark-900 h-11 bg-none shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" :class="isOptionSelected &amp;&amp; 'text-gray-500 dark:text-gray-400'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        Category 1
                                    </option>
                                    <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        Category 2
                                    </option>
                                    <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        Category 3
                                    </option>
                                </select>
                                <span class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="w-full px-2.5">
                            <h4 class="pb-4 text-base font-medium text-gray-800 border-b border-gray-200 dark:border-gray-800 dark:text-white/90">
                                Address
                            </h4>
                        </div>

                        <div class="w-full px-2.5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Street
                            </label>
                            <input type="text" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                City
                            </label>
                            <input type="text" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                State
                            </label>
                            <input type="text" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Post Code
                            </label>
                            <input type="text" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Country
                            </label>
                            <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                <select class="w-full px-4 py-3 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg appearance-none dark:bg-dark-900 h-11 bg-none shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" :class="isOptionSelected &amp;&amp; 'text-gray-500 dark:text-gray-400'" @change="isOptionSelected = true">
                                    <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        --Select Country--
                                    </option>
                                    <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        USA
                                    </option>
                                    <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                        Canada
                                    </option>
                                </select>
                                <span class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="w-full px-2.5">
                            <div class="flex items-center gap-3" x-data="{ isChecked: '' }">
                                <label class="text-sm font-medium text-gray-800 dark:text-white/90">
                                    Membership:
                                </label>

                                <div class="flex flex-wrap items-center gap-4">
                                    <div>
                                        <label :class="isChecked === 'Free' ? 'text-gray-700 dark:text-gray-400' : 'text-gray-500 dark:text-gray-400'" class="relative flex items-center gap-3 text-sm font-medium cursor-pointer select-none text-gray-500 dark:text-gray-400">
                                            <input class="sr-only" type="radio" name="roleSelect" id="Free" @change="isChecked = 'Free'">
                                            <span :class="isChecked === 'Free' ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="flex h-5 w-5 items-center justify-center rounded-full border-[1.25px] bg-transparent border-gray-300 dark:border-gray-700">
                                                <span :class="isChecked === 'Free' ? 'block' : 'hidden'" class="w-2 h-2 bg-white rounded-full hidden"></span>
                                            </span>
                                            Free
                                        </label>
                                    </div>

                                    <div>
                                        <label :class="isChecked === 'Paid' ? 'text-gray-700 dark:text-gray-400' : 'text-gray-500 dark:text-gray-400'" class="relative flex items-center gap-3 text-sm font-medium cursor-pointer select-none text-gray-500 dark:text-gray-400">
                                            <input class="sr-only" type="radio" name="roleSelect" id="Paid" @change="isChecked = 'Paid'">
                                            <span :class="isChecked === 'Paid' ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="flex h-5 w-5 items-center justify-center rounded-full border-[1.25px] bg-transparent border-gray-300 dark:border-gray-700">
                                                <span :class="isChecked === 'Paid' ? 'block' : 'hidden'" class="w-2 h-2 bg-white rounded-full hidden"></span>
                                            </span>
                                            Paid
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="w-full px-2.5">
                            <div class="flex items-center gap-3 mt-1">
                                <button type="submit" class="flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 hover:bg-brand-600">
                                    Save Changes
                                </button>

                                <button class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Example Form with Icons
                    </h3>
                </div>
                <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
                    <form>
                        <div class="-mx-2.5 flex flex-wrap gap-y-5">
                            <div class="w-full px-2.5">
                                <div class="relative">
                                    <span class="absolute text-gray-500 -translate-y-1/2 left-4 top-1/2 dark:text-gray-400">
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.0254 6.17845C8.0254 4.90629 9.05669 3.875 10.3289 3.875C11.601 3.875 12.6323 4.90629 12.6323 6.17845C12.6323 7.45061 11.601 8.48191 10.3289 8.48191C9.05669 8.48191 8.0254 7.45061 8.0254 6.17845ZM10.3289 2.375C8.22827 2.375 6.5254 4.07786 6.5254 6.17845C6.5254 8.27904 8.22827 9.98191 10.3289 9.98191C12.4294 9.98191 14.1323 8.27904 14.1323 6.17845C14.1323 4.07786 12.4294 2.375 10.3289 2.375ZM8.92286 11.03C5.7669 11.03 3.2085 13.5884 3.2085 16.7444V17.0333C3.2085 17.4475 3.54428 17.7833 3.9585 17.7833C4.37271 17.7833 4.7085 17.4475 4.7085 17.0333V16.7444C4.7085 14.4169 6.59533 12.53 8.92286 12.53H11.736C14.0635 12.53 15.9504 14.4169 15.9504 16.7444V17.0333C15.9504 17.4475 16.2861 17.7833 16.7004 17.7833C17.1146 17.7833 17.4504 17.4475 17.4504 17.0333V16.7444C17.4504 13.5884 14.8919 11.03 11.736 11.03H8.92286Z" fill=""></path>
                                        </svg>
                                    </span>

                                    <input type="text" placeholder="Username" class="w-full px-4 py-3 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg dark:bg-dark-900 h-11 pl-11 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>
                            </div>

                            <div class="w-full px-2.5">
                                <div class="relative">
                                    <span class="absolute text-gray-500 -translate-y-1/2 left-4 top-1/2 dark:text-gray-400">
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.0415 7.06206V14.375C3.0415 14.6511 3.26536 14.875 3.5415 14.875H16.4582C16.7343 14.875 16.9582 14.6511 16.9582 14.375V7.06245L11.1441 11.1168C10.4568 11.5961 9.54348 11.5961 8.85614 11.1168L3.0415 7.06206ZM16.9582 5.19262C16.9582 5.19341 16.9582 5.1942 16.9582 5.19498V5.20026C16.957 5.22216 16.9458 5.24239 16.9277 5.25501L10.2861 9.88638C10.1143 10.0062 9.88596 10.0062 9.71412 9.88638L3.0723 5.25485C3.05318 5.24151 3.04178 5.21967 3.04177 5.19636C3.04176 5.15695 3.0737 5.125 3.1131 5.125H16.8869C16.925 5.125 16.9562 5.15494 16.9582 5.19262ZM18.4582 5.21428V14.375C18.4582 15.4796 17.5627 16.375 16.4582 16.375H3.5415C2.43693 16.375 1.5415 15.4796 1.5415 14.375V5.19498C1.5415 5.1852 1.54169 5.17546 1.54206 5.16577C1.55834 4.31209 2.25546 3.625 3.1131 3.625H16.8869C17.7546 3.625 18.4582 4.32843 18.4583 5.19622C18.4583 5.20225 18.4582 5.20826 18.4582 5.21428Z" fill=""></path>
                                        </svg>
                                    </span>
                                    <input type="email" placeholder="Email address" class="w-full px-4 py-3 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg dark:bg-dark-900 h-11 pl-11 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>
                            </div>

                            <div class="w-full px-2.5">
                                <div class="relative">
                                    <span class="absolute text-gray-500 -translate-y-1/2 left-4 top-1/2 dark:text-gray-400">
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10.6252 13.9582C10.6252 13.613 10.3453 13.3332 10.0002 13.3332C9.65498 13.3332 9.37516 13.613 9.37516 13.9582V15.2082C9.37516 15.5533 9.65498 15.8332 10.0002 15.8332C10.3453 15.8332 10.6252 15.5533 10.6252 15.2082V13.9582Z" fill="#667085"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 1.6665C7.58392 1.6665 5.62516 3.62526 5.62516 6.0415V7.604H4.5835C3.54796 7.604 2.7085 8.44347 2.7085 9.479V16.4578C2.7085 17.4933 3.54796 18.3328 4.5835 18.3328H15.4168C16.4524 18.3328 17.2918 17.4933 17.2918 16.4578V9.479C17.2918 8.44347 16.4524 7.604 15.4168 7.604H14.3752V6.0415C14.3752 3.62526 12.4164 1.6665 10.0002 1.6665ZM13.1252 6.0415V7.604H6.87516V6.0415C6.87516 4.31561 8.27427 2.9165 10.0002 2.9165C11.7261 2.9165 13.1252 4.31561 13.1252 6.0415ZM4.5835 8.854C4.23832 8.854 3.9585 9.13383 3.9585 9.479V16.4578C3.9585 16.8029 4.23832 17.0828 4.5835 17.0828H15.4168C15.762 17.0828 16.0418 16.8029 16.0418 16.4578V9.479C16.0418 9.13383 15.762 8.854 15.4168 8.854H4.5835Z" fill=""></path>
                                        </svg>
                                    </span>

                                    <input type="password" placeholder="Password" class="w-full px-4 py-3 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg dark:bg-dark-900 h-11 pl-11 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>
                            </div>

                            <div class="w-full px-2.5">
                                <div class="relative">
                                    <span class="absolute text-gray-500 -translate-y-1/2 left-4 top-1/2 dark:text-gray-400">
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10.6252 13.9582C10.6252 13.613 10.3453 13.3332 10.0002 13.3332C9.65498 13.3332 9.37516 13.613 9.37516 13.9582V15.2082C9.37516 15.5533 9.65498 15.8332 10.0002 15.8332C10.3453 15.8332 10.6252 15.5533 10.6252 15.2082V13.9582Z" fill="#667085"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 1.6665C7.58392 1.6665 5.62516 3.62526 5.62516 6.0415V7.604H4.5835C3.54796 7.604 2.7085 8.44347 2.7085 9.479V16.4578C2.7085 17.4933 3.54796 18.3328 4.5835 18.3328H15.4168C16.4524 18.3328 17.2918 17.4933 17.2918 16.4578V9.479C17.2918 8.44347 16.4524 7.604 15.4168 7.604H14.3752V6.0415C14.3752 3.62526 12.4164 1.6665 10.0002 1.6665ZM13.1252 6.0415V7.604H6.87516V6.0415C6.87516 4.31561 8.27427 2.9165 10.0002 2.9165C11.7261 2.9165 13.1252 4.31561 13.1252 6.0415ZM4.5835 8.854C4.23832 8.854 3.9585 9.13383 3.9585 9.479V16.4578C3.9585 16.8029 4.23832 17.0828 4.5835 17.0828H15.4168C15.762 17.0828 16.0418 16.8029 16.0418 16.4578V9.479C16.0418 9.13383 15.762 8.854 15.4168 8.854H4.5835Z" fill=""></path>
                                        </svg>
                                    </span>

                                    <input type="password" placeholder="Confirm Password" class="w-full px-4 py-3 text-sm text-gray-800 bg-transparent border border-gray-300 rounded-lg dark:bg-dark-900 h-11 pl-11 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>
                            </div>

                            <div class="w-full px-2.5">
                                <div class="flex flex-col justify-between gap-5 xl:flex-row xl:items-center">
                                    <div x-data="{ checkboxToggle: false }">
                                        <label for="checkboxLabelOne" class="flex items-center text-sm font-medium text-gray-700 cursor-pointer select-none dark:text-gray-400">
                                            <div class="relative">
                                                <input type="checkbox" id="checkboxLabelOne" class="sr-only" @change="checkboxToggle = !checkboxToggle">
                                                <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] bg-transparent border-gray-300 dark:border-gray-700">
                                                    <span :class="checkboxToggle ? '' : 'opacity-0'" class="opacity-0">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>
                                            Remember me
                                        </label>
                                    </div>

                                    <button type="submit" class="flex items-center justify-center w-full gap-2 px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 hover:bg-brand-600 xl:w-auto">
                                        Create Account

                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z" fill=""></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


        </div>
    </div>
    <!-- ====== Form Layouts Section End -->
</div>
@endsection