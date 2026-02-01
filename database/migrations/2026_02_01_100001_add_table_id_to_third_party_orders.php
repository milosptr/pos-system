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
        Schema::table('third_party_orders', function (Blueprint $table) {
            $table->bigInteger('table_id')->nullable()->after('external_order_id')->index();
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
            $table->dropIndex(['table_id']);
            $table->dropColumn('table_id');
        });
    }
};
