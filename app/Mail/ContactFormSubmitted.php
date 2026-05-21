<?php

namespace App\Mail;

use App\Models\Form;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Form $form,
        public array $settings,
        public $fields,
        public array $values,
        public $context,
        public string $mailSubject,
        public array $fileAttachments = [],
    ) {}

    public function build()
    {
        $mail = $this
            ->subject($this->mailSubject)
            ->view('emails.contact_form_submitted')
            ->with([
                'form'     => $this->form,
                'settings' => $this->settings,
                'fields'   => $this->fields,
                'values'   => $this->values,
                'context'  => $this->context,
            ]);

        foreach ($this->fileAttachments as $a) {
            $mail->attach($a['path'], [
                'as'   => $a['name'],
                'mime' => $a['mime'],
            ]);
        }

        return $mail;
    }
}
