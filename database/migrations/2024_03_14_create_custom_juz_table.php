<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('custom_juz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('juz_number');
            $table->string('edition')->default('quran-simple-enhanced');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_juz');
    }
}; 