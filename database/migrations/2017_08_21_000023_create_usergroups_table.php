<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsergroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usergroups', function (Blueprint $table) {
            $table->increments('ugid');
            $table->integer('userid');
            $table->integer('groupid');
            $table->unique(['userid', 'groupid']);
            $table->timestamps();

            $table->foreign('groupid')
                  ->references('groupid')
                  ->on('groups')
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
        Schema::dropIfExists('usergroups');
    }
}
