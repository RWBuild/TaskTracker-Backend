<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('names');
            $table->string('email')->unique();
            $table->integer('murugo_user_id')->unique();
            $table->string('avatar');
            $table->boolean('has_checked')->default(0)->comment("to check if the user has checked in");
            // $table->timestamp('email_verified_at')->nullable();
            // $table->string('password');
            //$table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
