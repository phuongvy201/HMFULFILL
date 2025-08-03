@props(['src', 'alt', 'downloadUrl' => null, 'downloadText' => 'Tải xuống'])

<div class="relative">
    <img src="{{ $src }}"
        class="w-full max-w-xl mx-auto rounded-lg shadow-md object-contain transition-opacity duration-300"
        alt="{{ $alt }}"
        onload="this.style.opacity='1'"
        onerror="this.style.display='none'; this.nextElementSibling.style.display='block'"
        style="opacity: 0;">
    <div class="hidden w-full max-w-xl mx-auto rounded-lg shadow-md bg-gray-100 flex items-center justify-center py-12">
        <div class="text-center">
            <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
            <p class="text-gray-600 mb-4">Không thể tải ảnh</p>
            @if($downloadUrl)
            <a href="{{ $downloadUrl }}" target="_blank"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center">
                <i class="fas fa-external-link-alt mr-2"></i>Mở trong tab mới
            </a>
            @endif
        </div>
    </div>
</div>