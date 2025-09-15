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
        Schema::create('pipe_installations', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('farmer_id');
            $table->bigInteger('farmer_uniqueId');
            $table->string('farmer_plot_uniqueid', 20)->nullable()->index('farmer_plot_uniqueid');
            $table->integer('plot_no');
            $table->longText('ranges')->nullable()->comment('polygon');
            $table->string('polygon_date_time', 20)->nullable()->comment('date time while capturing polygon from app');
            $table->string('date_time', 20)->nullable()->comment('record time when surveyor submit basic data');
            $table->string('date_survey', 20)->nullable()->comment('record date when surveyor submit basic data');
            $table->bigInteger('surveyor_id')->nullable()->comment('surveyor who submitted form with basic info');
            $table->string('surveyor_name', 60)->nullable();
            $table->enum('status', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending')->index('status');
            $table->bigInteger('apprv_reject_user_id')->nullable();
            $table->string('l2_status', 50)->nullable()->default('Pending')->index('l2_status');
            $table->integer('l2_apprv_reject_user_id')->nullable();
            $table->bigInteger('surveyor_mobile')->nullable();
            $table->longText('pipes_location')->nullable()->comment('contain location, datetime and distance of pipe installed');
            $table->double('latitude', 10, 6)->nullable();
            $table->double('longitude', 10, 6)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('district', 50)->nullable();
            $table->string('taluka', 50)->nullable();
            $table->string('village', 50)->nullable();
            $table->string('khasara_no', 15)->nullable();
            $table->string('acers_units', 10)->nullable();
            $table->double('area_in_acers', 10, 2)->nullable()->comment('captured during onboarding');
            $table->double('plot_area', 10, 2)->nullable()->comment('captured during pipe installation from google map');
            $table->integer('installed_pipe')->nullable()->default(0);
            $table->integer('no_pipe_req')->nullable();
            $table->integer('no_pipe_avl')->nullable();
            $table->string('installing_pipe', 5)->nullable();
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
        Schema::dropIfExists('pipe_installations');
    }
};
