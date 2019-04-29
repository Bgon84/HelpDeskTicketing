<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('ticketid');
            $table->integer('requestorid');
            $table->integer('techid')->nullable();
            $table->integer('descriptionid');
            $table->integer('categoryid');
            $table->integer('priorityid');
            $table->integer('statusid');
            $table->integer('queueid')->nullable();
            $table->dateTime('dateresolved')->nullable();
            $table->timestamps();

            $table->foreign('categoryid')
                  ->references('categoryid')
                  ->on('categories')
                  ->onDelete('cascade');

            $table->foreign('priorityid')
                  ->references('priorityid')
                  ->on('priorities')
                  ->onDelete('cascade');

            $table->foreign('statusid')
                  ->references('statusid')
                  ->on('statuses')
                  ->onDelete('cascade');

            $table->foreign('techid')
                  ->references('userid')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('requestorid')
                  ->references('userid')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('descriptionid')
                  ->references('descriptionid')
                  ->on('ticketdescriptions')
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
        Schema::dropIfExists('tickets');
    }
}
