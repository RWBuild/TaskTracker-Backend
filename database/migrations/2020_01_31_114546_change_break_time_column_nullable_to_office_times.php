<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBreakTimeColumnNullableToOfficeTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('office_times', function (Blueprint $table) {
            $table->datetime('break_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('office_times', function (Blueprint $table) {
            $table->datetime('break_time')->nullable(false)->change();
        });
    }
}
