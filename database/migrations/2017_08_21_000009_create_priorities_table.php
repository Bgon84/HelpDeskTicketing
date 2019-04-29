<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreatePrioritiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('priorities', function (Blueprint $table) {
            $table->increments('priorityid');
            $table->integer('priority')->unique();
            $table->timestamps();
        });

        // Initial inserts
        DB::table('priorities')->insert([
            ['priority' => 1],
            ['priority' => 2],
            ['priority' => 3],
            ['priority' => 4],
            ['priority' => 5],
            ['priority' => 6],
            ['priority' => 7],
            ['priority' => 8],
            ['priority' => 9],
            ['priority' => 10],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('priorities');
    }
}
