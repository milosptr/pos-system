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
        Schema::create('exceptions', function (Blueprint $table) {
          $table->id();
          $table->string('type')->nullable();
          $table->text('message');
          $table->longText('stack_trace');
          $table->string('file')->nullable();
          $table->string('method')->nullable();
          $table->json('payload')->nullable();
          $table->integer('status_code')->nullable();
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
        Schema::dropIfExists('exceptions');
    }
};
