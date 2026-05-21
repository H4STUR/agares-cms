<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
  use HasFactory;
  
  protected $fillable = [
    'form_id','key','type','label','placeholder','required','options','sort_order'
  ];

  protected $casts = [
    'required' => 'boolean',
    'options'  => 'array',
  ];

  public function form(): BelongsTo
  {
    return $this->belongsTo(Form::class);
  }
}
