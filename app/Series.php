<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Series extends Model
{
    use LogsActivity;

    protected static $logName = 'series';       // Name for the log
    protected static $logAttributes = ['*'];    // Log All fields in the table
    protected static $logOnlyDirty = true;      // Only log the fields that have been updated

    public function seasons()
    {
        return $this->hasMany('App\Season');
    }

    public function constructors()
    {
        return $this->hasMany('App\Constructor');
    }

    public function circuits()
    {
        return $this->hasMany('App\Circuit');
    }

    /**
     * Scope a query to return matching series for the given code
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $code
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }
}
