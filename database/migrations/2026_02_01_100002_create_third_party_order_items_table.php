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
        Schema::create('third_party_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('third_party_order_id');
            $table->bigInteger('external_item_id')->index();
            $table->string('name');
            $table->decimal('qty', 10, 2);
            $table->integer('price');
            $table->string('unit')->default('kom');
            $table->string('modifier')->nullable();
            $table->integer('print_station_id')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->timestamps();

            $table->foreign('third_party_order_id')
                ->references('id')
                ->on('third_party_orders')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('third_party_order_items');
    }
};
