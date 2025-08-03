<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Lấy URL đầy đủ của ảnh
     */
    public function getFullUrl()
    {
        if (empty($this->image_url)) {
            return null;
        }

        return str_starts_with($this->image_url, 'http')
            ? $this->image_url
            : asset($this->image_url);
    }

    /**
     * Kiểm tra xem ảnh có phải là ảnh chính của sản phẩm không
     * (ảnh được tạo sớm nhất)
     */
    public function isMainImage()
    {
        return $this->product->images()->orderBy('created_at', 'asc')->first()?->id === $this->id;
    }

    /**
     * Scope để lấy ảnh theo thứ tự created_at
     */
    public function scopeOldestFirst($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}
