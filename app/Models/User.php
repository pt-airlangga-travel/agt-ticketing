<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
        'active_role_id',
    ];

    public function getRouteKeyName()
    {
        return 'username';
    }

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
            'is_active' => 'boolean',
        ];
    }

    public function setUsernameAttribute($value): void
    {
        $this->attributes['username'] = Str::of($value)
            ->lower()
            ->trim()
            ->replaceMatches('/\s+/', '_')
            ->replaceMatches('/[^a-z0-9_.]/', '')
            ->toString();
    }

    // start realtion
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function activeRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'active_role_id');
    }

    public function getActiveRoleLabel(): string
    {
        return $this->activeRole?->display_name
            ?? $this->activeRole?->name
            ?? 'There is no active role';
    }

    public function canAccess(string $permissionName): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $cacheKey = "user:{$this->id}:permissions";

        $permissions = Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return $this->roles()
                ->where('roles.is_active', true)
                ->with('permissions')
                ->get()
                ->flatMap(fn (Role $role) => $role->permissions->pluck('name'))
                ->unique()
                ->toArray();
        });

        return in_array($permissionName, $permissions, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles()
            ->where('roles.name', 'super-admin')
            ->exists();
    }

    public function forgetAccessCache(): void
    {
        Cache::forget("user:{$this->id}:permissions");
    }

    public function setActiveRole(int $roleId): void
    {
        if (! $this->roles()->whereKey($roleId)->exists()) {
            abort(403, 'Role tidak dimiliki user.');
        }

        $this->update([
            'active_role_id' => $roleId,
        ]);
    }
}
