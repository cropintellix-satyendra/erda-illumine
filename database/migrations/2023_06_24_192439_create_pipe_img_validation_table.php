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
        Schema::create('pipe_img_validation', function (Blueprint $table) {
            $table->integer('id', true);
            $table->bigInteger('farmer_uniqueId')->nullable();
            $table->string('farmer_plot_uniqueid', 50)->nullable()->index('farmer_plot_uniqueid');
            $table->bigInteger('plot_no')->nullable();
            $table->integer('pipe_no')->nullable();
            $table->string('status', 50)->nullable()->index('status');
            $table->string('level', 100)->nullable();
            $table->bigInteger('user_id')->nullable()->comment('approve or reject user id');
            $table->longText('comment')->nullable();
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
        Schema::dropIfExists('pipe_img_validation');
    }
};
