<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FileEncodingUTF8 implements Rule
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
    public function passes($attribute, $file)
    {
        try {
            iconv('UTF-8', 'UTF-8', file_get_contents($file));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The uploaded file for :attribute should be in UTF-8 encoding';
    }
}
