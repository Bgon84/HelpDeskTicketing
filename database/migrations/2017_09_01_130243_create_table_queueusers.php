<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableQueueusers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queueusers', function (Blueprint $table) {
            $table->increments('quid');
            $table->integer('queueid');
            $table->integer('userid');
            $table->unique(['queueid', 'userid']);
            $table->timestamps();

            $table->foreign('queueid')
                  ->references('queueid')
                  ->on('queues')
                  ->onDelete('cascade');

            $table->foreign('userid')
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
        Schema::dropIfExists('queueusers');
    }
}
