<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableQueueoptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queueoptions', function (Blueprint $table) {
            $table->increments('qoid');
            $table->integer('queueid');
            $table->integer('optionid');
            $table->unique(['queueid', 'optionid']);
            $table->timestamps();

            $table->foreign('queueid')
                  ->references('queueid')
                  ->on('queues')
                  ->onDelete('cascade');

            $table->foreign('optionid')
                  ->references('optionid')
                  ->on('options')
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
        Schema::dropIfExists('queueoptions');
    }
}
