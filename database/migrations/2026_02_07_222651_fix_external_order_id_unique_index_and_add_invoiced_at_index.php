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
        // C1: Drop unique constraint on external_order_id — soft-deleted rows
        // stay in DB so the external system can reuse IDs on subsequent days.
        Schema::table('third_party_orders', function (Blueprint $table) {
            $table->dropUnique(['external_order_id']);
            $table->index('external_order_id');
        });

        // W3: Add index on invoiced_at for COALESCE(invoiced_at, created_at) queries.
        // MySQL can't use a B-tree index on COALESCE directly, but an index on
        // invoiced_at still helps the optimizer when invoiced_at IS NOT NULL.
        Schema::table('third_party_invoices', function (Blueprint $table) {
            $table->index('invoiced_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('third_party_orders', function (Blueprint $table) {
            $table->dropIndex(['external_order_id']);
            $table->unique('external_order_id');
        });

        Schema::table('third_party_invoices', function (Blueprint $table) {
            $table->dropIndex(['invoiced_at']);
        });
    }
};
