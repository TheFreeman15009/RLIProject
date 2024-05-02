<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Signup extends Model
{
    use LogsActivity;

    protected static $logName = 'signup';       // Name for the log
    protected static $logAttributes = ['*'];    // Log All fields in the table
    protected static $logOnlyDirty = true;      // Only log the fields that have been updated

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function associatedSeason()
    {
        return $this->belongsTo('App\Season', 'season');
    }
}
