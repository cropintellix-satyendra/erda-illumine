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
        Schema::create('farmer_cropdata', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('farmer_id');
            $table->integer('farmer_uniqueId');
            $table->string('farmer_plot_uniqueid', 20)->nullable()->index('farmer_plot_uniqueid');
            $table->integer('plot_no');
            $table->string('area_in_acers', 30)->nullable();
            $table->string('season', 30)->nullable();
            $table->string('dt_irrigation_last', 20)->nullable();
            $table->string('crop_variety', 50)->nullable();
            $table->string('dt_ploughing', 20)->nullable();
            $table->string('dt_transplanting', 20)->nullable();
            $table->enum('status', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending')->index('status');
            $table->integer('apprv_reject_user_id')->nullable();
            $table->string('l2_status', 60)->nullable()->default('Pending')->index('l2_status');
            $table->integer('l2_apprv_reject_user_id')->nullable();
            $table->integer('surveyor_id')->nullable();
            $table->string('surveyor_name', 30)->nullable();
            $table->string('surveyor_email')->nullable();
            $table->bigInteger('surveyor_mobile')->nullable();
            $table->string('date_survey', 12)->nullable();
            $table->string('date_time', 12)->nullable();
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
        Schema::dropIfExists('farmer_cropdata');
    }
};
