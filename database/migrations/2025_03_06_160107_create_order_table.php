<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userID');
            $table->integer('totalCost');
            $table->string('daerah');
            $table->string('phone')->nullable();
            $table->string('payment');
            $table->boolean('isPayed');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('order_Details', function (Blueprint $table){
            $table->id();
            $table->foreignId('orderID');
            $table->foreignId('orderBookID');
            $table->integer('totalBookPrice');
        });

        Schema::create('order_Books', function (Blueprint $table){
            $table->id();
            $table->foreignId('bookClassID');
            $table->boolean('isDone');
            $table->integer('ammount');
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('orderDetails');
        Schema::dropIfExists('orderBooks');
    }
};
