<?php

namespace App\Http\Requests;

use App\Season;
use App\Rules\AccCarModel;
use App\Rules\TrackNameForSeries;
use Illuminate\Foundation\Http\FormRequest;

class AccResultFile extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Season $season): array
    {
        return [
            'trackName' => [ 'required', 'string', new TrackNameForSeries($season->series_id) ],
            'sessionResult.bestlap' => [ 'required', 'gte:0' ],

            'sessionResult.leaderBoardLines.*.timing.lapCount' => [ 'required', 'gte:0' ],
            'sessionResult.leaderBoardLines.*.timing.bestLap' => [ 'required', 'gte:0' ],
            'sessionResult.leaderBoardLines.*.timing.lastLap' => [ 'required', 'gte:0' ],
            'sessionResult.leaderBoardLines.*.timing.totalTime' => [ 'required', 'gte:0' ],

            'sessionResult.leaderBoardLines.*.currentDriver.playerId' => [ 'required', 'regex:/^S\d+$/' ],
            'sessionResult.leaderBoardLines.*.currentDriver.shortName' => [ 'required', 'string' ],

            'sessionResult.leaderBoardLines.*.car.carId' => [ 'required', 'integer' ],
            'sessionResult.leaderBoardLines.*.car.carModel' => [ 'required', 'integer' ],

            'sessionResult.leaderBoardLines' => [ 'required', new AccCarModel($season) ],
        ];
    }
}
