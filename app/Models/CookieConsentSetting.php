<?php
// app/Models/CookieConsentSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookieConsentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'enabled','block_until_choice','remember_consent',
        'title','message',
        'btn_accept_all','btn_reject_all','btn_manage','btn_save',
        'allow_essential','allow_functional','allow_analytics','allow_marketing',
        'desc_essential','desc_functional','desc_analytics','desc_marketing',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'block_until_choice' => 'boolean',
        'remember_consent' => 'boolean',
        'allow_essential' => 'boolean',
        'allow_functional' => 'boolean',
        'allow_analytics' => 'boolean',
        'allow_marketing' => 'boolean',
    ];
}
