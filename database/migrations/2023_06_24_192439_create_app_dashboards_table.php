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
        Schema::create('app_dashboards', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('state', 100)->nullable();
            $table->string('state_id', 100)->nullable();
            $table->integer('farmer_registration')->nullable()->default(0);
            $table->integer('crop_data')->nullable()->default(0);
            $table->integer('pipe_installation')->nullable()->default(0);
            $table->integer('capture_aeration')->nullable()->default(0);
            $table->integer('farmer_benefit')->nullable()->default(0);
            $table->integer('status')->nullable()->default(1)->index('status');
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
        Schema::dropIfExists('app_dashboards');
    }
};
