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
        Schema::create('client_invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_invoice_id');
            $table->integer('position')->nullable();
            $table->string('sku', 64)->nullable();
            $table->string('name')->index();
            $table->decimal('quantity', 14, 3)->default(0);
            $table->string('unit', 32)->nullable();
            $table->decimal('unit_price', 16, 4)->default(0);
            $table->decimal('unit_price_gross', 16, 4)->nullable();
            $table->decimal('net_amount', 18, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('client_invoice_id')
                ->references('id')
                ->on('client_invoices')
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
        Schema::dropIfExists('client_invoice_items');
    }
};
