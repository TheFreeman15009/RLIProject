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
        return $this->hasMany('App\Season', 'series');
    }

    public function constructors()
    {
        return $this->hasMany('App\Constructor', 'series');
    }

    public function circuits()
    {
        return $this->hasMany('App\Circuit', 'series');
    }
}
