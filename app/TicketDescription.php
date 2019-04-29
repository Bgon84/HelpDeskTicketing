<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketDescription extends Model
{
	protected $table = 'ticketdescriptions';
	protected $primaryKey = 'descriptionid';

	protected $fillable = ['description'];

    public function ticket()
    {
        return $this->belongsTo('App\Ticket');
    }
}
