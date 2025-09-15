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
        Schema::create('benefitdata_validation', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('farmer_uniqueId', 30)->nullable();
            $table->integer('benefit_id')->nullable();
            $table->string('level', 50)->nullable();
            $table->string('status', 50)->nullable()->index('status');
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
        Schema::dropIfExists('benefitdata_validation');
    }
};
