<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmtpsettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smtpsettings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('server');
            $table->integer('port');
            $table->string('encryption');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('fromaddress');
            $table->timestamps();
        });

        DB::table('smtpsettings')->insert([
            
            'server' => 'smtp.example.com', 
            'port' => '1234',
            'encryption' => 'None', 
            'username' => 'smtpusername',
            'password' => '', 
            'fromaddress' => 'smtp@example.com',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('smtpsettings');
    }
}
