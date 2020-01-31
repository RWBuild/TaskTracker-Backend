<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBreakTimeColumnToOfficeTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('office_times', function (Blueprint $table) {
            $table->datetime('break_time')->after('checkout_time');
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
            $table->dropColumn('break_time');
        });
    }
}
