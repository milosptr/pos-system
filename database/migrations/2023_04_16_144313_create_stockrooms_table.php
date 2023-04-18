<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stockrooms', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inventory_id')->references('id')->on('inventory');
            $table->string('sku');
            $table->string('item');
            $table->integer('qty')->default(1);
            $table->integer('tax')->default(20);
            $table->string('unit')->nullable();
            $table->bigInteger('total');
            $table->integer('type')->default(1); // 1 = imported, 2 = backoffice
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
        Schema::dropIfExists('stockrooms');
    }
};
