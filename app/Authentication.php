<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Authentication extends Model
{
	protected $table = 'authentication';

    protected $primaryKey = 'authid';

    protected $fillable = [
        'name', 'server', 'port', 'username', 'password', 'binddn', 'filter', 'active'
    ];
}
