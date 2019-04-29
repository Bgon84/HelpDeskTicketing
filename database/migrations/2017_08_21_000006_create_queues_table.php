<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->increments('queueid');
            $table->string('queuename')->unique();
            $table->integer('elevationqueue')->nullable();
            $table->integer('active')->default('1');
            $table->timestamps();

            $table->foreign('elevationqueue')
                  ->references('queueid')
                  ->on('queues')
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
        Schema::dropIfExists('queues');
    }
}
