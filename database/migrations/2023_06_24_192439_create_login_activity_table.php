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
        Schema::create('login_activity', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('rolename')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('type')->nullable();
            $table->string('ip')->nullable();
            $table->longText('log')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('login_activity');
    }
};
