<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
	protected $primaryKey = 'roleid';

	protected $fillable = ['rolename', 'active', 'description',];

    public function users()
	{
	    return $this->belongsToMany('App\User', 'userroles', 
	      'roleid', 'userid')->withTimestamps();
	}

	public function permissions()
	{
	    return $this->belongsToMany('App\Permission', 'securities', 
	      'roleid', 'permissionid')->withTimestamps();
	}

	public function groups()
	{
	    return $this->belongsToMany('App\Group', 'grouproles', 
	      'roleid', 'groupid')->withTimestamps();
	}

}
