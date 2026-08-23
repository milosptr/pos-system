<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stamped when an invoice closes the order (both the listastavki and the
     * stoid paths). Lets the sync endpoint tell "closed by an invoice" apart
     * from "soft-deleted by the 4am cleanup" when the external system resends
     * an order id.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('third_party_orders', function (Blueprint $table) {
            $table->timestamp('invoiced_at')->nullable()->after('ordered_at');
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
            $table->dropColumn('invoiced_at');
        });
    }
};
