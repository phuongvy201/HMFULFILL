<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FulfillmentRegion extends Model
{
    protected $fillable = ['name', 'created_at', 'updated_at'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_fulfillment_regions');
    }
}
