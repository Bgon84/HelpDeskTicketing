<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
	protected $primaryKey = 'permissionid';

	public function roles()
	{
	    return $this->belongsToMany('App\Role', 'securities', 
	      'permissionid', 'roleid');
	}
}
