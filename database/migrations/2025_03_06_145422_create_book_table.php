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
        Schema::create('book', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        Schema::create('bookDaerah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookID');
            $table->integer('price'); 
            $table->string('daerah');
            $table->timestamps();
        });

        Schema::create('bookClass', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookID');
            $table->integer('stock'); 
            $table->integer('class');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book');
        Schema::dropIfExists('bookDaerah');
        Schema::dropIfExists('bookClass');
    }
};
