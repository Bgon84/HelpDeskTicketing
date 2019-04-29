<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'email', 
        'name', 
        'password', 
        'active', 
        'phoneNumber', 
        'logintype', 
        'extension', 
        'mobilephone',
        'manager',
        'direxclude',
        'prefereddash',
    ];

    protected $primaryKey = 'userid';

    protected $hidden = [
        'password', 'remember_token',
    ];


    public function priorityor()
    {
        return $this->hasOne('App\UserPriorityOverride', 'userid');
    }

    public function tickets()
    {
        return $this->hasMany('App\Ticket', 'tickettechs',
            'userid', 'ticketid')->withTimestamps();
    }

    public function phones()
    {
        return $this->hasMany('App\UserPhone', 'userid');
    }

    public function emails()
    {
        return $this->hasMany('App\UserEmail', 'userid');
    }

    public function logs()
    {
        return $this->hasMany('App\ActivityLog', 'userid');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Role', 'userroles', 
          'userid', 'roleid')->withTimestamps();
    }

    public function groups()
    {
        return $this->belongsToMany('App\Group', 'usergroups', 
          'userid', 'groupid')->withTimestamps();
    }

    public function updates()
    {
      return $this->belongstoMany('App\TicketUpdate', 'userid');
    }

    // public function managers()
    // {
    //     return $this->belongsToMany('App\User', 'usermanagers', 
    //       'userid', 'managerid')->withTimestamps();
    // }

    public function underlings()
    {
        return $this->belongsToMany('App\User', 'usermanagers', 
          'managerid', 'userid')->withTimestamps();
    }

    public function notifications()
    {
        return $this->belongsToMany('App\Notification', 'notificationusers', 
          'userid', 'notificationid')->withTimestamps();
    }

    public function queues()
    {
        return $this->belongsToMany('App\Queue', 'queueusers', 
          'userid', 'queueid')->withTimestamps();
    }
}
