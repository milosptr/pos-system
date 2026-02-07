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
            $table->softDeletes();
        });

        Schema::table('third_party_order_items', function (Blueprint $table) {
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
        Schema::table('third_party_orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('third_party_order_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
