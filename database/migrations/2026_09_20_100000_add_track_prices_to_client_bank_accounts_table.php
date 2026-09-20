<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Struja, voda and the rest bill a different amount every month for the
     * same line, so their "price changes" are noise in the price screen.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_bank_accounts', function (Blueprint $table) {
            $table->boolean('track_prices')->default(true)->after('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_bank_accounts', function (Blueprint $table) {
            $table->dropColumn('track_prices');
        });
    }
};
