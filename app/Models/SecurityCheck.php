<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'check_type',
        'status',
        'breach_count',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'breach_count' => 'integer',
            'checked_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
