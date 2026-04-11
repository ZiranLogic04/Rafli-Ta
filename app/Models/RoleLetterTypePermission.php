<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleLetterTypePermission extends Model
{
    protected $table = 'role_letter_type_permissions';

    protected $fillable = [
        'role',
        'letter_type_id',
    ];

    public function letterType()
    {
        return $this->belongsTo(LetterType::class);
    }
}
