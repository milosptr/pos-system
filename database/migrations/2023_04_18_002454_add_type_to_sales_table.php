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
        Schema::table('sales', function (Blueprint $table) {
            $table->integer('type')->default(1)->after('sku');
            $table->uuid('batch_id')->nullable()->after('type');
            $table->unsignedBigInteger('invoice_id')->nullable()->change();
            $table->unsignedBigInteger('table_id')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // remove type column from sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('batch_id');
            $table->unsignedBigInteger('invoice_id')->nullable(false)->change();
            $table->unsignedBigInteger('table_id')->nullable(false)->change();
        });
    }
};
