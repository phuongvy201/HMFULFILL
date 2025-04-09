<?php

namespace App\Helpers;

class GoogleDriveHelper
{
    public static function getImageUrl($url)
    {
        if (str_contains($url, 'https://drive.google.com')) {
            // Lấy file ID từ URL
            preg_match('/[-\w]{25,}/', $url, $matches);
            $fileId = $matches[0] ?? null;

            if ($fileId) {
                // Tạo direct link
                return "https://drive.google.com/uc?export=view&id=" . $fileId;
            }
        }
        return $url;
    }
}
