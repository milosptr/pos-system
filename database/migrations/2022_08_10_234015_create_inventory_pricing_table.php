<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_pricing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id')->references('id')->on('inventory');
            $table->integer('purchase_price');
            $table->integer('retail_price');
            $table->float('norm');
            $table->dateTime('date')->default(Carbon::now());
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
        Schema::dropIfExists('inventory_pricing');
    }
};
