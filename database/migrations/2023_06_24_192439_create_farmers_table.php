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
        Schema::create('farmers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('surveyor_id')->nullable();
            $table->string('surveyor_name', 50)->nullable();
            $table->string('surveyor_email', 30)->nullable();
            $table->bigInteger('surveyor_mobile')->nullable();
            $table->string('aadhaar', 100)->nullable();
            $table->string('organization_id', 10)->nullable();
            $table->bigInteger('farmer_uniqueId')->nullable();
            $table->string('farmer_name', 30);
            $table->string('mobile_access', 100)->nullable();
            $table->string('mobile_reln_owner', 50)->nullable();
            $table->bigInteger('mobile')->nullable();
            $table->integer('mobile_verified')->nullable();
            $table->string('gender', 50)->nullable();
            $table->string('guardian_name')->nullable();
            $table->bigInteger('no_of_plots')->nullable();
            $table->double('total_plot_area', 10, 2)->nullable()->default(0);
            $table->integer('country_id')->nullable();
            $table->string('country', 30)->nullable();
            $table->integer('state_id')->nullable();
            $table->string('state', 30)->nullable();
            $table->integer('district_id')->nullable();
            $table->string('district', 30)->nullable();
            $table->integer('taluka_id')->nullable();
            $table->string('taluka', 30)->nullable();
            $table->integer('panchayat_id')->nullable();
            $table->string('panchayat', 30)->nullable();
            $table->integer('village_id')->nullable();
            $table->string('village', 30)->nullable();
            $table->string('latitude', 30)->nullable()->default('0');
            $table->string('longitude', 30)->nullable()->default('0');
            $table->date('date_survey')->nullable();
            $table->time('time_survey')->nullable();
            $table->integer('check_carbon_credit')->nullable()->default(0);
            $table->string('signature')->nullable();
            $table->string('sign_carbon_credit', 100)->nullable();
            $table->timestamp('sign_carbon_date')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('farmer_status', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending');
            $table->text('reject_remark')->nullable();
            $table->timestamp('reject_timestamp')->nullable();
            $table->longText('status_onboarding_plot')->nullable();
            $table->enum('final_status_onboarding', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending');
            $table->enum('status_onboarding', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending');
            $table->integer('onboarding_form')->nullable()->default(0);
            $table->enum('status_cropdata', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending');
            $table->integer('cropdata_form')->nullable()->default(0);
            $table->enum('status_benefits', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending');
            $table->integer('benefit_form')->nullable()->default(0);
            $table->enum('status_awd', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending');
            $table->integer('awd_form')->nullable()->default(0);
            $table->longText('farmer_sign')->nullable();
            $table->longText('plotowner_sign')->nullable();
            $table->longText('farmer_photo')->nullable();
            $table->longText('aadhaar_photo')->nullable();
            $table->longText('others_photo')->nullable();
            $table->enum('status_pipes', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending')->index('status_pipes');
            $table->integer('pipes_form')->nullable()->default(0);
            $table->enum('status_other', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending');
            $table->integer('other_form')->nullable()->default(0);
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
        Schema::dropIfExists('farmers');
    }
};
