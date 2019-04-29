<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Security extends Model
{
	protected $primaryKey = 'securityid';

    public function role()
    {
        return $this->hasOne('App\Role');
    }

    public function permission()
    {
        return $this->hasOne('App\Permission');
    }
}
