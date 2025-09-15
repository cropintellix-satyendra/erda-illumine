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
        Schema::create('aeration_images', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pipe_installation_id')->nullable();
            $table->integer('farmer_uniqueId')->nullable();
            $table->string('farmer_plot_uniqueid', 20)->nullable()->index('farmer_plot_uniqueid');
            $table->integer('plot_no')->nullable();
            $table->integer('aeration_no')->nullable();
            $table->integer('pipe_no');
            $table->string('path')->nullable();
            $table->string('status', 20)->nullable()->default('Pending');
            $table->integer('trash')->default(0);
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
        Schema::dropIfExists('aeration_images');
    }
};
