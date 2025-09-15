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
        Schema::create('cropdata_detail', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('farmer_cropdata_id')->nullable();
            $table->string('crop_season_lastyrs', 20)->nullable();
            $table->string('crop_season_currentyrs', 20)->nullable();
            $table->string('crop_variety_lastyrs', 20)->nullable();
            $table->string('crop_variety_currentyrs', 20)->nullable();
            $table->string('fertilizer_1_name', 50)->nullable();
            $table->string('fertilizer_1_lastyrs', 50)->nullable();
            $table->string('fertilizer_1_currentyrs', 50)->nullable();
            $table->string('fertilizer_2_name', 50)->nullable();
            $table->string('fertilizer_2_lastyrs', 50)->nullable();
            $table->string('fertilizer_2_currentyrs', 50)->nullable();
            $table->string('fertilizer_3_name', 50)->nullable();
            $table->string('fertilizer_3_lastyrs', 50)->nullable();
            $table->string('fertilizer_3_currentyrs', 50)->nullable();
            $table->string('water_mng_lastyrs', 20)->nullable();
            $table->string('water_mng_currentyrs', 20)->nullable();
            $table->string('yeild_lastyrs', 20)->nullable();
            $table->string('yeild_currentyrs', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cropdata_detail');
    }
};
