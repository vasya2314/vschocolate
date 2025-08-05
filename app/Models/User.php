<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_SUPER_ADMIN = 1;
    const ROLE_USER = 40;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
    ];

    protected $attributes = [
        'role' => self::ROLE_USER,
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    #[Scope]
    protected function findByEmail(Builder $query, string $email): void
    {
        $query->where('email', $email);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role == self::ROLE_SUPER_ADMIN;
    }
}
