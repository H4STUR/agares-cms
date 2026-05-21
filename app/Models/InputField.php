<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InputField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'field_type',
        'created_by',
        'updated_by',
    ];

    public function instances(): HasMany
    {
        return $this->hasMany(InputInstance::class, 'input_field_id');
    }

    public function templateItems(): HasMany
    {
        return $this->hasMany(InputTemplateItem::class, 'input_field_id');
    }
}
