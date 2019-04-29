<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTickettechsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickettechs', function (Blueprint $table) {
            $table->increments('ttid');
            $table->integer('ticketid');
            $table->integer('techid');
            $table->unique(['ticketid', 'techid']);
            $table->timestamps();

            $table->foreign('ticketid')
                  ->references('ticketid')
                  ->on('tickets')
                  ->onDelete('cascade');

            $table->foreign('techid')
                  ->references('userid')
                  ->on('users')
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
        Schema::dropIfExists('tickettechs');
    }
}
