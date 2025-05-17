@extends('layouts.admin')

@section('title', 'Add Product')

@section('content-admin')

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <!-- Thông báo thành công hoặc lỗi -->
    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @elseif (session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Breadcrumb Start -->
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
    <!-- Breadcrumb End -->
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid gap-6 mb-6 md:grid-cols-2">
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                <input type="text" id="name" value="{{ old('name') }}" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Product Name" required />
                @error('name')
                <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="categories" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category</label>
                <select id="categories" value="{{ old('category_id') }}" name="category_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected>Choose a category</option>
                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                <select id="status" value="{{ old('status') }}" name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected>Choose a status</option>
                    <option value="1">Active</option>
                    <option value="2">Inactive</option>
                </select>
                @error('status')
                <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="base_price" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Base Price</label>
                <input type="number" id="base_price" step="0.01" value="{{ old('base_price') }}" name="base_price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="0.00" required />
                @error('base_price')
                <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="template_link" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Template Link</label>
                <input type="url" id="template_link" value="{{ old('template_link') }}" name="template_link" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="flowbite.com" required />
                @error('template_link')
                <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div>
    <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
    <textarea id="description" name="description">{{ old('description') }}</textarea>
    @error('description')
    <span class="text-red-500">{{ $message }}</span>
    @enderror
</div>
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Countries</label>

                <div class="flex items-center my-4">
                    <input type="checkbox" value="US" name="fulfillment_locations[0][country_code]" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                    <label for="country-US" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">United States</label>
                </div>
                <div class="flex items-center my-4">
                    <input type="checkbox" value="UK" name="fulfillment_locations[1][country_code]" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                    <label for="country-UK" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">United Kingdom</label>
                </div>
                <div class="flex items-center my-4">
                    <input type="checkbox" value="VN" name="fulfillment_locations[2][country_code]" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                    <label for="country-VN" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Vietnam</label>
                </div>

                @error('fulfillment_locations.*.country_code')
                <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-center w-full">
            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                    </svg>
                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
                </div>
                <input id="dropzone-file" name="images[]" type="file" class="hidden" multiple accept="image/*" onchange="previewImages(event)" />
            </label>
            @error('images.*.image_url')
            <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <!-- Thêm phần preview -->
        <div id="image-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"></div>

        <div class="max-w-full">
            <div class="flex flex-wrap gap-6 mb-4">
                <div class="flex flex-col w-1/2">
                    <label for="optionName" class="text-[13px] font-semibold mb-1 tracking-wide">OPTION NAME</label>
                    <input id="optionName" type="text" class="border border-[#1d9bf0] rounded-md px-3 py-2 text-[14px] font-serif" />
                </div>
                <div class="flex flex-col w-1/2">
                    <label for="values" class="text-[13px] font-semibold mb-1 tracking-wide">VALUES</label>
                    <input id="values" type="text" class="border border-[#1d4ef0] rounded-md px-3 py-2 text-[14px] font-serif" placeholder="Black,Blue,White" />
                </div>
            </div>
            <button type="button" onclick="addOption()" class="bg-[#228b22] text-white text-[14px] font-serif px-5 py-2 rounded-md mb-6">
                ADD OPTION
            </button>

            <div id="optionsContainer" class="mb-4 text-[14px] font-serif">
                <p class="mb-2">Options:</p>
            </div>

            <div class="flex gap-3 mb-4">
                <button type="button" onclick="setBulkPrice()" class="bg-[#2563eb] text-white rounded-md px-5 py-2 text-[14px] font-serif">
                    Set Bulk Price
                </button>
                <button type="button" onclick="setBulkSKU()" class="bg-[#2563eb] text-white rounded-md px-5 py-2 text-[14px] font-serif">
                    Set Bulk SKU
                </button>
            </div>

            <div id="variantsTable" class="overflow-x-auto border border-black rounded-md max-w-full">
                <!-- Table will be dynamically generated here -->
            </div>
        </div>

        <!-- Modal Set Bulk Price -->
        <div id="bulkPriceModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Set Bulk Price</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ship by tiktok 1 item</label>
                            <input type="number" step="0.01" id="bulkPrice1" name="bulkPrice1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ship by tiktok từ 2 item</label>
                            <input type="number" step="0.01" id="bulkPrice2" name="bulkPrice2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ship by seller 1 item</label>
                            <input type="number" step="0.01" id="bulkPrice3" name="bulkPrice3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ship by seller từ 2 item</label>
                            <input type="number" step="0.01" id="bulkPrice4" name="bulkPrice4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeBulkPriceModal()" class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="button" onclick="applyBulkPrice()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Set Bulk SKU -->
        <div id="bulkSKUModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Set Bulk SKU</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SKU</label>
                            <input type="text" id="bulkSKU1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SKU Two fifteen</label>
                            <input type="text" id="bulkSKU2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SKU flash ship</label>
                            <input type="text" id="bulkSKU3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeBulkSKUModal()" class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="button" onclick="applyBulkSKU()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function previewImages(event) {
                const preview = document.getElementById('image-preview');
                preview.innerHTML = ''; // Xóa preview cũ

                const files = event.target.files;

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (!file.type.startsWith('image/')) continue;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-full h-48 object-cover rounded-lg';

                        const removeBtn = document.createElement('button');
                        removeBtn.innerHTML = '×';
                        removeBtn.className = 'absolute top-0 right-0 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center cursor-pointer hover:bg-red-600';
                        removeBtn.onclick = function() {
                            div.remove();
                        };
                        div.appendChild(img);
                        div.appendChild(removeBtn);
                        preview.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            }

            let options = []; // Bỏ JSON old cho options

            let variants = []; // Bỏ JSON old cho variants

            function addOption() {
                const name = document.getElementById('optionName').value;
                const values = document.getElementById('values').value.split(',').map(v => v.trim());

                if (!name || values.length === 0) return;

                options.push({
                    name,
                    values: values.map(value => ({
                        value,
                        selected: false
                    }))
                });

                renderOptions();
                generateVariants();

                // Clear inputs
                document.getElementById('optionName').value = '';
                document.getElementById('values').value = '';
            }

            function toggleValue(optionName, value) {
                // Cập nhật trạng thái selected của option
                const option = options.find(opt => opt.name === optionName);
                if (option) {
                    const valueObj = option.values.find(v => v.value === value);
                    if (valueObj) {
                        valueObj.selected = !valueObj.selected;
                    }
                }

                // Render lại options để cập nhật giao diện
                renderOptions();

                // Cập nhật variants dựa trên các options được chọn
                updateVariantSelection();
            }

            function updateVariantSelection() {
                // Lấy tất cả các options và values đã được chọn
                const selectedOptions = options.map(opt => ({
                    name: opt.name,
                    selectedValues: opt.values.filter(v => v.selected).map(v => v.value)
                })).filter(opt => opt.selectedValues.length > 0);

                // Lấy tất cả các checkbox trong bảng variants
                const checkboxes = document.querySelectorAll('.variant-checkbox');

                checkboxes.forEach(checkbox => {
                    const variantText = checkbox.getAttribute('data-variant');
                    const variantValues = variantText.split('/');

                    // Kiểm tra xem variant có match với tất cả các options được chọn không
                    const shouldBeSelected = selectedOptions.every(opt => {
                        const optIndex = options.findIndex(o => o.name === opt.name);
                        const variantValue = variantValues[optIndex];
                        return opt.selectedValues.includes(variantValue);
                    });

                    checkbox.checked = selectedOptions.length > 0 ? shouldBeSelected : false;
                });
            }

            function renderOptions() {
                const container = document.getElementById('optionsContainer');
                container.innerHTML = '<p class="mb-2">Options:</p>';

                options.forEach((option, optionIndex) => {
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'flex flex-wrap items-center gap-3 mb-2';
                    optionDiv.innerHTML = `
                    
                        <div class="flex items-center gap-2 min-w-[50px]">
                            <span>${option.name}:</span>
                            <button type="button" 
                                onclick="deleteOption(${optionIndex})"
                                class="text-red-600 hover:text-red-800 font-bold"
                            >
                                ×
                            </button>
                        </div>
                        ${option.values.map((valueObj, valueIndex) => `
                            <div class="relative">
                                <button type="button" 
                                    onclick="toggleValue('${option.name}', '${valueObj.value}')"
                                    class="value-btn border border-black rounded-md px-4 py-1 text-[14px] font-serif hover:bg-gray-100 
                                    ${valueObj.selected ? 'bg-[#228b22] text-white' : ''}"
                                    data-option="${option.name}"
                                    data-value="${valueObj.value}">
                                    ${valueObj.value}
                                </button>
                                <button type="button"
                                    onclick="deleteOptionValue(${optionIndex}, ${valueIndex})"
                                    class="absolute -top-2 -right-2 text-red-600 hover:text-red-800 font-bold"
                                >
                                    ×
                                </button>
                            </div>
                        `).join('')}
                    `;
                    container.appendChild(optionDiv);
                });
            }

            function deleteOption(optionIndex) {
                options.splice(optionIndex, 1);
                renderOptions();
                generateVariants();
            }

            function deleteOptionValue(optionIndex, valueIndex) {
                options[optionIndex].values.splice(valueIndex, 1);
                if (options[optionIndex].values.length === 0) {
                    options.splice(optionIndex, 1);
                }
                renderOptions();
                generateVariants();
            }

            function generateVariants() {
                // Tạo tất cả tổ hợp có thể từ tất cả các options và values
                const allOptions = options.map(option => ({
                    name: option.name,
                    values: option.values.map(v => v.value)
                }));

                if (allOptions.length === 0) {
                    variants = [];
                    renderVariantsTable();
                    return;
                }

                function combine(arrays, current = [], index = 0) {
                    if (index === arrays.length) {
                        return [current];
                    }

                    const results = [];
                    for (const value of arrays[index]) {
                        results.push(...combine(arrays, [...current, value], index + 1));
                    }
                    return results;
                }

                const arraysToCombine = allOptions.map(option => option.values);
                const combinations = combine(arraysToCombine);
                variants = combinations.map(combination => ({
                    variant: combination.join('/'),
                    selected: false
                }));

                renderVariantsTable();
            }

            function renderVariantsTable() {
                const table = document.getElementById('variantsTable');
                table.innerHTML = `
                    <div class="relative overflow-x-auto">
                        ${variants.map((variant, index) => {
                            // Tách variant thành mảng các giá trị
                            const variantValues = variant.variant.split('/');
                            
                            // Tạo mảng các cặp option-value
                            const optionValuePairs = options.map((option, optIndex) => ({
                                name: option.name,
                                value: variantValues[optIndex]
                            }));

                            return `
                            <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 flex justify-between items-center border-b">
                                    <div class="flex items-center gap-4">
                                        <input type="checkbox"
                                            name="variants[${index}][selected]"
                                            id="variant_${index}"
                                            data-variant="${variant.variant}"
                                            ${variant.selected ? 'checked' : ''}
                                            class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 variant-checkbox" />
                                        <h3 class="font-medium text-gray-900 dark:text-white text-lg">${variant.variant}</h3>
                                        ${optionValuePairs.map((pair, pairIndex) => `
                                            <input type="hidden" 
                                                name="variants[${index}][attributes][${pairIndex}][name]" 
                                                value="${pair.name}" />
                                            <input type="hidden" 
                                                name="variants[${index}][attributes][${pairIndex}][value]" 
                                                value="${pair.value}" />
                                        `).join('')}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-500">Variant #${index + 1}</span>
                                        <button
                                            type="button"
                                            onclick="deleteVariant(${index})"   
                                            class="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-50">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-5 bg-white dark:bg-gray-800">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3 border-b pb-2">SKU Information</h4>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">SKU</label>
                                                    <input type="text"
                                                        value="{{ old('sku') }}"
                                                        name="variants[${index}][sku]"
                                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                        placeholder="Enter SKU" />
                                                </div>
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">SKU Two fifteen</label>
                                                    <input type="text"
                                                        value="{{ old('twofifteen_sku') }}"
                                                        name="variants[${index}][twofifteen_sku]"
                                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                        placeholder="Enter Two fifteen SKU" />
                                                </div>
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">SKU flash ship</label>
                                                    <input type="text"
                                                        value="{{ old('flashship_sku') }}"
                                                        name="variants[${index}][flashship_sku]"
                                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                        placeholder="Enter Flash ship SKU" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3 border-b pb-2">Ship by TikTok</h4>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">1 item price</label>
                                                    <div class="relative">
                                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                            <span class="text-gray-500">$</span>
                                                        </div>
                                                        <input type="text"
                                                            step="0.01"
                                                            value="{{ old('ship_tiktok_1') }}"
                                                            id="ship_tiktok_1_${index}"
                                                            name="variants[${index}][ship_tiktok_1]"
                                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                            placeholder="0.00" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">2+ items price</label>
                                                    <div class="relative">
                                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                            <span class="text-gray-500">$</span>
                                                        </div>
                                                        <input type="text"
                                                            id="ship_tiktok_2_${index}"
                                                            step="0.01"
                                                            value="{{ old('ship_tiktok_2') }}"
                                                            name="variants[${index}][ship_tiktok_2]"
                                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                            placeholder="0.00" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3 border-b pb-2">Ship by Seller</h4>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">1 item price</label>
                                                    <div class="relative">
                                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                            <span class="text-gray-500">$</span>
                                                        </div>
                                                        <input type="text"
                                                            id="ship_seller_1_${index}"
                                                            step="0.01"
                                                            value="{{ old('ship_seller_1') }}"
                                                            name="variants[${index}][ship_seller_1]"
                                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                            placeholder="0.00" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">2+ items price</label>
                                                    <div class="relative">
                                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                            <span class="text-gray-500">$</span>
                                                        </div>
                                                        <input type="text"
                                                            id="ship_seller_2_${index}"
                                                            value="{{ old('ship_seller_2') }}"
                                                            step="0.01"
                                                            name="variants[${index}][ship_seller_2]"
                                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                            placeholder="0.00" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                        }).join('')}
                    </div>
                `;
            }

            function selectAllVariants(checkbox) {
                const checkboxes = document.querySelectorAll('#variantsTable input[type="checkbox"]');
                checkboxes.forEach(cb => cb.checked = checkbox.checked);
            }

            function setBulkPrice() {
                document.getElementById('bulkPriceModal').classList.remove('hidden');
            }

            function closeBulkPriceModal() {
                document.getElementById('bulkPriceModal').classList.add('hidden');
            }

            function applyBulkPrice() {
                const price1 = document.getElementById('bulkPrice1').value;
                const price2 = document.getElementById('bulkPrice2').value;
                const price3 = document.getElementById('bulkPrice3').value;
                const price4 = document.getElementById('bulkPrice4').value;

                // Get all checked variants
                const checkedRows = document.querySelectorAll('#variantsTable input[type="checkbox"]:checked');

                checkedRows.forEach((checkbox, idx) => {
                    const variantContainer = checkbox.closest('.mb-6');

                    // Update prices for the variant
                    if (price1) variantContainer.querySelector(`#ship_tiktok_1_${idx}`).value = price1;
                    if (price2) variantContainer.querySelector(`#ship_tiktok_2_${idx}`).value = price2;
                    if (price3) variantContainer.querySelector(`#ship_seller_1_${idx}`).value = price3;
                    if (price4) variantContainer.querySelector(`#ship_seller_2_${idx}`).value = price4;
                });

                closeBulkPriceModal();
            }

            function setBulkSKU() {
                document.getElementById('bulkSKUModal').classList.remove('hidden');
            }

            function closeBulkSKUModal() {
                document.getElementById('bulkSKUModal').classList.add('hidden');
            }

            function applyBulkSKU() {
                const sku1 = document.getElementById('bulkSKU1').value;
                const sku2 = document.getElementById('bulkSKU2').value;
                const sku3 = document.getElementById('bulkSKU3').value;

                // Get all checked variants
                const checkedRows = document.querySelectorAll('#variantsTable input[type="checkbox"]:checked');

                checkedRows.forEach(checkbox => {
                    const variantContainer = checkbox.closest('.mb-6');

                    // Update SKUs for the variant
                    const inputs = variantContainer.querySelectorAll('input[type="text"]');
                    if (sku1) inputs[0].value = sku1; // SKU
                    if (sku2) inputs[1].value = sku2; // SKU Two fifteen
                    if (sku3) inputs[2].value = sku3; // SKU flash ship
                });

                closeBulkSKUModal();
            }

            function deleteVariant(index) {
                variants.splice(index, 1);
                renderVariantsTable();
            }

            // Initialize the interface
            renderOptions();
            renderVariantsTable();
        </script>

<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>

<script>
    ClassicEditor
        .create(document.querySelector('#description'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
            height: '300px'
        })
        .catch(error => {
            console.error(error);
        });
</script>

        <button type="submit" class="my-4 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
    </form>
</div>
@endsection