<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'employee_code', 'name', 'mobile', 'email', 'password', 'mpin',
        'role_id', 'branch_id', 'is_active',
    ];

    protected $hidden = ['password', 'mpin', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Role-check helper used across policies/middleware.
     */
    public function hasRole(string $roleCode): bool
    {
        return $this->role ? $this->role->code === $roleCode : false;
    }

    public function hasAnyRole(array $roleCodes): bool
    {
        return in_array($this->role ? $this->role->code : null, $roleCodes, true);
    }
}
