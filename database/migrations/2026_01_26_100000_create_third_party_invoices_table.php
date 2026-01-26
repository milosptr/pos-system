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
        Schema::create('third_party_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->index();
            $table->boolean('is_duplicate')->default(false);
            $table->bigInteger('external_order_id')->nullable()->index();
            $table->string('table_name')->nullable();
            $table->integer('status')->default(1);
            $table->text('order');
            $table->integer('total');
            $table->integer('payment_type')->nullable();
            $table->float('discount')->default(0);
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
        Schema::dropIfExists('third_party_invoices');
    }
};
