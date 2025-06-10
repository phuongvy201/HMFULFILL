@extends('layouts.customer')

@section('title', 'API Token Management')

@section('content-customer')
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-xl font-semibold text-gray-700 dark:text-gray-400">Your API Token</h4>
        </div>

        @if (session('message'))
        <div class="mb-4 flex rounded-lg border border-green-100 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-400/10 dark:text-green-400">
            <svg class="mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            {{ session('message') }}
        </div>
        @endif

        <div class="mb-6">
            <label class="mb-2.5 block font-medium text-gray-700 dark:text-gray-400">Current API Token:</label>
            <div class="flex items-center gap-4">
                <div class="relative flex-1">
                    <input type="text"
                        id="apiToken"
                        class="w-full rounded-lg border border-gray-200 px-4 py-2.5 pl-4 pr-10 text-sm focus:border-primary focus:ring-primary dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        value="{{ $user->api_token }}"
                        readonly>
                    <button type="button"
                        onclick="copyToken()"
                        id="copyButton"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                        </svg>
                    </button>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                This token is used to authenticate your API requests. Please keep this token secure.
            </p>
        </div>

        <div class="flex items-center gap-4">
            <form action="{{ route('api-token.regenerate') }}" method="POST" class="inline-block">
                @csrf
                <button type="submit"
                    style="background-color: #F7961D;"
                    onclick="return confirm('Are you sure you want to regenerate the token? The old token will no longer be valid.');"
                    class="inline-flex items-center gap-1.5 rounded-lg border-inherit  px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-warning-600 dark:bg-warning-600 dark:hover:bg-warning-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Regenerate Token
                </button>
            </form>
        </div>
    </div>


</div>

@push('scripts')
<script>
    async function copyToken() {
        const tokenInput = document.getElementById('apiToken');
        const copyButton = document.getElementById('copyButton');

        try {
            // Try using modern Clipboard API
            await navigator.clipboard.writeText(tokenInput.value);

            // Change button icon and text
            copyButton.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        `;
            copyButton.classList.add('text-green-500');

            // Show notification
            showNotification('Token has been copied!', 'success');

            // Reset button state after 2 seconds
            setTimeout(() => {
                copyButton.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
            `;
                copyButton.classList.remove('text-green-500');
            }, 2000);
        } catch (err) {
            // Fallback for older browsers
            tokenInput.select();
            document.execCommand('copy');
            showNotification('Token has been copied!', 'success');
        }
    }

    function showNotification(message, type = 'success') {
        // Remove existing notification if any
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Create new notification
        const notification = document.createElement('div');
        notification.className = `notification fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-y-0 opacity-100 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
        notification.textContent = message;

        // Add to DOM
        document.body.appendChild(notification);

        // Fade out animation
        setTimeout(() => {
            notification.classList.add('translate-y-2', 'opacity-0');
        }, 1500);

        // Remove after animation completes
        setTimeout(() => {
            notification.remove();
        }, 2000);
    }
</script>
@endpush
@endsection