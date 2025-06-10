<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMapping extends Model
{
    use HasFactory;

    protected $table = 'orders_mapping';

    protected $fillable = [
        'external_id',
        'internal_id',
        'factory',
        'api_response'
    ];

    protected $casts = [
        'api_response' => 'array'
    ];

    // Các factory được hỗ trợ
    const FACTORIES = [
        'UK1' => 'Twofifteen',
        'UK2' => 'Prinful',
        'US' => 'DTG',
        'VN' => 'Lenful'
    ];



    /**
     * Tìm mapping theo external_id
     */
    public static function findByExternalId($externalId, $factory = null)
    {
        $query = static::where('external_id', $externalId);

        if ($factory) {
            $query->where('factory', $factory);
        }

        return $query->get();
    }

    /**
     * Tìm mapping theo internal_id
     */
    public static function findByInternalId($internalId, $factory)
    {
        return static::where('internal_id', $internalId)
            ->where('factory', $factory)
            ->first();
    }

    /**
     * Tạo hoặc cập nhật mapping
     */
    public static function createOrUpdate($externalId, $internalId, $factory, $apiResponse = null)
    {
        return static::updateOrCreate(
            [
                'external_id' => $externalId,
                'factory' => $factory
            ],
            [
                'internal_id' => $internalId,
                'api_response' => $apiResponse
            ]
        );
    }

    /**
     * Lấy danh sách mapping theo factory
     */
    public static function getByFactory($factory)
    {
        $query = static::where('factory', $factory);
        return $query->get();
    }
}
