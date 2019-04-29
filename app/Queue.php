<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
	protected $primaryKey = 'queueid';

    protected $fillable = [
        'queuename', 
        'elevationqueue', 
        'active', 
        'description',
    ];

    public function tickets()
    {
        return $this->belongsToMany('App\Ticket', 'ticketqueues',
            'queueid', 'ticketid');
    }

    public function elevationqueue()
    {
        return $this->hasOne('App\Queue');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Category', 'categoryqueues', 
          'queueid', 'categoryid');
    }

    public function notifications()
    {
        return $this->belongsToMany('App\Notification', 'notificationqueues', 
          'queueid', 'notificationid')->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'queueusers', 
          'queueid', 'userid')->withTimestamps();
    }

    public function groups()
    {
        return $this->belongsToMany('App\Group', 'queuegroups', 
          'queueid', 'groupid')->withTimestamps();
    }

    public function options()
    {
        return $this->belongsToMany('App\Option', 'queueoptions', 
          'queueid', 'optionid')->withTimestamps();
    }
}
