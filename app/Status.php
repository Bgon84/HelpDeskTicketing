<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
	protected $primaryKey = 'statusid';

    public function tickets()
    {
        return $this->belongsToMany('App\Ticket');
    }
}
