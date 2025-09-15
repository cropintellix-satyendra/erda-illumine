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
        Schema::create('farmer_plot_detail', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('farmer_id');
            $table->bigInteger('farmer_uniqueId');
            $table->string('farmer_plot_uniqueid', 50)->nullable()->index('farmer_plot_uniqueid');
            $table->string('plot_no', 11);
            $table->string('area_in_acers', 20)->nullable();
            $table->string('area_in_other', 20)->nullable();
            $table->string('area_in_other_unit', 20)->nullable();
            $table->string('area_acre_awd', 20)->nullable();
            $table->string('area_other_awd', 20)->nullable();
            $table->string('area_other_awd_unit', 20)->nullable();
            $table->string('patta_number', 100)->nullable();
            $table->string('daag_number', 100)->nullable();
            $table->string('khatha_number', 20)->nullable();
            $table->string('pattadhar_number', 20)->nullable();
            $table->string('khatian_number', 20)->nullable();
            $table->string('land_ownership', 30)->nullable();
            $table->string('actual_owner_name', 30)->nullable();
            $table->integer('affidavit_tnc')->nullable();
            $table->string('sign_affidavit')->nullable();
            $table->timestamp('sign_affidavit_date')->nullable();
            $table->string('survey_no', 60)->nullable();
            $table->enum('final_status', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending');
            $table->timestamp('finalaprv_timestamp')->nullable();
            $table->longText('finalaprv_remark')->nullable();
            $table->integer('finalappr_userid')->nullable();
            $table->timestamp('finalreject_timestamp')->nullable();
            $table->integer('finalreject_userid')->nullable();
            $table->enum('status', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending')->index('status');
            $table->longText('approve_comment')->nullable();
            $table->string('reject_comment')->nullable();
            $table->timestamp('reject_timestamp')->nullable();
            $table->timestamp('appr_timestamp')->nullable();
            $table->integer('aprv_recj_userid')->nullable();
            $table->integer('check_update')->nullable()->default(0);
            $table->integer('reason_id')->nullable();
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
        Schema::dropIfExists('farmer_plot_detail');
    }
};
