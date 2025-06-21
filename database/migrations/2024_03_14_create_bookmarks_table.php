<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('api_ayat_identifier');
            $table->integer('surah_number');
            $table->integer('ayah_number');
            $table->timestamps();
            
            // Add unique constraint to prevent duplicate bookmarks
            $table->unique(['user_id', 'api_ayat_identifier']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookmarks');
    }
}; 