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
        Schema::create('warehouse_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("warehouse_id")->foreign("warehouse_id")->references("id")->on("warehouse");
            $table->unsignedBigInteger("inventory_id")->foreign("inventory_id")->references("id")->on("inventory")->nullable();
            $table->float('quantity')->default(0);
            $table->float('previous_status')->default(0);
            $table->integer('type')->default(1);
            $table->string('comment')->nullable();
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
        Schema::dropIfExists('warehouse_status');
    }
};
