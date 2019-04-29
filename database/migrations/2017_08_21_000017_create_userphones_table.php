<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserphonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userphones', function (Blueprint $table) {
            $table->increments('userphoneid');
            $table->integer('userid');
            $table->string('userphone');
            $table->string('extension')->nullable();
            $table->integer('primaryphone');
            $table->timestamps();

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
        Schema::dropIfExists('userphones');
    }
}
