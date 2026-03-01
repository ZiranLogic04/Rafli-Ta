<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $fillable = [
        'user_id',
        'letter_type_id',
        'file_path',
        'status',
        'letter_number',
        'rejection_note',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(LetterType::class, 'letter_type_id')->withTrashed();
    }
}
