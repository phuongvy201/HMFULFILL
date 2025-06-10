<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidPrintSpace implements Rule
{
    protected $validPositions = ['Front', 'Back', 'Left sleeve', 'Right sleeve', 'Hem'];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array($value, $this->validPositions);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The selected print position is invalid. Allowed values are: Front, Back, Left sleeve, Right sleeve, Hem.';
    }
}
