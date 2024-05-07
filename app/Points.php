<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Points extends Model
{
    use LogsActivity;

    protected static $logName = 'Points';       // Name for the log
    protected static $logAttributes = ['*'];    // Log All fields in the table
    protected static $logOnlyDirty = true;      // Only log the fields that have been updated

    public function races()
    {
        return $this->hasMany('App\Race');
    }
}
