<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consultation extends Model
{
    /** status => [label, admin badge style] */
    public const STATUSES = [
        'new' => ['New', 'info'],
        'contacted' => ['Contacted', 'warning'],
        'scheduled' => ['Scheduled', 'success'],
        'closed' => ['Closed', 'neutral'],
    ];

    protected $fillable = ['user_id', 'name', 'email', 'phone', 'interest', 'message', 'status', 'admin_note'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return 'dash-badge--'.(self::STATUSES[$this->status][1] ?? 'neutral');
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);
        if ($digits === '') {
            return null;
        }
        if (strlen($digits) === 10) {
            $digits = '91'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }
}
