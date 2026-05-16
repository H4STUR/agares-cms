<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'username',
        'name',
        'surname',
        'email',
        'phone',
        'password',
        'role_id',
        'description',
        'avatar',
        'background_image',
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
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_email_code_sent_at' => 'datetime',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        if (is_null($this->two_factor_confirmed_at)) {
            return false;
        }
        if ($this->two_factor_method === 'email') {
            return true;
        }
        return !empty($this->two_factor_secret);
    }

    /**
     * Define a relationship to the Role model.
     */
    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    protected static function booted()
    {
        static::saved(function ($user) {
            if ($user->role_id) {
                $role = \Spatie\Permission\Models\Role::find($user->role_id);
                if ($role) {
                    $user->syncRoles($role->name);
                }
            }
        });
    }

    public function primaryRoleId(): ?int
    {
        // If you keep users.role_id:
        return $this->role_id;

        // OR if you drop users.role_id and use Spatie only:
        // return $this->roles()->value('id');
    }

    public function canOn(string $ability, \App\Models\Site $site): bool
    {
        $roleId = $this->primaryRoleId();
        if (!$roleId) return false;

        $perm = \App\Models\RoleSitePermission::where('role_id', $roleId)
            ->where('site_id', $site->id)
            ->first();

        if (!$perm) return false;

        return match ($ability) {
            'site_preview', 'view' => (bool) $perm->can_view,
            'edit'               => (bool) $perm->can_edit,
            'categories'         => (bool) $perm->can_categories,
            'articles'           => (bool) $perm->can_articles,
            default              => false,
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        $value = (string) ($this->avatar ?? '');

        if ($value === '') {
            return asset('assets/admin/images/default-avatar.png');
        }

        // External (Google/Facebook/etc.)
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        // Local stored path
        if (Storage::exists($value)) {
            return Storage::url($value);
        }

        return asset('assets/admin/images/default-avatar.png');
    }

    public function getBackgroundImageUrlAttribute(): string
    {
        $value = (string) ($this->background_image ?? '');

        if ($value === '') {
            return asset('assets/admin/images/default-background.png');
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (Storage::exists($value)) {
            return Storage::url($value);
        }

        return asset('assets/admin/images/default-background.png');
    }


}
