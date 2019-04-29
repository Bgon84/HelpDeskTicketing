<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
	protected $primaryKey = 'priorityid';

	protected $fillable = [
        'priority', 
        'description',
    ];


    public function tickets()
    {
        return $this->belongsToMany('App\Ticket');
    }
}
