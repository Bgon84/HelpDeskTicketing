<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddtlLDAPColsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('users', function (Blueprint $table) 
        {
            $table->string('faxnumber')->nullable();
            $table->string('manager')->nullable();
            $table->string('location')->nullable();
            $table->string('title')->nullable();
            $table->string('pwdlastset')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
