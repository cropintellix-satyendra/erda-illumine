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
        Schema::create('register_otp', function (Blueprint $table) {
            $table->integer('id', true);
            $table->bigInteger('mobile');
            $table->integer('otp');
            $table->string('otp_time', 20)->nullable();
            $table->string('status', 30);
            $table->string('ip')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('register_otp');
    }
};
