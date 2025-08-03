<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UspsTrackingService
{
    protected $baseUrl = 'https://secure.shippingapis.com/ShippingAPI.dll';
    protected $userId;
    protected $password;

    public function __construct()
    {
        $this->userId = config('services.usps.user_id');
        $this->password = config('services.usps.password');
    }

    /**
     * Track một tracking number
     * 
     * @param string $trackingNumber
     * @return array
     */
    public function trackSinglePackage($trackingNumber)
    {
        try {
            $xml = $this->buildTrackRequest([$trackingNumber]);
            $response = $this->sendRequest($xml);

            return $this->parseTrackResponse($response);
        } catch (\Exception $e) {
            Log::error('USPS Tracking Error for ' . $trackingNumber, [
                'error' => $e->getMessage(),
                'tracking_number' => $trackingNumber
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'tracking_number' => $trackingNumber
            ];
        }
    }

    /**
     * Track nhiều tracking numbers (tối đa 35)
     * 
     * @param array $trackingNumbers
     * @return array
     */
    public function trackMultiplePackages($trackingNumbers)
    {
        // Giới hạn 35 tracking numbers theo API
        if (count($trackingNumbers) > 35) {
            throw new \Exception('USPS API chỉ hỗ trợ tối đa 35 tracking numbers mỗi request');
        }

        try {
            $xml = $this->buildTrackRequest($trackingNumbers);
            $response = $this->sendRequest($xml);

            return $this->parseTrackResponse($response);
        } catch (\Exception $e) {
            Log::error('USPS Tracking Error for multiple packages', [
                'error' => $e->getMessage(),
                'tracking_numbers' => $trackingNumbers
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'tracking_numbers' => $trackingNumbers
            ];
        }
    }

    /**
     * Xây dựng XML request
     * 
     * @param array $trackingNumbers
     * @return string
     */
    protected function buildTrackRequest($trackingNumbers)
    {
        $trackIdElements = '';
        foreach ($trackingNumbers as $trackingNumber) {
            $trackIdElements .= '<TrackID ID="' . htmlspecialchars($trackingNumber) . '"></TrackID>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<TrackRequest USERID="' . htmlspecialchars($this->userId) . '"';

        if ($this->password) {
            $xml .= ' PASSWORD="' . htmlspecialchars($this->password) . '"';
        }

        $xml .= '>';
        $xml .= $trackIdElements;
        $xml .= '</TrackRequest>';

        return $xml;
    }

    /**
     * Gửi request đến USPS API
     * 
     * @param string $xml
     * @return string
     */
    protected function sendRequest($xml)
    {
        $response = Http::asForm()->post($this->baseUrl, [
            'API' => 'TrackV2',
            'XML' => $xml
        ]);

        if (!$response->successful()) {
            throw new \Exception('USPS API request failed: ' . $response->status());
        }

        return $response->body();
    }

    /**
     * Parse response từ USPS API
     * 
     * @param string $response
     * @return array
     */
    protected function parseTrackResponse($response)
    {
        try {
            $xml = simplexml_load_string($response);

            if (!$xml) {
                throw new \Exception('Invalid XML response from USPS API');
            }

            $result = [
                'success' => true,
                'packages' => []
            ];

            if (isset($xml->TrackInfo)) {
                foreach ($xml->TrackInfo as $trackInfo) {
                    $package = [
                        'tracking_number' => (string) $trackInfo['ID'],
                        'delivery_notification_date' => (string) $trackInfo->DeliveryNotificationDate,
                        'expected_delivery_date' => (string) $trackInfo->ExpectedDeliveryDate,
                        'expected_delivery_time' => (string) $trackInfo->ExpectedDeliveryTime,
                        'guaranteed_delivery_date' => (string) $trackInfo->GuaranteedDeliveryDate,
                        'track_summary' => (string) $trackInfo->TrackSummary,
                        'track_details' => []
                    ];

                    // Parse track details
                    if (isset($trackInfo->TrackDetail)) {
                        foreach ($trackInfo->TrackDetail as $detail) {
                            $package['track_details'][] = (string) $detail;
                        }
                    }

                    $result['packages'][] = $package;
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error parsing USPS response', [
                'error' => $e->getMessage(),
                'response' => $response
            ]);

            return [
                'success' => false,
                'error' => 'Error parsing USPS response: ' . $e->getMessage(),
                'raw_response' => $response
            ];
        }
    }

    /**
     * Kiểm tra trạng thái giao hàng
     * 
     * @param string $trackingNumber
     * @return array
     */
    public function checkDeliveryStatus($trackingNumber)
    {
        $result = $this->trackSinglePackage($trackingNumber);

        if (!$result['success'] || empty($result['packages'])) {
            return $result;
        }

        $package = $result['packages'][0];
        $trackSummary = strtolower($package['track_summary'] ?? '');

        // Phân tích trạng thái dựa trên track summary
        $status = 'unknown';
        $isDelivered = false;

        if (strpos($trackSummary, 'delivered') !== false) {
            $status = 'delivered';
            $isDelivered = true;
        } elseif (strpos($trackSummary, 'in transit') !== false || strpos($trackSummary, 'enroute') !== false) {
            $status = 'in_transit';
        } elseif (strpos($trackSummary, 'out for delivery') !== false) {
            $status = 'out_for_delivery';
        } elseif (strpos($trackSummary, 'arrived') !== false) {
            $status = 'arrived_at_unit';
        } elseif (strpos($trackSummary, 'accepted') !== false || strpos($trackSummary, 'pickup') !== false) {
            $status = 'accepted';
        }

        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'status' => $status,
            'is_delivered' => $isDelivered,
            'track_summary' => $package['track_summary'],
            'expected_delivery_date' => $package['expected_delivery_date'],
            'track_details' => $package['track_details'],
            'full_response' => $package
        ];
    }

    /**
     * Cache tracking result để tránh gọi API quá nhiều
     * 
     * @param string $trackingNumber
     * @param int $cacheMinutes
     * @return array
     */
    public function trackWithCache($trackingNumber, $cacheMinutes = 30)
    {
        $cacheKey = 'usps_tracking_' . $trackingNumber;

        return Cache::remember($cacheKey, $cacheMinutes * 60, function () use ($trackingNumber) {
            return $this->trackSinglePackage($trackingNumber);
        });
    }

    /**
     * Xóa cache cho tracking number
     * 
     * @param string $trackingNumber
     * @return bool
     */
    public function clearTrackingCache($trackingNumber)
    {
        $cacheKey = 'usps_tracking_' . $trackingNumber;
        return Cache::forget($cacheKey);
    }
}
