<?php

namespace App;

use Spatie\Activitylog\LogOptions;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use LogsActivity;

    public static function updateAlias()
    {
        $user = User::all();
        return $user;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'discord_discrim', 'discord_id', 'avatar'
    ];

    protected static $logName = 'user';         // Name for the log
    protected static $logAttributes = ['*'];    // Log All fields in the table
    protected static $logOnlyDirty = true;      // Only log the fields that have been updated
    public const SENSITIVEATTRIBUTES = [
        'password', 'remember_token', 'api_token', 'email', 'mothertongue', 'location'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = self::SENSITIVEATTRIBUTES;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                         ->logAll()
                         ->logOnlyDirty()
                         ->useLogName('user')
                         ->logExcept([ 'password', 'remember_token', 'api_token' ]);
    }

    public function driver()
    {
        return $this->hasOne('App\Driver');
    }

    public function signups()
    {
        return $this->hasMany('App\Signup');
    }
}
