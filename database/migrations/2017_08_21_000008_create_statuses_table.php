<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->increments('statusid');
            $table->string('status')->unique();            
            $table->timestamps();
        });

        // Initial inserts
        DB::table('statuses')->insert([
            ['status' => 'Submitted'],
            ['status' => 'In Progress'],
            ['status' => 'Resolved'],
            ['status' => 'Voided'],
            ['status' => 'Frozen'],
            ['status' => 'Escalated'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statuses');
    }
}
