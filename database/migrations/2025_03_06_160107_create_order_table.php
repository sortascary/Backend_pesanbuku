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
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('schoolName')->nullable();
            $table->string('phone');
            $table->enum('daerah', ['Demak', 'Jepara', 'Kudus']);
            $table->enum('payment', ['cash', 'transfer', 'angsuran']);
            $table->enum('status', ['dipesan', 'diproses', 'done'])->default('dipesan');
            $table->integer('total_book_price'); 
            $table->timestamp('done_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        //TODO: the name is not gonna show up on deletion of a book (add soft delete)
        Schema::create('order_books', function (Blueprint $table) { 
            $table->id();
            $table->string('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreignId('book_class_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->boolean('isDone')->default(false); //is it packed yet
            $table->integer('amount'); 
            $table->integer('bought_price'); 
            $table->integer('subtotal'); 
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_books');
        Schema::dropIfExists('orders');
    }
};
