<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
	protected $fillable = [
        'groupname', 'active', 'description',
    ];

    protected $primaryKey = 'groupid';

	public function users()
	{
	    return $this->belongsToMany('App\User', 'usergroups', 
	      'groupid', 'userid')->withTimestamps();
	}

	public function roles()
	{
	    return $this->belongsToMany('App\Role', 'grouproles', 
	      'groupid', 'roleid')->withTimestamps();
	}

    public function queues()
    {
        return $this->belongsToMany('App\Queue', 'queuegroups', 
          'groupid', 'queueid')->withTimestamps();
    }

}
