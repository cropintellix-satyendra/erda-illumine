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
        Schema::create('pipe_installation_pipeimg', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('farmer_uniqueId')->nullable();
            $table->string('farmer_plot_uniqueid', 20)->nullable()->index('farmer_plot_uniqueid');
            $table->integer('plot_no')->nullable();
            $table->integer('pipe_no')->nullable();
            $table->string('lat', 40)->nullable();
            $table->string('lng', 40)->nullable();
            $table->string('images')->nullable();
            $table->string('status', 20)->nullable()->default('Pending')->index('status');
            $table->string('l2status', 50)->nullable()->default('Pending')->index('l2status');
            $table->integer('distance')->nullable();
            $table->string('date', 20)->nullable()->comment('image submission date/time');
            $table->string('time', 20)->nullable()->comment('image submission date/time');
            $table->integer('reason_id')->nullable();
            $table->integer('trash')->nullable()->default(0);
            $table->integer('l2trash')->nullable()->default(0);
            $table->integer('surveyor_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status'], 'status_2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pipe_installation_pipeimg');
    }
};
