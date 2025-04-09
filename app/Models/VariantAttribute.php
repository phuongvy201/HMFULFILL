<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantAttribute extends Model
{
    protected $fillable = ['variant_id', 'attribute_id', 'value'];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
    
}
