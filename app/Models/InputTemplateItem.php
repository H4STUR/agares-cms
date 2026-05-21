<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InputTemplateItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'input_template_id',
        'input_field_id',
        'label',
        'variable',
        'default_value',
        'description',
        'sort_order',
        'is_locked',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(InputTemplate::class, 'input_template_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(InputField::class, 'input_field_id');
    }
}
