<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKeyCreate extends Command
{
    protected $signature = 'api-key:create
        {name : Friendly name for the key}
        {--site_id= : Optional site/tenant lock}
        {--abilities=* : Ability/scope (repeatable). Use "*" for full access}
        {--expires= : Expiration date (YYYY-MM-DD), optional}';

    protected $description = 'Create an API key (plaintext shown once)';

    public function handle(): int
    {
        // Plaintext key (show once)
        $plain = 'ak_' . Str::random(48);

        $abilities = $this->option('abilities');
        if (!is_array($abilities)) {
            $abilities = [];
        }

        $expiresAt = $this->option('expires')
            ? now()->parse($this->option('expires'))
            : null;

        $key = ApiKey::create([
            'name' => $this->argument('name'),
            'key_hash' => Hash::make($plain),
            'site_id' => $this->option('site_id'),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
            'created_by' => null,
        ]);

        $this->info('API key created');
        $this->line('ID: ' . $key->id);
        $this->warn('Plaintext key (copy now, shown once):');
        $this->line($plain);

        return self::SUCCESS;
    }
}
