<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqItem extends Model
{
    protected $fillable = [
        'input_instance_id', 'question', 'answer', 'sort_order', 'is_active'
    ];

    public function inputInstance()
    {
        return $this->belongsTo(InputInstance::class);
    }
}
