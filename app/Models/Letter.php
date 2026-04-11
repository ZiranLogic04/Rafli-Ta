<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'letter_type_id',
        'target_user_id',
        'target_name',
        'target_role',
        'target_wadir_level',
        'target_jurusan',
        'file_path',
        'status',
        'current_approver_role',
        'letter_number',
        'rejection_note',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'target_wadir_level' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(LetterType::class, 'letter_type_id')->withTrashed();
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
