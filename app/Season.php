<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Season extends Model
{
    use LogsActivity;

    protected static $logName = 'season';  // Name for the log
    protected static $logAttributes = ['*']; // Log All fields in the table
    protected static $logOnlyDirty = true; // Only log the fields that have been updated

    private const CDELIM = ',';
    public static function fetch()
    {
        $seasons = Season::all()->orderBy('updated_at', 'desc')->get();
        return $seasons;
    }

    protected $fillable = [
        'game', 'season', 'tier', 'year'
    ];

    public function races()
    {
        return $this->hasMany('App\Race');
    }

    public function series()
    {
        return $this->belongsTo('App\Series');
    }

    public function getConstructorsAttribute($string)
    {
        $cars = array_map('intval', explode(self::CDELIM, $string));
        $lm = Constructor::whereIn('id', $cars)->get()->toArray();

        return $lm;
    }
    public function getTttracksAttribute($string)
    {
        $circuits = array_map('intval', explode(self::CDELIM, $string));
        $lm = Circuit::whereIn('id', $circuits)->get()->toArray();

        return $lm;
    }

    public function signups()
    {
        return $this->hasMany('App\Signup');
    }
}
