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
        Schema::table('kitchen_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('external_item_id');
            $table->string('sku')->nullable()->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kitchen_order_items', function (Blueprint $table) {
            $table->dropColumn(['category_id', 'sku']);
        });
    }
};
