<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LetterType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'template_path', 'original_filename', 'parent_id'];

    public function letters()
    {
        return $this->hasMany(Letter::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'letter_type_user');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function rolePermissions()
    {
        return $this->hasMany(RoleLetterTypePermission::class);
    }
}
