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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->references('id')->on('categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(1);
            $table->smallInteger('sold_by')->default(1);
            $table->integer('price');
            $table->string('sku')->nullable();
            $table->integer('qty')->nullable();
            $table->string('color')->nullable();
            $table->unsignedInteger('order')->default(0);
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
        Schema::dropIfExists('inventory');
    }
};
