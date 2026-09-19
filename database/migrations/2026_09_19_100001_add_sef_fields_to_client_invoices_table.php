<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * sef_id is the import's idempotency key. Nullable so invoices entered by
     * hand in the backoffice stay valid, unique so a resent invoice is
     * recognised and left alone.
     *
     * supplier_pib and supplier_bank_account are a snapshot of who was billing
     * at the time: the supplier record moves on when an account changes, an
     * invoice must not.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('sef_id')->nullable()->after('id')->unique();
            $table->string('invoice_number', 64)->nullable()->after('sef_id')->index();
            $table->string('supplier_pib', 20)->nullable()->after('client_account');
            $table->string('supplier_bank_account', 64)->nullable()->after('supplier_pib');
            $table->string('payment_model', 8)->nullable()->after('reference_number');
            $table->date('issue_date')->nullable()->after('payment_model');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_invoices', function (Blueprint $table) {
            $table->dropUnique(['sef_id']);
            $table->dropColumn([
                'sef_id',
                'invoice_number',
                'supplier_pib',
                'supplier_bank_account',
                'payment_model',
                'issue_date',
            ]);
        });
    }
};
