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
        Schema::table('warehouse_status', function (Blueprint $table) {
            // Covering index: allows DB to answer query entirely from index
            // without accessing table rows (faster for large aggregations)
            $table->index(['date', 'warehouse_id', 'type', 'quantity'], 'ws_covering_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_status', function (Blueprint $table) {
            $table->dropIndex('ws_covering_idx');
        });
    }
};
