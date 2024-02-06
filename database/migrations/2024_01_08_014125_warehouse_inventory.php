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
        Schema::create('warehouse_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("inventory_id")->foreign("inventory_id")->references("id")->on("inventory");
            $table->unsignedBigInteger("warehouse_id")->foreign("warehouse_id")->references("id")->on("warehouse");
            $table->float('norm')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_inventory');
    }
};
