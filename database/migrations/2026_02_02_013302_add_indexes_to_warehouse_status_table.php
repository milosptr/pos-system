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
            // Composite index for main warehouse status query (filters by date range, groups by warehouse_id)
            $table->index(['warehouse_id', 'date', 'type'], 'ws_warehouse_date_type_idx');
            // Index for date range filtering
            $table->index('date', 'ws_date_idx');
            // Index for type filtering (used in imports, recalculate queries)
            $table->index('type', 'ws_type_idx');
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
            $table->dropIndex('ws_warehouse_date_type_idx');
            $table->dropIndex('ws_date_idx');
            $table->dropIndex('ws_type_idx');
        });
    }
};
