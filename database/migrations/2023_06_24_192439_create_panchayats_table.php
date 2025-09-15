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
        Schema::create('panchayats', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('panchayat', 30)->nullable();
            $table->integer('taluka_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->integer('status')->nullable()->index('status');
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
        Schema::dropIfExists('panchayats');
    }
};
