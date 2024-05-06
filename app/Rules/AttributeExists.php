<?php

namespace App\Rules;

use DB;
use Illuminate\Contracts\Validation\Rule;

class AttributeExists implements Rule
{
    private $model;
    private $column;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($model, $column)
    {
        $this->model = $model;
        $this->column = $column;
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
        $record = $this->model::where($this->column, $value)->first();
        if ($record) {
            request()->request->add(['db_' . $attribute => $record]);
        }

        return !is_null($record);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
