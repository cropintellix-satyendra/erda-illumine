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
        Schema::create('farmer_image', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('farmer_id', 10)->nullable();
            $table->string('farmer_unique_id', 10)->nullable();
            $table->string('image', 30)->nullable();
            $table->string('path', 90)->nullable();
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
        Schema::dropIfExists('farmer_image');
    }
};
