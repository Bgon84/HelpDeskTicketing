<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketattachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticketattachments', function (Blueprint $table) {
            $table->increments('attachmentid');
            $table->integer('ticketid');
            $table->string('attachmentpath');
            $table->timestamps();

            $table->foreign('ticketid')
                  ->references('ticketid')
                  ->on('tickets')
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
        Schema::dropIfExists('ticketattachments');
    }
}
