<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityType extends Model
{
	protected $table = 'activitytypes';
	protected $primaryKey = 'activitytypeid';

    public function logs()
    {
        return $this->belongToMany('App\ActivityLog');
    }
}
