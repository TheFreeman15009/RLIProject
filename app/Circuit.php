<?php

namespace App;

use App\Traits\Queryable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Circuit extends Model
{
    use Queryable;
    use LogsActivity;

    protected static $logName = 'circuit';   // Name for the log
    protected static $logAttributes = ['*']; // Log All fields in the table
    protected static $logOnlyDirty = true;   // Only log the fields that have been updated

    protected static $filterableFields = ['series', 'title', 'game', 'id', 'name', 'country'];
    protected static $prohibitedFields = [];        // For Index API

    public static function getOfficial()
    {
        $official_list = Circuit::select('id', 'official')->get();
        return json_decode(json_encode($official_list), true);
    }

    public static function getTrackByGame($game, $series)
    {
        $track = Circuit::where('game', $game)
                        ->where('series', $series)
                        ->first();

        return $track;
    }
    public function associatedSeries()
    {
        return $this->belongsTo('App\Series', 'series');
    }

    public function races()
    {
        return $this->hasMany('App\Race');
    }
}
