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
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('payment'); // cash, transfer, or angsuran
            $table->boolean('isPayed');
            $table->string('status'); // dipesan, diproses, done
            $table->integer('total_book_price'); 
            $table->timestamps();
        });

        Schema::create('order_books', function (Blueprint $table) { 
            $table->id();
            $table->string('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreignId('book_class_id')->constrained()->cascadeOnDelete();
            $table->boolean('isDone');
            $table->integer('amount'); 
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('order_books');
        Schema::dropIfExists('orders');
    }
};
