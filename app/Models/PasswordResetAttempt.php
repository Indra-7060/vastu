<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function countForEmailInLastDay(string $email): int
    {
        return static::query()
            ->where('email', strtolower(trim($email)))
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    public static function record(string $email, ?string $ip = null): void
    {
        static::query()->create([
            'email' => strtolower(trim($email)),
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }
}
