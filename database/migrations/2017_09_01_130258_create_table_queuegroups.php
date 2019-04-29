<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableQueuegroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queuegroups', function (Blueprint $table) {
            $table->increments('qgid');
            $table->integer('queueid');
            $table->integer('groupid');
            $table->unique(['queueid', 'groupid']);
            $table->timestamps();

            $table->foreign('queueid')
                  ->references('queueid')
                  ->on('queues')
                  ->onDelete('cascade');

            $table->foreign('groupid')
                  ->references('groupid')
                  ->on('groups')
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
        Schema::dropIfExists('queuegroups');
    }
}
