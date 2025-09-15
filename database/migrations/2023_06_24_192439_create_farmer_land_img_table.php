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
        Schema::create('farmer_land_img', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('farmer_id', 10)->nullable();
            $table->string('farmer_unique_id', 10)->nullable();
            $table->integer('plot_no')->nullable();
            $table->string('image', 30)->nullable();
            $table->string('path')->nullable();
            $table->string('status', 30)->nullable()->default('Approved')->index('status');
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
        Schema::dropIfExists('farmer_land_img');
    }
};
