<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['title','settings','created_by','updated_by'];

    protected $casts = [
        'settings' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(FaqItem::class)->orderBy('sort_order');
    }

    public function settingsWithDefaults(): array
    {
        $s = $this->settings ?? [];
        return array_replace_recursive([
            'heading' => null,
        ], $s);
    }
}
