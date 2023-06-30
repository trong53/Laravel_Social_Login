<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NameRegex implements Rule
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
        // dd($attribute);      // "name" // app\Rules\NameRegex.php:28
        $name_pattern = '/^[A-z]{1,}\s?([A-z]{1,}\'?\-?[A-z]{1,}\s?)+([A-z]{1,})?$/';

        if (preg_match($name_pattern, $value)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute field is not correct';
    }
}
