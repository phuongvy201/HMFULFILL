<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcelOrder extends Model
{
    protected $fillable = [
        'external_id',
        'brand',
        'channel',
        'buyer_email',
        'first_name',
        'last_name',
        'company',
        'address1',
        'address2',
        'city',
        'county',
        'post_code',
        'country',
        'phone1',
        'phone2',
        'comment',
        'status',
        'api_response',
        'import_file_id'
    ];

    protected $casts = [
        'api_response' => 'array'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ExcelOrderItem::class);
    }

    public function importFile(): BelongsTo
    {
        return $this->belongsTo(ImportFile::class);
    }
}
