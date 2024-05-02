<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Report extends Model
{
    use LogsActivity;

    protected static $logName = 'report';    // Name for the log
    protected static $logAttributes = ['*']; // Log All fields in the table
    protected static $logOnlyDirty = true;   // Only log the fields that have been updated

    protected $fillable = [
         'reporting_driver', 'reported_against', 'race_id',
         'lap', 'explanation', 'verdict_message', 'proof',
         'stewards_notes', 'verdict_pp', 'verdict_time',
         'resolved'
    ];

    public function race()
    {
        return $this->belongsTo('App\Race');
    }

    // phpcs:ignore
    public function reporting_driver()
    {
        return $this->belongsTo('App\Driver', 'reporting_driver');
    }

    // phpcs:ignore
    public function reported_against()
    {
        return $this->belongsTo('App\Driver', 'reported_against');
    }

    // phpcs:ignore
    public function counter_report()
    {
        return $this->belongsTo('App\Report', 'counter_report');
    }
    public function counterReportAgainst()
    {
        return $this->hasMany('App\Report', 'counter_report');
    }
}
