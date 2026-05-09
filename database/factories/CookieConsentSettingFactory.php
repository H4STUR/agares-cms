<?php

namespace Database\Factories;

use App\Models\CookieConsentSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class CookieConsentSettingFactory extends Factory
{
    protected $model = CookieConsentSetting::class;

    public function definition(): array
    {
        return [
            'domain'            => 'demo.agares.co.uk',
            'enabled'           => true,
            'block_until_choice' => true,
            'remember_consent'  => true,

            'title'   => 'We use cookies',
            'message' => 'This site uses cookies to improve your experience.',

            'btn_accept_all' => 'Accept all',
            'btn_reject_all' => 'Reject',
            'btn_manage'     => 'Manage',
            'btn_save'       => 'Save',

            'allow_essential'  => true,
            'allow_functional' => true,
            'allow_analytics'  => false,
            'allow_marketing'  => false,

            'desc_essential'  => 'Required for the site to function.',
            'desc_functional' => 'Enhance your experience.',
            'desc_analytics'  => 'Help us understand usage.',
            'desc_marketing'  => 'Used for personalised ads.',
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn () => ['enabled' => false]);
    }

    public function forDomain(string $domain): static
    {
        return $this->state(fn () => ['domain' => $domain]);
    }
}
