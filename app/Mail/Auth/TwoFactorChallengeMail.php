<?php

namespace App\Mail\Auth;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorChallengeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $code,
        public readonly int $ttlMinutes,
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('Your verification code: :code', ['code' => $this->code]))
            ->view('emails.auth.two-factor-challenge', [
                'user'       => $this->user,
                'code'       => $this->code,
                'ttlMinutes' => $this->ttlMinutes,
            ]);
    }
}
