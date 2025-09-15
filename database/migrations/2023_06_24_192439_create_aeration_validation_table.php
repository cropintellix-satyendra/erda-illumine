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
        Schema::create('aeration_validation', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('pipe_installation_id')->nullable();
            $table->bigInteger('farmer_uniqueId')->nullable();
            $table->string('farmer_plot_uniqueid', 60)->nullable()->index('farmer_plot_uniqueid');
            $table->integer('plot_no')->nullable();
            $table->integer('aeration_no')->nullable();
            $table->integer('pipe_no')->nullable();
            $table->string('level', 60)->nullable();
            $table->string('status', 30)->nullable();
            $table->longText('comment')->nullable();
            $table->integer('apprv_reject_user_id')->nullable();
            $table->integer('reject_reason_id')->nullable();
            $table->timestamp('timestamp')->nullable();
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
        Schema::dropIfExists('aeration_validation');
    }
};
