<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Foundation\Auth\User as Auththenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Guest extends Auththenticatable
{
    protected $connection = 'tenant';

    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'avatar',
        'phone',
        'email',
        'address',
        'identification_number',
        'nation_id',
        'email_verified_at',
        'remember_token',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
        'password',
        'email_verified_at',
    ];

    public function nation()
    {
        return $this->belongsTo(Nation::class, 'nation_id', 'id');
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            if (str_starts_with($this->avatar, '/') || str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }
            return asset('storage/' . $this->avatar);
        }
        return null;
    }

    public function interests()
    {
        return $this->hasMany(Interest::class, 'guest_id', 'id');
    }
}
