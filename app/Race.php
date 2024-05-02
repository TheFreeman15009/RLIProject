<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Race extends Model
{
    use LogsActivity;

    protected static $logName = 'race';         // Name for the log
    protected static $logAttributes = ['*'];    // Log All fields in the table
    protected static $logOnlyDirty = true;      // Only log the fields that have been updated

    public function insertRace()
    {
        $race = Race::where([
            ['circuit_id', '=', $this->circuit_id],
            ['season_id', '=', $this->season_id],
            ['round', '=', $this->round]
        ])->first();

        if ($race) {
            return $race;
        } else {
            $this->save();
            $this->refresh();
            return $this;
        }
    }

    protected $fillable = [
        'circuit_id', 'season_id', 'round', 'distance', 'points'
    ];

    public function season()
    {
        return $this->belongsTo('App\Season');
    }

    public function circuit()
    {
        return $this->belongsTo('App\Circuit');
    }

    public function pointSystem()
    {
        return $this->belongsTo('App\Points', 'points');
    }

    public function results()
    {
        return $this->hasMany('App\Result');
    }

    public function reports()
    {
        return $this->hasMany('App\Report');
    }
}
