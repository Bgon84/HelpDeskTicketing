<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryqueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categoryqueues', function (Blueprint $table) {
            $table->increments('cqid');
            $table->integer('categoryid');
            $table->integer('queueid');
            $table->unique(['categoryid', 'queueid']);
            $table->integer('active')->default('1');
            $table->timestamps();

            $table->foreign('categoryid')
                  ->references('categoryid')
                  ->on('categories')
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
        Schema::dropIfExists('categoryqueues');
    }
}
