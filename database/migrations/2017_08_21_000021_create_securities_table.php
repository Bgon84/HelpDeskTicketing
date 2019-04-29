<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecuritiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('securities', function (Blueprint $table) {
            $table->increments('securityid');
            $table->integer('permissionid');
            $table->integer('roleid');
            $table->timestamps();

            $table->foreign('permissionid')
                  ->references('permissionid')
                  ->on('permissions')
                  ->onDelete('cascade');

            $table->foreign('roleid')
                  ->references('roleid')
                  ->on('roles')
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
        Schema::dropIfExists('securities');
    }
}
