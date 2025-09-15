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
        Schema::create('plots_status_record', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('farmer_uniqueId')->nullable();
            $table->string('farmer_plot_uniqueid', 20)->nullable()->index('farmer_plot_uniqueid');
            $table->integer('plot_no')->nullable();
            $table->string('level', 20)->nullable();
            $table->string('module', 100)->nullable();
            $table->string('status', 20)->nullable()->index('status');
            $table->longText('comment')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('reject_reason_id')->nullable();
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
        Schema::dropIfExists('plots_status_record');
    }
};
