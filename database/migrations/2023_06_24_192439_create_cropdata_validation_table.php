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
        Schema::create('cropdata_validation', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('farmer_plot_uniqueid', 30)->nullable()->index('farmer_plot_uniqueid');
            $table->integer('plot_no')->nullable();
            $table->string('level', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->integer('user_id')->nullable();
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
        Schema::dropIfExists('cropdata_validation');
    }
};
