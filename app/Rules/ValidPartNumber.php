<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\ProductVariant;

class ValidPartNumber implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return ProductVariant::where('sku', $value)
            ->orWhere('twofifteen_sku', $value)
            ->orWhere('flashship_sku', $value)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Mã sản phẩm :input không tồn tại trong hệ thống.';
    }
}
