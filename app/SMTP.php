<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMTP extends Model
{
    protected $table = 'smtpsettings';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'server', 
        'port', 
        'encryption', 
        'username', 
        'password', 
        'fromaddress', 
    ];

    protected $hidden = [
        'password', 
    ];
}
