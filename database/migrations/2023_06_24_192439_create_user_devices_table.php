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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->string('device_id')->nullable();
            $table->string('ip', 100)->nullable();
            $table->string('versioncode', 100)->nullable();
            $table->string('versionname', 100)->nullable();
            $table->string('released', 100)->nullable();
            $table->string('devicename', 100)->nullable();
            $table->string('device_manufacturer', 100)->nullable();
            $table->string('token')->nullable();
            $table->string('type', 10)->nullable();
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
        Schema::dropIfExists('user_devices');
    }
};
