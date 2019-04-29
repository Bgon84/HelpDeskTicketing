<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitylogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activitylog', function (Blueprint $table) {
            $table->increments('activityid');
            $table->integer('userid');
            $table->integer('activitytypeid');
            $table->text('activity');
            $table->timestamps();

            $table->foreign('activitytypeid')
                  ->references('activitytypeid')
                  ->on('activitytypes')
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
        Schema::dropIfExists('activitylog');
    }
}
