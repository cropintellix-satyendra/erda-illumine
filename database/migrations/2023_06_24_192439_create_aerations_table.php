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
        Schema::create('aerations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pipe_installation_id');
            $table->bigInteger('farmer_uniqueId');
            $table->string('farmer_plot_uniqueid', 20)->index('farmer_plot_uniqueid');
            $table->integer('plot_no');
            $table->integer('aeration_no');
            $table->integer('pipe_no');
            $table->string('path')->nullable();
            $table->enum('status', ['Approved', 'Pending', 'Rejected'])->nullable()->default('Pending');
            $table->bigInteger('apprv_reject_user_id')->nullable();
            $table->string('l2_status', 50)->nullable()->default('Pending');
            $table->integer('l2_apprv_reject_user_id')->nullable();
            $table->integer('reason_id')->nullable();
            $table->integer('surveyor_id')->nullable();
            $table->string('date_survey', 20)->nullable();
            $table->time('time_survey')->nullable();
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
        Schema::dropIfExists('aerations');
    }
};
