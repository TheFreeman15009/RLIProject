<?php

namespace App\Rules;

use App\Circuit;
use Illuminate\Contracts\Validation\Rule;

class TrackNameForSeries implements Rule
{
    private $seriesId;
    private $trackName;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($seriesId)
    {
        $this->seriesId = $seriesId;
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
        $circuit = Circuit::game($value, $this->seriesId)->first();

        $this->trackName = $value;
        if ($circuit) {
            request()->request->add(['db_circuitTrackName' => $circuit]);
        }

        return !is_null($circuit);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "Circuit \"" . $this->trackName . "\" does not exist in the database";
    }
}
