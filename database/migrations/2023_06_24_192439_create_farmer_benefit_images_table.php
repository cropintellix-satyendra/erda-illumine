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
        Schema::create('farmer_benefit_images', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('farmer_id')->nullable();
            $table->integer('farmer_uniqueId')->nullable();
            $table->integer('farmer_benefit_id')->nullable();
            $table->string('path')->nullable();
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
        Schema::dropIfExists('farmer_benefit_images');
    }
};
