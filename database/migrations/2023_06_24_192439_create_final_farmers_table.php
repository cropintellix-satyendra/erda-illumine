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
        Schema::create('final_farmers', function (Blueprint $table) {
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
            $table->string('signature_old')->nullable();
            $table->string('sign_carbon_credit', 100)->nullable();
            $table->timestamp('sign_carbon_date')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('final_status_onboarding', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending');
            $table->enum('status_onboarding', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending')->index('status_onboarding');
            $table->integer('onboarding_form')->nullable()->default(0);
            $table->string('farmer_plot_uniqueid', 100)->nullable();
            $table->string('plot_no', 10)->nullable()->index('plot_no');
            $table->string('area_in_acers', 20)->nullable();
            $table->string('land_ownership', 30)->nullable();
            $table->string('actual_owner_name', 50)->nullable();
            $table->integer('affidavit_tnc')->nullable();
            $table->string('sign_affidavit')->nullable();
            $table->timestamp('sign_affidavit_date')->nullable();
            $table->string('survey_no', 60)->nullable();
            $table->enum('final_status', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending')->index('final_status');
            $table->timestamp('L2_aprv_timestamp')->nullable();
            $table->longText('L2_aprv_remark')->nullable();
            $table->integer('L2_appr_userid')->nullable();
            $table->timestamp('L2_reject_timestamp')->nullable();
            $table->integer('L2_reject_userid')->nullable();
            $table->longText('L1_approve_comment')->nullable();
            $table->timestamp('L1_appr_timestamp')->nullable();
            $table->integer('L1_reason_id')->nullable();
            $table->string('L1_reject_comment')->nullable();
            $table->timestamp('L1_reject_timestamp')->nullable();
            $table->integer('L1_aprv_recj_userid')->nullable();
            $table->integer('cropdata_form')->nullable()->default(0);
            $table->integer('benefit_form')->nullable()->default(0);
            $table->integer('pipe_form')->nullable()->default(0);
            $table->integer('awd_form')->nullable()->default(0);
            $table->longText('farmer_sign')->nullable();
            $table->longText('plotowner_sign')->nullable();
            $table->string('plotowner_sign_old')->nullable();
            $table->longText('farmer_photo')->nullable();
            $table->string('farmer_photo_old')->nullable();
            $table->longText('aadhaar_photo')->nullable();
            $table->string('aadhaar_photo_old')->nullable();
            $table->longText('others_photo')->nullable();
            $table->string('others_photo_old')->nullable();
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
        Schema::dropIfExists('final_farmers');
    }
};
