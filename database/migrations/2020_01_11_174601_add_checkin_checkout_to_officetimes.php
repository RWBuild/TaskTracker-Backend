<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCheckinCheckoutToOfficetimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('office_times', function (Blueprint $table) {
            $table->boolean('has_checked_in')->after('user_id')->default(1);
            $table->boolean('has_checked_out')->after('has_checked_in')->default(0);
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
            $table->dropColumn('has_checked_in');
            $table->dropColumn('has_checked_out');
        });
    }
}
