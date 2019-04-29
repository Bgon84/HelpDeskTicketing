<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsupdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settingsupdates', function (Blueprint $table) {
            $table->increments('settingsupdateid');
            $table->integer('updatedby');
            $table->string('setting');
            $table->string('oldvalue')->nullable();
            $table->string('newvalue')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settingsupdates');
    }
}
