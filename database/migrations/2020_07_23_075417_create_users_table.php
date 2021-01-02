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
            $table->id();
            $table->string('fullname');
            $table->string('email')->unique();
            $table->string('username');
            $table->string('password');
            $table->string('no_npwp')->unique();
            $table->string('npwp')->nullable();
            $table->string('address')->nullable();
            $table->string("phone_number")->nullable();
            $table->string("scope")->nullable();
            $table->string("no_ktp")->nullable();
            $table->string('ktp')->nullable();
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
