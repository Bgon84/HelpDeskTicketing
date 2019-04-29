<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
	protected $primaryKey = 'categoryid';

    protected $fillable = [
        'category', 
        'priorityid', 
        'active', 
        'internal',
        'description',
    ];

    public function tickets()
    {
        return $this->belongsToMany('App\Ticket');
    }

    public function queues()
    {
        return $this->belongsToMany('App\Queue', 'categoryqueues', 
          'categoryid', 'queueid');
    }

}
