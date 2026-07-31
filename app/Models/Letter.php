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
        'signatory_name',
        'notes',
        'file_path',
        'status',
        'current_approver_role',
        'letter_number',
        'rejection_note',
        'approved_at',
        'approved_by',
    ];

    protected $appends = ['target_info'];

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

    public function getTargetInfoAttribute()
    {
        return $this->target_name ?: "-";
    }

    public static function makeUniqueLetterNumber(string $letterNumber, ?int $ignoreId = null): string
    {
        $query = static::where('letter_number', $letterNumber);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if (! $query->exists()) {
            return $letterNumber;
        }

        if (preg_match('/^(\d+)([A-Z]*)(.*)/', $letterNumber, $matches)) {
            $digits = $matches[1];
            $rest = $matches[3];

            $index = 0;
            while (true) {
                $suffix = static::getLetterSuffix($index);
                $candidate = $digits . $suffix . $rest;

                $candidateQuery = static::where('letter_number', $candidate);
                if ($ignoreId) {
                    $candidateQuery->where('id', '!=', $ignoreId);
                }

                if (! $candidateQuery->exists()) {
                    return $candidate;
                }
                $index++;
            }
        }

        $index = 0;
        while (true) {
            $suffix = static::getLetterSuffix($index);
            $candidate = $letterNumber . '-' . $suffix;

            $candidateQuery = static::where('letter_number', $candidate);
            if ($ignoreId) {
                $candidateQuery->where('id', '!=', $ignoreId);
            }

            if (! $candidateQuery->exists()) {
                return $candidate;
            }
            $index++;
        }
    }

    private static function getLetterSuffix(int $index): string
    {
        $suffix = '';
        while ($index >= 0) {
            $suffix = chr(65 + ($index % 26)) . $suffix;
            $index = intdiv($index, 26) - 1;
        }
        return $suffix;
    }
}
