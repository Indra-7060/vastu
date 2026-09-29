<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    public const TYPES = ['Flagship Store', 'Experience Centre', 'Consultation Centre', 'Partner Store'];

    protected $fillable = [
        'name', 'slug', 'type', 'address', 'city', 'state', 'pincode', 'phone', 'email',
        'opening_hours', 'services', 'map_url', 'image', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function getFullAddressAttribute(): string
    {
        return collect([$this->address, $this->city, trim($this->state.' '.$this->pincode)])->filter()->implode(', ');
    }

    /** Admin-entered map link, or a Google Maps search for the address. */
    public function getDirectionsUrlAttribute(): string
    {
        return $this->map_url ?: 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->name.', '.$this->full_address);
    }

    /** @return array<int, string> */
    public function getServiceListAttribute(): array
    {
        return collect(explode(',', (string) $this->services))->map(fn ($s) => trim($s))->filter()->values()->all();
    }

    public function getTelAttribute(): ?string
    {
        return $this->phone ? preg_replace('/[^0-9+]/', '', $this->phone) : null;
    }
}
