<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserEmail extends Model
{
	protected $table = 'useremails';

	protected $primaryKey = 'useremailid';

	protected $fillable = [
        'userid', 'useremail', 'primaryemail',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'userid');
    }

}
