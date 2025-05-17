<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FulfillmentLocation extends Model
{
    protected $fillable = ['product_id', 'country_code'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
