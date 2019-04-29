<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthenticationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authentication', function (Blueprint $table) {
            $table->increments('authid');
            $table->string('name')->unique();
            $table->string('server');
            $table->string('port');
            $table->string('username');
            $table->string('password');
            $table->string('binddn');
            $table->string('filter')->nullable();
            $table->integer('active')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authentication');
    }
}
