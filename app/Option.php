<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
	protected $primaryKey = 'optionid';
	
	public function queues()
	{
		 return $this->belongsToMany('App\Queue', 'queueoptions', 
          'optionid', 'queueid')->withTimestamps();
	}
}
