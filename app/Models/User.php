<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'code',
        'wadir_level',
        'jurusan',
    ];

    public function letters()
    {
        return $this->hasMany(Letter::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wadir_level' => 'integer',
        ];
    }

    public function letterTypes()
    {
        return $this->belongsToMany(LetterType::class, 'letter_type_user');
    }

    public function rolePermissions()
    {
        return $this->hasMany(RoleLetterTypePermission::class, 'role', 'role');
    }

    public function allowedLetterTypes()
    {
        if ($this->role === 'admin') {
            return LetterType::query()->with('parent')->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')->orderBy('name');
        }

        return LetterType::whereHas('rolePermissions', function ($q) {
            $q->where('role', $this->role);
        })->with('parent')->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')->orderBy('name');
    }
}
