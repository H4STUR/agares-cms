<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
  use HasFactory;
  
  protected $fillable = ['name', 'type', 'settings'];

  protected $casts = [
    'settings' => 'array',
  ];

  public function fields(): HasMany
  {
    return $this->hasMany(FormField::class)->orderBy('sort_order');
  }

  public function settingsWithDefaults(): array
  {
    $s = is_array($this->settings) ? $this->settings : [];

    return array_replace_recursive([
      'mail' => [
        'recipients' => [],          // array of emails
        'from_email' => null,        // optional override
        'from_name'  => null,
        'reply_to_field' => 'email', // default: use email field
        'subject' => 'New contact form message',
      ],
      'success_message' => 'Thanks! We will contact you soon.',
    ], $s);
  }
}
