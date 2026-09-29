<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'birth_date',
        'marketing_opt_in',
        'avatar',
        'platform',
        'login_provider',
        'google_id',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'marketing_opt_in' => 'boolean',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(UserBankAccount::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class)->latest();
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class)->latest();
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class)->latest();
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->latest('ordered_at');
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=e7ece9&color=0b251f&bold=true';
    }

    public function getIsGoogleLoginAttribute(): bool
    {
        return $this->login_provider === 'google';
    }
}
