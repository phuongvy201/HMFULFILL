@extends('layouts.customer')

@section('title', 'Wallet')

@section('content-customer')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `Wallet`}">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2
                class="text-xl font-semibold text-gray-800 dark:text-white/90"
                x-text="pageName"></h2>

            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                            href="/customer/dashboard">
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
    <div class="flex flex-col items-center px-4 py-5 xl:px-6 xl:py-6">
        <div class="flex flex-col w-full gap-5 sm:justify-between xl:flex-row xl:items-center">
            <div class="flex flex-wrap items-center gap-x-1 gap-y-2 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900">
                <p>Overview of transactions and transaction history and can add more money to your wallet here
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 xl:justify-end">
                <div x-data="topupForm" x-init="init()">
                    <!-- Top Up Button -->
                    <button class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600" @click="isModalOpen = !isModalOpen">
                        Top Up
                    </button>

                    <!-- Modal -->
                    <div x-show="isModalOpen" class="fixed inset-0 flex mt-20 items-center justify-center p-5 overflow-y-auto modal z-40" style="display: none;">
                        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
                        <div @click.outside="isModalOpen = false" class="relative w-full max-w-[800px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
                            <!-- Close Button -->
                            <button @click="isModalOpen = false" class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""></path>
                                </svg>
                            </button>

                            <div class="p-6 lg:p-8">
                                <h4 class="font-semibold text-gray-800 mb-6 text-xl dark:text-white/90">
                                    Top Up
                                </h4>

                                <!-- Payment Method Tabs -->
                                <div class="flex border-b mb-6">
                                    <button
                                        @click="selectTab('Bank Vietnam')"
                                        :class="{'border-b-2 border-green-500 text-green-500': activeTab === 'Bank Vietnam'}"
                                        class="px-4 py-2 text-sm font-medium">Bank Vietnam</button>
                                    <button
                                        @click="selectTab('Payoneer')"
                                        :class="{'border-b-2 border-green-500 text-green-500': activeTab === 'Payoneer'}"
                                        class="px-4 py-2 text-sm font-medium">Payoneer</button>
                                    <button
                                        @click="selectTab('PingPong')"
                                        :class="{'border-b-2 border-green-500 text-green-500': activeTab === 'PingPong'}"
                                        class="px-4 py-2 text-sm font-medium">PingPong</button>
                                    <button
                                        @click="selectTab('LianLian')"
                                        :class="{'border-b-2 border-green-500 text-green-500': activeTab === 'LianLian'}"
                                        class="px-4 py-2 text-sm font-medium">LianLian</button>
                                    <button
                                        @click="selectTab('Worldfirst')"
                                        :class="{'border-b-2 border-green-500 text-green-500': activeTab === 'Worldfirst'}"
                                        class="px-4 py-2 text-sm font-medium">Worldfirst</button>
                                </div>

                                <!-- Min Amount Info -->
                                <div class="bg-blue-50 p-4 rounded-lg mb-6">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-700">
                                            Min top up: $1.00 ↔ {{ number_format($usdToVndRate, 2, '.', ',') }}đ
                                            <span class="text-blue-500">(Rate: $1.00 ↔ {{ number_format($usdToVndRate, 2, '.', ',') }}đ)</span>
                                            Or enter the money
                                        </span>
                                    </div>
                                </div>

                                <!-- Bank Vietnam Tab -->
                                <div x-show="activeTab === 'Bank Vietnam'">
                                    <div class="space-y-4 my-4">
                                        <div class="grid grid-cols-3 gap-0 border rounded-lg overflow-hidden">
                                            <div class="border-r p-3">
                                                <span class="font-medium text-gray-900 dark:text-white">MB Bank</span>
                                            </div>
                                            <div class="border-r p-3 flex items-center justify-between">
                                                <span class="font-medium text-gray-900 dark:text-white">8266566666</span>
                                                <button @click="copyToClipboard('8266566666', $event)"
                                                    class="text-blue-500 hover:text-blue-600 transition-colors"
                                                    title="Copy to clipboard">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="p-3">
                                                <span class="font-medium text-gray-900 dark:text-white">CONG TY TNHH HM FULFILL</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-8">
                                        <div class="w-1/3">
                                            <img src="{{ asset('assets/images/qr-code.jpg') }}" alt="QR Code" class="w-full rounded-lg mb-6">
                                        </div>
                                        <div class="w-2/3">
                                            <form @submit.prevent="submitTopup" enctype="multipart/form-data">
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount (USD)</label>
                                                        <input name="amount" type="number" x-model="form.amount" class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90" required>
                                                        <p class="text-sm text-gray-500">* Minimum top up amount is $10.00</p>
                                                    </div>
                                                    @error('amount')
                                                    <div class="text-red-500 text-sm">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                    <div id="transaction-code-input">
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Transfer Description</label>
                                                        <div class="relative">
                                                            <button @click="copyToClipboard(transactionCode, $event)" type="button" class="absolute top-1/2 right-0 inline-flex -translate-y-1/2 items-center gap-1 border-l py-3 pr-3 pl-3.5 text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400">
                                                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20"></svg>
                                                                <div>Copy</div>
                                                            </button>
                                                            <input name="transaction_code" type="text" x-model="form.transaction_code" readonly class="h-11 w-full rounded-lg border bg-transparent py-3 pr-[90px] pl-4 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Proof Image</label>
                                                        <input name="proof_image" type="file" @change="handleFileUpload" class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90">
                                                    </div>
                                                </div>
                                                <button type="submit"
                                                    id="submit-topup"
                                                    class="px-6 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600"
                                                    :disabled="isLoading">
                                                    <span x-show="!isLoading">Submit</span>
                                                    <span x-show="isLoading">Loading...</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payoneer Tab -->
                                <div x-show="activeTab === 'Payoneer'" class="space-y-6 my-6">
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-3 gap-0 border rounded-lg overflow-hidden">
                                            <div class="border-r p-3">
                                                <span class="font-medium text-gray-900 dark:text-white">Payoneer</span>
                                            </div>
                                            <div class="border-r p-3 flex items-center justify-between">
                                                <span class="font-medium text-gray-900 dark:text-white">admin@bluprinter.com</span>
                                                <button @click="copyToClipboard('admin@bluprinter.com')"
                                                    class="text-blue-500 hover:text-blue-600 transition-colors"
                                                    title="Copy to clipboard">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="p-3">
                                                <span class="font-medium text-gray-900 dark:text-white">Bluprinter Admin</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-8">
                                        <div class="w-full">
                                            <form @submit.prevent="submitTopup" enctype="multipart/form-data">
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount (USD)</label>
                                                        <input name="amount" type="number" x-model="form.amount" class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90" required>
                                                        <p class="text-sm text-gray-500">* Minimum top up amount is $1.00</p>
                                                    </div>
                                                    @error('amount')
                                                    <div class="text-red-500 text-sm">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                    <div id="transaction-code-input">
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Transfer Description</label>
                                                        <div class="relative">
                                                            <button @click="copyToClipboard(transactionCode, $event)" type="button" class="absolute top-1/2 right-0 inline-flex -translate-y-1/2 items-center gap-1 border-l py-3 pr-3 pl-3.5 text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400">
                                                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20"></svg>
                                                                <div>Copy</div>
                                                            </button>
                                                            <input name="transaction_code" type="text" x-model="form.transaction_code" readonly class="h-11 w-full rounded-lg border bg-transparent py-3 pr-[90px] pl-4 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Proof Image</label>
                                                        <input name="proof_image" type="file" @change="handleFileUpload" class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90">
                                                    </div>
                                                </div>
                                                <button type="submit"
                                                    id="submit-topup"
                                                    class="mt-4 px-6 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600"
                                                    :disabled="isLoading">
                                                    <span x-show="!isLoading">Submit</span>
                                                    <span x-show="isLoading">Loading...</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- PingPong Tab -->
                                <div x-show="activeTab === 'PingPong'" class="space-y-6 my-6">
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-3 gap-0 border rounded-lg overflow-hidden">
                                            <div class="border-r p-3">
                                                <span class="font-medium text-gray-900 dark:text-white">PingPong</span>
                                            </div>
                                            <div class="border-r p-3 flex items-center justify-between">
                                                <span class="font-medium text-gray-900 dark:text-white">admin@bluprinter.com</span>
                                                <button @click="copyToClipboard('admin@bluprinter.com')"
                                                    class="text-blue-500 hover:text-blue-600 transition-colors"
                                                    title="Copy to clipboard">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="p-3">
                                                <span class="font-medium text-gray-900 dark:text-white">Bluprinter Admin</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-8">
                                        <div class="w-full">
                                            <form @submit.prevent="submitTopup" enctype="multipart/form-data">
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount (USD)</label>
                                                        <input name="amount" type="number" x-model="form.amount" class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90" required>
                                                        <p class="text-sm text-gray-500">* Minimum top up amount is $1.00</p>
                                                    </div>
                                                    @error('amount')
                                                    <div class="text-red-500 text-sm">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                    <div id="transaction-code-input">
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Transfer Description</label>
                                                        <div class="relative">
                                                            <button @click="copyToClipboard(transactionCode, $event)" type="button" class="absolute top-1/2 right-0 inline-flex -translate-y-1/2 items-center gap-1 border-l py-3 pr-3 pl-3.5 text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400">
                                                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20"></svg>
                                                                <div>Copy</div>
                                                            </button>
                                                            <input name="transaction_code" type="text" x-model="form.transaction_code" readonly class="h-11 w-full rounded-lg border bg-transparent py-3 pr-[90px] pl-4 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Proof Image</label>
                                                        <input name="proof_image" type="file" @change="handleFileUpload" class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90">
                                                    </div>
                                                </div>
                                                <button type="submit"
                                                    id="submit-topup"
                                                    class="mt-4 px-6 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600"
                                                    :disabled="isLoading">
                                                    <span x-show="!isLoading">Submit</span>
                                                    <span x-show="isLoading">Loading...</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- LianLian Tab -->
                                <div x-show="activeTab === 'LianLian'" class="space-y-6 my-6">
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-3 gap-0 border rounded-lg overflow-hidden">
                                            <div class="border-r p-3">
                                                <span class="font-medium text-gray-900 dark:text-white">LianLian Pay</span>
                                            </div>
                                            <div class="border-r p-3 flex items-center justify-between">
                                                <span class="font-medium text-gray-900 dark:text-white">admin@bluprinter.com</span>
                                                <button @click="copyToClipboard('admin@bluprinter.com')"
                                                    class="text-blue-500 hover:text-blue-600 transition-colors"
                                                    title="Copy to clipboard">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="p-3">
                                                <span class="font-medium text-gray-900 dark:text-white">Bluprinter Admin</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-8">
                                        <div class="w-full">
                                            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg" role="alert">
                                                <span class="block sm:inline">Phương thức thanh toán LianLian Pay đang được phát triển. Vui lòng chọn phương thức khác.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Worldfirst Tab -->
                                <div x-show="activeTab === 'Worldfirst'" class="space-y-6 my-6">
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-3 gap-0 border rounded-lg overflow-hidden">
                                            <div class="border-r p-3">
                                                <span class="font-medium text-gray-900 dark:text-white">Worldfirst</span>
                                            </div>
                                            <div class="border-r p-3 flex items-center justify-between">
                                                <span class="font-medium text-gray-900 dark:text-white">admin@bluprinter.com</span>
                                                <button @click="copyToClipboard('admin@bluprinter.com')"
                                                    class="text-blue-500 hover:text-blue-600 transition-colors"
                                                    title="Copy to clipboard">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="p-3">
                                                <span class="font-medium text-gray-900 dark:text-white">Bluprinter Admin</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-8">
                                        <div class="w-full">
                                            <form @submit.prevent="submitTopup" enctype="multipart/form-data">
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount (USD)</label>
                                                        <input name="amount" type="number" x-model="form.amount" class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90" required>
                                                        <p class="text-sm text-gray-500">* Minimum top up amount is $1.00</p>
                                                    </div>
                                                    @error('amount')
                                                    <div class="text-red-500 text-sm">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                    <div id="transaction-code-input">
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Transfer Description</label>
                                                        <div class="relative">
                                                            <button @click="copyToClipboard(transactionCode, $event)" type="button" class="absolute top-1/2 right-0 inline-flex -translate-y-1/2 items-center gap-1 border-l py-3 pr-3 pl-3.5 text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400">
                                                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20"></svg>
                                                                <div>Copy</div>
                                                            </button>
                                                            <input name="transaction_code" type="text" x-model="form.transaction_code" readonly class="h-11 w-full rounded-lg border bg-transparent py-3 pr-[90px] pl-4 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Proof Image</label>
                                                        <input name="proof_image" type="file" @change="handleFileUpload" class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90">
                                                    </div>
                                                </div>
                                                <button type="submit"
                                                    id="submit-topup"
                                                    class="mt-4 px-6 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600"
                                                    :disabled="isLoading">
                                                    <span x-show="!isLoading">Submit</span>
                                                    <span x-show="isLoading">Loading...</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 bg-yellow-50 p-4 rounded-lg space-y-2">
                                    <p class="text-sm text-yellow-700">✓ The system will automatically top up to your wallet if you enter the exact transfer note.</p>
                                    <p class="text-sm text-yellow-700">✓ In case you enter the wrong content of Transfer Note, please contact support.</p>
                                    <p class="text-sm text-yellow-700">✓ Please check the payment details before making a transaction.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4 my-10">
            <!-- Metric Item Start -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                    Total Balance
                </p>

                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ number_format($totalBalance, 0, ',', '.') }} USD
                        </h4>
                    </div>


                </div>
            </div>
            <!-- Metric Item End -->

            <!-- Metric Item Start -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                    Available Balance
                </p>

                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ number_format($availableBalance, 0, ',', '.') }} USD
                        </h4>
                    </div>

                </div>
            </div>
            <!-- Metric Item End -->

            <!-- Metric Item Start -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Hold Amount</p>

                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ number_format($holdAmount, 0, ',', '.') }} USD
                        </h4>
                    </div>


                </div>
            </div>
            <!-- Metric Item End -->

            <!-- Metric Item Start -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Credit</p>

                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ number_format($creditAmount, 0, ',', '.') }} USD
                        </h4>
                    </div>


                </div>
            </div>
            <!-- Metric Item End -->
        </div>
        <div class="col-span-12">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full">
                        <thead>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Transaction Code
                                    </p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Type
                                    </p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Method
                                    </p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Amount
                                    </p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Status
                                    </p>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Date
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="px-6 py-3.5">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                        {{ $transaction->transaction_code }}
                                    </p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ ucfirst($transaction->type) }}
                                    </p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                        @if($transaction->type == 'topup')
                                        {{ $transaction->method }}
                                        @endif
                                    </p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="text-theme-sm {{ $transaction->type == 'topup' ? 'text-success-600' : 'text-error-500' }}">
                                        {{ $transaction->type == 'topup' ? '+' : '-' }} {{ number_format($transaction->amount, 2) }} USD
                                    </p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="text-theme-sm {{ $transaction->status == 'approved' ? 'text-success-600' : ($transaction->status == 'rejected' ? 'text-error-500' : 'text-warning-500') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </p>
                                </td>
                                <td class="px-6 py-3.5">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                        {{ $transaction->created_at->format('d M Y H:i') }}
                                    </p>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">No transactions found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 px-6 py-3 border-t border-gray-100 dark:border-gray-800">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function generateTransactionCode() {
            return `TXN-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('topupForm', () => ({
                isModalOpen: false,
                activeTab: 'Bank Vietnam',
                isLoading: false,
                transactionCode: '{{ auth()->user()->transaction_code ?? "N/A" }}',
                form: {
                    amount: '',
                    transaction_code: '',
                    method: 'Bank Vietnam',
                    proof_image: null
                },

                selectTab(tab) {
                    this.activeTab = tab;
                    this.form.method = tab;
                    console.log('Tab selected:', tab, 'Method set to:', this.form.method);
                },

                copyToClipboard(text, event) {
                    navigator.clipboard.writeText(text).then(() => {
                        const copyButton = event.currentTarget;
                        const originalTitle = copyButton.title;
                        copyButton.title = 'Copied!';
                        setTimeout(() => {
                            copyButton.title = originalTitle;
                        }, 2000);
                    }).catch(err => {
                        console.error('Failed to copy text: ', err);
                    });
                },

                handleFileUpload(e) {
                    this.form.proof_image = e.target.files[0];
                },

                async submitTopup() {
                    console.log('Submitting with method:', this.form.method);
                    if (!this.form.amount) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Please enter the amount.',
                        });
                        return;
                    }

                    this.isLoading = true;
                    const formData = new FormData();
                    formData.append('amount', this.form.amount);
                    formData.append('transaction_code', this.form.transaction_code);
                    formData.append('proof_image', this.form.proof_image);
                    formData.append('method', this.form.method);

                    try {
                        const response = await fetch('/customer/wallet/topup', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Top up successfully!',
                            }).then(() => {
                                this.isModalOpen = false;
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred: ' + result.message,
                            }).then(() => {
                                this.isModalOpen = true;
                            });
                        }
                    } catch (error) {
                        console.error('Error sending top up:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred: ' + error.message,
                        }).then(() => {
                            this.isModalOpen = true;
                        });
                    } finally {
                        this.isLoading = false;
                    }
                },

                init() {
                    this.transactionCode = generateTransactionCode();
                    this.form.transaction_code = this.transactionCode;
                    this.form.method = this.activeTab;
                    console.log('Initialized with method:', this.form.method);
                }
            }));
        });
    </script>


    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            z-index: 50;
        }

        .modal-content {
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
        }
    </style>

    @endsection