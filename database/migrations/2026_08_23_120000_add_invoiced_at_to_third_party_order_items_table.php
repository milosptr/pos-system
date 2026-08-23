<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stamped when a partial invoice (listastavki) pays the item. Lets a
     * resend tell "soft-deleted because it was paid" (skip it) apart from
     * "soft-deleted because a sync pruned it" (restore it when re-added).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('third_party_order_items', function (Blueprint $table) {
            $table->timestamp('invoiced_at')->nullable()->after('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('third_party_order_items', function (Blueprint $table) {
            $table->dropColumn('invoiced_at');
        });
    }
};
