<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notificationusers', function (Blueprint $table) {
            $table->increments('nuid');
            $table->integer('notificationid');
            $table->integer('userid');
            $table->unique(['notificationid', 'userid']);
            $table->timestamps();

            $table->foreign('notificationid')
                  ->references('notificationid')
                  ->on('notifications')
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
        Schema::dropIfExists('notificationusers');
    }
}
