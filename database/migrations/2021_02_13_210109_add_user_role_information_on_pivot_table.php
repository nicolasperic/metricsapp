<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserRoleInformationOnPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_user', function (Blueprint $table) {
            $table->string('user_role')->nullable()->after('user_id');
            $table->unsignedInteger('user_status')->nullable()->after('user_role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_user', function (Blueprint $table) {
            //
        });
    }
}
