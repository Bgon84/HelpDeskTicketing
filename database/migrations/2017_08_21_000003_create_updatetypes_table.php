<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateUpdatetypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('updatetypes', function (Blueprint $table) {
            $table->increments('updatetypeid');
            $table->string('updatetype')->unique();
            $table->timestamps();
        });

        // Initial inserts
        DB::table('updatetypes')->insert([
            ['updatetype' => 'Status Change'],
            ['updatetype' => 'Priority Change'],
            ['updatetype' => 'Category Change'],
            ['updatetype' => 'Tech Change'],
            ['updatetype' => 'Public Note Added'],
            ['updatetype' => 'Internal Note Added'],
            ['updatetype' => 'Attachment Added']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('updatetypes');
    }
}
