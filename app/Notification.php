<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'notificationid';

    protected $fillable = [
        'notificationname', 
        'notification', 
        'active',
        'triggeraction',
        'filterexpression',
        'recipient',
    ];

    public function users()
    {
    	return $this->belongsToMany('App\User', 'notificationusers', 
          'notificationid', 'userid')->withTimestamps();
    }

    public function queues()
    {
    	return $this->belongsToMany('App\Queue', 'notificationqueues', 
          'notificationid', 'queueid')->withTimestamps();
    }
}
