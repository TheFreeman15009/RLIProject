<?php

namespace App;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Race extends Model
{
    use LogsActivity;

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
        'circuit_id', 'season_id', 'round', 'distance', 'points_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                         ->logAll()
                         ->logOnlyDirty()
                         ->useLogName('race');
    }

    public function season()
    {
        return $this->belongsTo('App\Season');
    }

    public function circuit()
    {
        return $this->belongsTo('App\Circuit');
    }

    public function points()
    {
        return $this->belongsTo('App\Points');
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
