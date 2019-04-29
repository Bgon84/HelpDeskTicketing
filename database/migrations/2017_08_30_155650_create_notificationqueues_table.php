<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationqueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notificationqueues', function (Blueprint $table) {
            $table->increments('nqid');
            $table->integer('notificationid');
            $table->integer('queueid');
            $table->unique(['notificationid', 'queueid']);
            $table->timestamps();

            $table->foreign('notificationid')
                  ->references('notificationid')
                  ->on('notifications')
                  ->onDelete('cascade');

            $table->foreign('queueid')
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
        Schema::dropIfExists('notificationqueues');
    }
}
