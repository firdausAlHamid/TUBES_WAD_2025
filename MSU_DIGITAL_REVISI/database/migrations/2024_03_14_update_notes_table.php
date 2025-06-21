<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('notes')) {
            Schema::create('notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('api_ayat_identifier');
                $table->text('note');
                $table->timestamps();
            });
        } else {
            Schema::table('notes', function (Blueprint $table) {
                if (!Schema::hasColumn('notes', 'api_ayat_identifier')) {
                    $table->string('api_ayat_identifier')->after('user_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('notes')) {
            if (Schema::hasColumn('notes', 'api_ayat_identifier')) {
                Schema::table('notes', function (Blueprint $table) {
                    $table->dropColumn('api_ayat_identifier');
                });
            }
        }
    }
}; 