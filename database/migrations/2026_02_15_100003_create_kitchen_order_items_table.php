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
        Schema::create('kitchen_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_order_id')->constrained()->cascadeOnDelete();
            $table->integer('external_item_id')->nullable();
            $table->string('name');
            $table->decimal('qty', 8, 2)->default(1);
            $table->string('modifier')->nullable();
            $table->boolean('storno')->default(false);
            $table->boolean('is_done')->default(false);
            $table->timestamps();
        });

        Schema::table('kitchen_orders', function (Blueprint $table) {
            $table->dropColumn('items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kitchen_orders', function (Blueprint $table) {
            $table->json('items')->after('table_name');
        });

        Schema::dropIfExists('kitchen_order_items');
    }
};
