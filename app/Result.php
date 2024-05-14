<?php

namespace App;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Result extends Model
{
    use LogsActivity;

    public function storeResult()
    {
        $result = Result::where([
            ['race_id', '=', $this->race_id],
            ['driver_id', '=', $this->driver_id]
        ])->first();

        if ($result) {
            return $result;
        } else {
            $this->save();
            $this->refresh();
            return $this;
        }
    }

    protected $fillable = [
        'constructor_id', 'driver_id', 'race_id',
        'grid', 'points', 'status',
        'fastestlaptime', 'position', 'tyres',
        'stops', 'time'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                         ->logAll()
                         ->logOnlyDirty()
                         ->useLogName('result');
    }

    public function race()
    {
        return $this->belongsTo('App\Race');
    }

    public function constructor()
    {
        return $this->belongsTo('App\Constructor');
    }

    public function driver()
    {
        return $this->belongsTo('App\Driver');
    }
}
