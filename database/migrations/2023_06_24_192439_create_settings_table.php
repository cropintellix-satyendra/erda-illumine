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
        Schema::create('settings', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('terms_and_conditions')->nullable();
            $table->text('carbon_credit')->nullable();
            $table->longText('app_privacypolicy')->nullable();
            $table->longText('app_termncond')->nullable();
            $table->longText('cquest_tnc_cquest')->nullable();
            $table->longText('cquest_privacypolicy_cquest')->nullable();
            $table->string('otpTime', 20)->nullable();
            $table->integer('no_of_hectares')->nullable();
            $table->integer('preparation_date_interval')->nullable();
            $table->integer('transplantation_date_interval')->nullable();
            $table->double('threshold_pipe_installation', 10, 2)->nullable();
            $table->string('cropdata_end_days', 200)->nullable();
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
        Schema::dropIfExists('settings');
    }
};
