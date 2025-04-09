<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'created_at', 'updated_at'];

    public function variantAttributes()
    {
        return $this->hasMany(VariantAttribute::class);
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attributes');
    }
}
