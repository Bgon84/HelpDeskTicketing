<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketupdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticketupdates', function (Blueprint $table) {
            $table->increments('ticketupdateid');
            $table->integer('ticketid');
            $table->integer('userid');
            $table->text('content');
            $table->integer('updatetypeid');
            $table->timestamps();

            $table->foreign('ticketid')
                  ->references('ticketid')
                  ->on('tickets')
                  ->onDelete('cascade');

            $table->foreign('userid')
                  ->references('userid')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('updatetypeid')
                  ->references('updatetypeid')
                  ->on('updatetypes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticketupdates');
    }
}
