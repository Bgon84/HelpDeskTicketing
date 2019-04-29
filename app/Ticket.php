<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $primaryKey = 'ticketid';

    protected $fillable = [
        'requestorid', 
        'descriptionid', 
        'categoryid', 
        'priorityid', 
        'statusid',
        'queueid',
        'parentticketid',
        'masterticket',
        'timetoclose',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'dateresolved'
    ];

    public function requestor()
    {
        return $this->hasOne('App\User');
    }

    public function category()
    {
        return $this->hasOne('App\Category');
    }

    public function priority()
    {
        return $this->hasOne('App\Priority');
    }

    public function status()
    {
        return $this->hasOne('App\Status');
    }

    public function description()
    {
        return $this->hasOne('App\TicketDescription');
    }

    public function attachments()
    {
        return $this->hasMany('App\TicketAttachment');
    }

    public function updates()
    {
        return $this->belongsTo('App\TicketUpdate');
    }

    public function techs()
    {
        return $this->belongsToMany('App\User', 'tickettechs',
            'ticketid', 'techid')->withTimestamps();
    }

    public function queues()
    {
        return $this->belongsToMany('App\Queue', 'ticketqueues',
            'ticketid', 'queueid');
    }
}

