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
        Schema::create('old_polygons', function (Blueprint $table) {
            $table->integer('id', true);
            $table->bigInteger('farmer_uniqueId')->nullable();
            $table->string('farmer_plot_uniqueid', 200)->nullable();
            $table->integer('plot_no')->nullable();
            $table->longText('polygon')->nullable();
            $table->double('google_plot_area', 10, 2)->nullable();
            $table->string('type', 30)->nullable();
            $table->integer('surveyor_id')->nullable();
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
        Schema::dropIfExists('old_polygons');
    }
};
