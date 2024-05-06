<?php

namespace App\Rules;

use App\Season;
use Illuminate\Contracts\Validation\Rule;

class AccCarModel implements Rule
{
    private $season;
    private $missingCarModels;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Season $season)
    {
        $this->season = $season;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $leaderBoardLines)
    {
        $constructorGameIds = $this->season->constructors->pluck('game')->toArray();

        $inputCarModels = array();
        foreach ($leaderBoardLines as $driver) {
            array_push($inputCarModels, strval($driver['car']['carModel']));
        }

        $this->missingCarModels = array_diff($inputCarModels, $constructorGameIds);
        return (count($this->missingCarModels) == 0);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $missingCarModelsString = implode(", ", $this->missingCarModels);
        return 'ACC car models [ ' . $missingCarModelsString . ' ] do not exist in the database';
    }
}
