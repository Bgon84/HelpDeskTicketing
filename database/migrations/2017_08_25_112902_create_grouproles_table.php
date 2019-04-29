<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGrouprolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grouproles', function (Blueprint $table) {
            $table->increments('grid');
            $table->integer('groupid');
            $table->integer('roleid');
            $table->unique(['groupid', 'roleid']);
            $table->timestamps();

            $table->foreign('roleid')
                  ->references('roleid')
                  ->on('roles')
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
        Schema::dropIfExists('grouproles');
    }
}
