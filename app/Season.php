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

    public function associatedSeries()
    {
        return $this->belongsTo('App\Series', 'series');
    }

    public function getConstructorsAttribute($string)
    {
        $cars = array_map('intval', explode(self::CDELIM, $string));
        $lm = Constructor::whereIn('id', $cars)->get();

        return $lm;
    }
    public function getTttracksAttribute($string)
    {
        $circuits = array_map('intval', explode(self::CDELIM, $string));
        $lm = Circuit::whereIn('id', $circuits)->get()->toArray();

        return $lm;
    }

    public function races()
    {
        return $this->hasMany('App\Race');
    }

    public function signups()
    {
        return $this->hasMany('App\Signup', 'season');
    }

    /**
     * Scope a query to only include active seasons with signups.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveWithSignups($query)
    {
        return $query->where([
            ['status', '<', 2],
            ['status', '>', 0]
        ]);
    }

    /**
     * Scope a query to only include active seasons.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', '<', 2);
    }
}
