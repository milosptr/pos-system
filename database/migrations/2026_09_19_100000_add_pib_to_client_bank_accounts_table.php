<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Indexed but not unique: hand-entered suppliers can legitimately be
     * duplicated, and a unique index would turn the import's PIB backfill
     * into a constraint error.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_bank_accounts', function (Blueprint $table) {
            $table->string('pib', 20)->nullable()->after('name')->index();
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
            $table->dropColumn('pib');
        });
    }
};
