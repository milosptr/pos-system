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
        Schema::create('client_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_account')->references('id')->on('client_bank_accounts');
            $table->text('reference_number')->nullable()->default(null);
            $table->timestamp('payment_deadline')->useCurrent();
            $table->timestamp('transaction_date')->nullable()->default(null);
            $table->timestamp('processed_at')->nullable()->default(null);
            $table->double('amount');
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('client_invoices');
    }
};
