<?php

namespace App\Mail;

use App\Models\Form;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Form $form,
        public array $settings,
        public array $values,
    ) {}

    public function build()
    {
        $subject = $this->settings['confirmation_subject'] ?? 'Thank you for contacting us';

        return $this
            ->subject($subject)
            ->view('emails.contact_form_confirmation')
            ->with([
                'form' => $this->form,
                'values' => $this->values,
            ]);
    }
}
