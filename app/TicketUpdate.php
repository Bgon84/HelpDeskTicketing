<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketUpdate extends Model
{
    protected $table = 'ticketupdates';
	protected $primaryKey = 'ticketupdateid';

    protected $fillable = [
        'ticketid', 'userid', 'content', 'updatetypeid',
    ];

 	public function ticket()
    {
        return $this->hasOne('App\Ticket');
    }

 	public function user()
    {
        return $this->hasOne('App\User');
    }

 	public function updatetype()
    {
        return $this->hasOne('App\UpdateType');
    }
}
