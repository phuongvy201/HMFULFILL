@extends('layouts.admin')

@section('title', 'Import User Pricing')

@section('content-admin')
<div class="p-6">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Import User Pricing</h3>
            </div>
            <div class="p-6">
                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Cấu trúc file Excel -->
                    <div>
                        <h5 class="text-lg font-medium text-gray-900 mb-4">📋 Cấu trúc file Excel:</h5>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Cột</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Tên</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Mô tả</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">A</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">User ID</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">ID của user (số). Nhiều ID: 123,456,789 hoặc 123;456;789</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">B</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">Product Name</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Tên sản phẩm (để tham khảo)</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">C</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">Variant SKU</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">SKU của variant</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">D</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">TikTok 1st Price</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Giá TikTok 1st (số, để 0 nếu không áp dụng)</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">E</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">TikTok Next Price</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Giá TikTok Next (số, để 0 nếu không áp dụng)</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">F</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">Seller 1st Price</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Giá Seller 1st (số, để 0 nếu không áp dụng)</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">G</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">Seller Next Price</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Giá Seller Next (số, để 0 nếu không áp dụng)</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">H</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">Currency</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">USD, VND, GBP (mặc định: USD)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Import File -->
                    <div>
                        <h5 class="text-lg font-medium text-gray-900 mb-4">📥 Import File:</h5>
                        <form action="{{ route('admin.user-pricing.import.post') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">Chọn file Excel:</label>
                                <input type="file"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    id="excel_file"
                                    name="excel_file"
                                    accept=".xlsx,.xls"
                                    required>
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Import
                            </button>
                        </form>

                        <div class="mt-6">
                            <h5 class="text-lg font-medium text-gray-900 mb-4">📄 Download Template:</h5>
                            <a href="{{ route('admin.user-pricing.template') }}"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download Template
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <h5 class="text-lg font-medium text-gray-900 mb-4">📝 Ví dụ dữ liệu:</h5>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">User ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Product Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Variant SKU</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">TikTok 1st</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">TikTok Next</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Seller 1st</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Seller Next</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Currency</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">123,456,789</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">Product A</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">SKU001</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">12.50</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">15.00</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">18.75</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">20.00</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">USD</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">123;456</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">Product B</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">SKU002</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">10.00</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">13.50</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">16.25</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">18.50</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">USD</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">456</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">Product C</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">SKU003</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">8.75</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">0</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">12.00</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">0</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">USD</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h6 class="text-sm font-medium text-blue-900 mb-2">💡 Lưu ý:</h6>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• User ID phải tồn tại trong hệ thống (có thể nhập nhiều ID: 123,456,789)</li>
                        <li>• Variant SKU phải tồn tại trong database</li>
                        <li>• Mỗi cột giá tương ứng với một shipping method: TikTok 1st, TikTok Next, Seller 1st, Seller Next</li>
                        <li>• Để 0 nếu không muốn áp dụng giá cho method đó</li>
                        <li>• Ít nhất một giá phải lớn hơn 0</li>
                        <li>• Nếu user đã có giá cho method này, sẽ được cập nhật</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection