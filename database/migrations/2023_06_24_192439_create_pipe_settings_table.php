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
        Schema::create('pipe_settings', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('unit', 10);
            $table->double('area', 10, 6);
            $table->integer('no_of_pipe');
            $table->enum('type', ['acres', 'hectare']);
            $table->integer('status')->default(0)->index('status');
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
        Schema::dropIfExists('pipe_settings');
    }
};
