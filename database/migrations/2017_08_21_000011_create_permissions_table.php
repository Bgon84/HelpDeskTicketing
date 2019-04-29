<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('permissionid');
            $table->string('permission')->unique();
            $table->text('description');
            $table->timestamps();
        });

            // Initial inserts
        DB::table('permissions')->insert([
            ['permission' => 'View_Admin_Dashboard', 'description' => 'Gives user the ability to view Admin Dashboard']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
