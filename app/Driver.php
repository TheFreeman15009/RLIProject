<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Driver extends Model
{
    use LogsActivity;

    protected static $logName = 'driver';  // Name for the log
    protected static $logAttributes = ['*']; // Log All fields in the table // Log when a driver is Created or Updated
    protected static $logOnlyDirty = true; // Only log the fields that have been updated

    private const DELIMITER = '~$~';

    public static function selfLearn(string $predicted, int $id)
    {
        $driver = Driver::find($id);
        $driver->insertAlias($predicted);
        return 0;
    }

    public static function getNames()
    {
        $driver_list = Driver::select('id', 'name', 'alias')->get();
        return json_decode(json_encode($driver_list), true);
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function insertAlias(string $newAlias)
    {
        $aliasString = implode(self::DELIMITER, $this->alias);
        if (!in_array($newAlias, $this->alias)) {
            $this->alias = $aliasString . self::DELIMITER . $newAlias;
            $this->save();
            return 1;
        }
        return 0;
    }

    // Split alias string into an array of aliases
    public function getAliasAttribute($aliasString)
    {
        return explode(self::DELIMITER, $aliasString);
    }

    public function results()
    {
        return $this->hasMany('App\Result');
    }

    public function myReports()
    {
        return $this->hasMany('App\Report', 'reporting_driver');
    }

    public function reportsAgainstMe()
    {
        return $this->hasMany('App\Report', 'reported_against');
    }
}
