<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'api_ayat_identifier',
        'surah_number',
        'ayah_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSurahName()
    {
        $surahNames = [
            1 => "Al-Fatihah",
            2 => "Al-Baqarah",
            3 => "Ali 'Imran",
            4 => "An-Nisa",
            5 => "Al-Ma'idah",
            6 => "Al-An'am",
            7 => "Al-A'raf",
            8 => "Al-Anfal",
            9 => "At-Tawbah",
            10 => "Yunus",
            // Add more surah names as needed
        ];
        
        return $surahNames[$this->surah_number] ?? "Surah {$this->surah_number}";
    }
} 