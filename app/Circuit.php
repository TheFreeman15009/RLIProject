<?php

namespace App;

use App\Traits\Queryable;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Circuit extends Model
{
    use Queryable;
    use LogsActivity;

    protected static $filterableFields = ['series_id', 'title', 'game', 'id', 'name', 'country'];
    protected static $prohibitedFields = [];        // For Index API

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                         ->logAll()
                         ->logOnlyDirty()
                         ->useLogName('circuit');
    }

    public static function getOfficial()
    {
        $official_list = Circuit::select('id', 'official')->get();
        return json_decode(json_encode($official_list), true);
    }

    public function series()
    {
        return $this->belongsTo('App\Series');
    }

    public function races()
    {
        return $this->hasMany('App\Race');
    }

     /**
     * Scope a query to return matching game for a particular series.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $game
     * @param  int $seriesId
     * @param  int|null $title
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGame($query, $game, $seriesId, $title = null)
    {
        $resultQuery = $query->where('game', $game)->where('series_id', $seriesId);
        if (!is_null($title)) {
            $resultQuery = $resultQuery->where('title', $title);
        }

        $resultQuery = $resultQuery->orderBy('id', 'desc');
        return $resultQuery;
    }
}
