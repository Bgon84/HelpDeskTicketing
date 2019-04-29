<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsermanagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usermanagers', function (Blueprint $table) {
            $table->increments('umid');
            $table->integer('userid');
            $table->integer('managerid');
            $table->unique(['userid', 'managerid']);
            $table->timestamps();

            $table->foreign('userid')
                  ->references('userid')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('managerid')
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
        Schema::dropIfExists('usermanagers');
    }
}
