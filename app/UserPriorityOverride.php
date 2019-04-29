<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPriorityOverride extends Model
{
	protected $table = 'userpriorityoverride';

	protected $primaryKey = 'priorityorid';

    protected $fillable = [
        'userid', 'level',
    ];

    public function user()
    {
        return $this->belongsToMany('App\User');
    }
}
