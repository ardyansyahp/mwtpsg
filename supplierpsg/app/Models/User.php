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
        'user_id',
        'password',
        'is_superadmin',
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
            'is_superadmin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the permissions for the user.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /**
     * Get the manpower record for the user.
     * For regular users, user_id is the same as mp_id
     */
    public function manpower()
    {
        return $this->belongsTo(\App\Models\MManpower::class, 'user_id', 'mp_id');
    }

    /**
     * Check if user is superadmin.
     */
    public function isSuperadmin(): bool
    {
        return $this->is_superadmin;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $slug): bool
    {
        // Superadmin has all permissions
        if ($this->is_superadmin) {
            return true;
        }

        return $this->permissions()->where('slug', $slug)->exists();
    }

    /**
     * Get all permission slugs for the user.
     */
    public function getPermissionSlugs(): array
    {
        if ($this->is_superadmin) {
            return ['*']; // Superadmin has all permissions
        }

        return $this->permissions()->pluck('slug')->toArray();
    }
}
