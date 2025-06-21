<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PersonalNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'api_ayat_identifier',
        'surah_number',
        'ayah_number',
        'note',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
