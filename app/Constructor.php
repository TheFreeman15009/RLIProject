<?php

namespace App;

use App\Traits\Queryable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Constructor extends Model
{
    use Queryable;
    use LogsActivity;

    protected static $logName = 'constructor';  // Name for the log
    protected static $logAttributes = ['*'];    // Log All fields in the table
    protected static $logOnlyDirty = true;      // Only log the fields that have been updated

    protected static $filterableFields = ['series', 'title', 'game', 'id', 'name'];
    protected static $prohibitedFields = [];

    public static function getTeams()
    {
        $team_list = Constructor::select('id', 'name')->get();
        return json_decode(json_encode($team_list), true);
    }

    public function results()
    {
        return $this->hasMany('App\Result');
    }
}
