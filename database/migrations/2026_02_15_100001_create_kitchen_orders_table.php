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
        Schema::create('kitchen_orders', function (Blueprint $table) {
            $table->id();
            $table->string('orderable_type');
            $table->string('orderable_id');
            $table->string('table_name');
            $table->json('items');
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();

            $table->unique(['orderable_type', 'orderable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kitchen_orders');
    }
};
