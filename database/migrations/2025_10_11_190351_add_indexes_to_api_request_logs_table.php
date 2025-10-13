<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToApiRequestLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            // Only add composite indexes (single indexes already exist)
            $table->index(['method', 'response_status'], 'method_status_index');
            $table->index(['created_at', 'method'], 'created_method_index');
            $table->index(['user_id', 'created_at'], 'user_created_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            // Drop only composite indexes
            $table->dropIndex('method_status_index');
            $table->dropIndex('created_method_index');
            $table->dropIndex('user_created_index');
        });
    }
}