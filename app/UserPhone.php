<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPhone extends Model
{
	protected $table = 'userphones';

	protected $primaryKey = 'userphoneid';

	protected $fillable = [
        'userid', 'userphone', 'extension', 'primaryphone',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'userid');
    }
}
