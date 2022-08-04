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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->references('id')->on('invoices');
            $table->unsignedBigInteger('table_id')->references('id')->on('tables');
            $table->unsignedBigInteger('inventory_id')->references('id')->on('inventory');
            $table->unsignedBigInteger('category_id')->references('id')->on('categories');
            $table->string('name');
            $table->text('category_name')->nullable();
            $table->integer('price');
            $table->integer('total');
            $table->integer('status')->default(1);
            $table->float('qty')->nullable();
            $table->string('sku')->nullable();
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
        Schema::dropIfExists('sales');
    }
};
