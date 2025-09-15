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
        Schema::create('farmer_benefits', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('farmer_id');
            $table->integer('farmer_uniqueId');
            $table->string('farmer_plot_uniqueid', 20)->nullable()->index('farmer_plot_uniqueid');
            $table->double('total_plot_area', 10, 3)->nullable();
            $table->string('seasons', 30)->nullable();
            $table->integer('benefit_id')->nullable();
            $table->string('benefit', 30)->nullable();
            $table->string('date_survey', 20)->nullable();
            $table->string('date_time', 20)->nullable();
            $table->enum('status', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending')->index('status');
            $table->integer('apprv_reject_user_id')->nullable();
            $table->string('l2_status', 50)->nullable()->default('Pending')->index('l2_status');
            $table->integer('l2_apprv_reject_user_id')->nullable();
            $table->integer('surveyor_id');
            $table->string('surveyor_name', 30);
            $table->string('surveyor_email', 30);
            $table->bigInteger('surveyor_mobile');
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
        Schema::dropIfExists('farmer_benefits');
    }
};
