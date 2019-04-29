<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
	protected $table = 'activitylog';
	protected $primaryKey = 'activityid';

	public function user()
    {
        return $this->hasOne('App\User');
    }

    public function activitytype()
    {
        return $this->hasOne('App\ActivityType');
    }

}
