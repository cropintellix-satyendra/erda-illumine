<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
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
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->bigInteger('mobile')->nullable();
            $table->string('password');
            $table->string('company_code', 6)->nullable();
            $table->integer('state_id')->nullable();
            $table->rememberToken();
            $table->string('role', 20)->nullable();
            $table->integer('status')->default(0);
            $table->timestamp('last_login')->nullable();
            $table->string('ip')->nullable()->comment('ip address');
            $table->softDeletes();
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
};
