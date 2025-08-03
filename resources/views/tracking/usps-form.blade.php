@extends('layouts.app')

@section('title', 'USPS Package Tracking')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">📦 USPS Package Tracking</h1>

        <!-- Single Tracking Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">🔍 Track Single Package</h2>
            <form id="singleTrackingForm" class="space-y-4">
                @csrf
                <div>
                    <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Tracking Number
                    </label>
                    <input type="text"
                        id="tracking_number"
                        name="tracking_number"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter USPS tracking number"
                        required>
                </div>
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Track Package
                </button>
            </form>
        </div>

        <!-- Multiple Tracking Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">📋 Track Multiple Packages (Max 35)</h2>
            <form id="multipleTrackingForm" class="space-y-4">
                @csrf
                <div>
                    <label for="tracking_numbers" class="block text-sm font-medium text-gray-700 mb-2">
                        Tracking Numbers (one per line)
                    </label>
                    <textarea id="tracking_numbers"
                        name="tracking_numbers"
                        rows="5"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter tracking numbers, one per line"
                        required></textarea>
                </div>
                <button type="submit"
                    class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Track Multiple Packages
                </button>
            </form>
        </div>

        <!-- Results Section -->
        <div id="results" class="bg-white rounded-lg shadow-md p-6 hidden">
            <h2 class="text-xl font-semibold mb-4">📊 Tracking Results</h2>
            <div id="resultsContent"></div>
        </div>

        <!-- Loading Spinner -->
        <div id="loading" class="hidden text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Đang kiểm tra tracking information...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const singleForm = document.getElementById('singleTrackingForm');
        const multipleForm = document.getElementById('multipleTrackingForm');
        const results = document.getElementById('results');
        const resultsContent = document.getElementById('resultsContent');
        const loading = document.getElementById('loading');

        // Single tracking form
        singleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const trackingNumber = document.getElementById('tracking_number').value.trim();

            if (!trackingNumber) {
                alert('Vui lòng nhập tracking number');
                return;
            }

            showLoading();
            trackPackage('/customer/tracking/single', {
                tracking_number: trackingNumber
            });
        });

        // Multiple tracking form
        multipleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const trackingNumbersText = document.getElementById('tracking_numbers').value.trim();

            if (!trackingNumbersText) {
                alert('Vui lòng nhập tracking numbers');
                return;
            }

            const trackingNumbers = trackingNumbersText.split('\n')
                .map(num => num.trim())
                .filter(num => num.length > 0);

            if (trackingNumbers.length === 0) {
                alert('Vui lòng nhập ít nhất một tracking number');
                return;
            }

            if (trackingNumbers.length > 35) {
                alert('Chỉ có thể track tối đa 35 packages một lần');
                return;
            }

            showLoading();
            trackPackage('/customer/tracking/multiple', {
                tracking_numbers: trackingNumbers
            });
        });

        function showLoading() {
            loading.classList.remove('hidden');
            results.classList.add('hidden');
        }

        function hideLoading() {
            loading.classList.add('hidden');
        }

        function trackPackage(url, data) {
            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    hideLoading();
                    displayResults(result);
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    displayError('Có lỗi xảy ra khi kiểm tra tracking information');
                });
        }

        function displayResults(result) {
            results.classList.remove('hidden');

            if (!result.success) {
                resultsContent.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                ${result.error || 'Unknown error occurred'}
                            </div>
                        </div>
                    </div>
                </div>
            `;
                return;
            }

            let html = '';

            if (result.data && result.data.packages) {
                result.data.packages.forEach(package => {
                    html += `
                    <div class="border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900">
                                📦 ${package.tracking_number}
                            </h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full ${
                                package.track_summary && package.track_summary.toLowerCase().includes('delivered') 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-blue-100 text-blue-800'
                            }">
                                ${package.track_summary ? 'Active' : 'Unknown'}
                            </span>
                        </div>
                        
                        <div class="space-y-2">
                            ${package.track_summary ? `
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700">Summary:</span>
                                    <span class="text-gray-900">${package.track_summary}</span>
                                </div>
                            ` : ''}
                            
                            ${package.expected_delivery_date ? `
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700">Expected Delivery:</span>
                                    <span class="text-gray-900">${package.expected_delivery_date}</span>
                                </div>
                            ` : ''}
                            
                            ${package.expected_delivery_time ? `
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700">Expected Time:</span>
                                    <span class="text-gray-900">${package.expected_delivery_time}</span>
                                </div>
                            ` : ''}
                            
                            ${package.guaranteed_delivery_date ? `
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700">Guaranteed Delivery:</span>
                                    <span class="text-gray-900">${package.guaranteed_delivery_date}</span>
                                </div>
                            ` : ''}
                        </div>
                        
                        ${package.track_details && package.track_details.length > 0 ? `
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">📍 Tracking Details:</h4>
                                <div class="space-y-1">
                                    ${package.track_details.map(detail => `
                                        <div class="text-xs text-gray-600 pl-4 border-l-2 border-gray-200">
                                            ${detail}
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `;
                });
            } else {
                html = `
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">No Data</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                Không có thông tin tracking cho package này.
                            </div>
                        </div>
                    </div>
                </div>
            `;
            }

            resultsContent.innerHTML = html;
        }

        function displayError(message) {
            results.classList.remove('hidden');
            resultsContent.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Error</h3>
                        <div class="mt-2 text-sm text-red-700">
                            ${message}
                        </div>
                    </div>
                </div>
            </div>
        `;
        }
    });
</script>
@endsection