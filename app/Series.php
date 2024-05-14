<?php

namespace App;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Series extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                         ->logAll()
                         ->logOnlyDirty()
                         ->useLogName('series');
    }

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
