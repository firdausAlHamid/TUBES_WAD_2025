<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomJuz extends Model
{
    use HasFactory;

    protected $table = 'custom_juz';
    
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'juz_number',
        'edition'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 