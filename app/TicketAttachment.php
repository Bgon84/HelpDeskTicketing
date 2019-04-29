<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
	protected $table = 'ticketattachments';
	protected $primaryKey = 'attachmentid';

	protected $fillable = [
        'ticketid', 'attachmentpath',
    ];

    public function ticket()
    {
        return $this->belongsTo('App\Ticket');
    }
}
