<?php

namespace App\Models;

use App\Services\HotelConfig;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'lastname',
        'email',
        'phone',
        'state',
        'city',
        'cp',
        'hotel_code',
        'password',
    ];

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
        ];
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->rol_id === 3;
    }

    public function isDashboardAdmin(): bool
    {
        return in_array((int) $this->rol_id, [2, 3], true);
    }

    public function assignedHotelCode(): ?string
    {
        if (!$this->hotel_code) {
            return null;
        }

        try {
            return HotelConfig::normalize($this->hotel_code);
        } catch (\Throwable) {
            return null;
        }
    }

    public function canAccessHotel(?string $hotelCode): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $assignedHotelCode = $this->assignedHotelCode();

        if (!$assignedHotelCode || !$hotelCode) {
            return false;
        }

        return $assignedHotelCode === HotelConfig::normalize($hotelCode);
    }
}
