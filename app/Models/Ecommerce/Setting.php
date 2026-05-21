<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'ecommerce_settings';

    protected $guarded = [];

    protected $casts = [
        // Value is stored as text; you can interpret by type in a helper later.
    ];

    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'integer' => is_null($this->value) ? null : (int) $this->value,
            'boolean' => (bool) ((int) $this->value),
            'json'    => is_null($this->value) ? null : json_decode($this->value, true),
            default   => $this->value,
        };
    }
}
