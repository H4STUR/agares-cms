<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class RoleSitePermission extends Model
{
    use HasFactory;
    protected $table = 'role_site_permissions';

    protected $fillable = [
        'role_id',
        'site_id',
        'can_view',
        'can_edit',
        'can_categories',
        'can_articles',
    ];

    protected $casts = [
        'can_view'       => 'bool',
        'can_edit'       => 'bool',
        'can_categories' => 'bool',
        'can_articles'   => 'bool',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Site::class, 'site_id');
    }
}
