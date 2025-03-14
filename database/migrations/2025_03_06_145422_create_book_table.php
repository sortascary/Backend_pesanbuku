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
        
        Schema::create('bookDaerahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookClassID');
            $table->integer('price'); 
            $table->string('daerah');
            $table->timestamps();
        });

        Schema::create('bookClass', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookDaerahID');
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
        Schema::dropIfExists('bookDaerahs');
        Schema::dropIfExists('bookClass');
    }
};
