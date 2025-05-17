<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'twofifteen_sku',
        'flashship_sku'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributes()
    {
        return $this->hasMany(VariantAttribute::class, 'variant_id');
    }

    public function shippingPrices()
    {
        return $this->hasMany(ShippingPrice::class, 'variant_id');
    }

    /**
     * Lấy SKU tương ứng dựa trên warehouse
     *
     * @param string|null $warehouse Giá trị warehouse (US, UK, hoặc null)
     * @return string|null SKU tương ứng với warehouse
     */
    public function getSkuByWarehouse(?string $warehouse): ?string
    {
        $wh = strtoupper(trim((string)$warehouse));
        if ($wh === 'US') {
            return $this->flashship_sku;
        } elseif ($wh === 'UK') {
            return $this->twofifteen_sku;
        }
        return $this->sku;
    }
}
