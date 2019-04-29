<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->increments('optionid');
            $table->string('optionname')->unique();
            $table->text('optiondesc');
            $table->timestamps();
        });

       DB::table('options')->insert([
            [
                'optionname' => 'Round Robin', 
                'optiondesc' => 'Techs are assigned based on time since last recieved ticket from this queue.'
            ],
            [
                'optionname' => 'Select Tech',
                'optiondesc' => 'Techs are assigned based on the selected Tech on the Ticket Creation form.'
            ],
            [
                'optionname' => 'No Assignment', 
                'optiondesc' => 'Tickets will not be automatically assigned. Assignment must be done manually.'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('options');
    }
}
