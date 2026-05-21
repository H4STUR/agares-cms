<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormSubmitted;
use App\Mail\ContactFormConfirmation;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FormController extends Controller
{
    public function submit(Request $request, Form $form)
    {
        $form->load('fields');
        $settings = $form->settingsWithDefaults();

        // 1) Build validation rules from DB fields
        $rules = [];
        $labels = []; // for nicer error messages

        foreach ($form->fields as $field) {
            $key = $field->key;
            $labels["fields.$key"] = trim(strip_tags($field->label ?? $key)) ?: $key;

            $base = $field->required ? ['required'] : ['nullable'];

            switch ($field->type) {
                case 'email':
                    $rules["fields.$key"] = array_merge($base, ['email:rfc,dns', 'max:255']);
                    break;

                case 'tel':
                    // permissive; adjust if you want stricter
                    $rules["fields.$key"] = array_merge($base, ['string', 'max:50', 'regex:/^[+\d\s().-]+$/']);
                    break;

                case 'textarea':
                    $rules["fields.$key"] = array_merge($base, ['string', 'max:5000']);
                    break;

                case 'checkbox':
                    // required checkbox must be accepted
                    $rules["fields.$key"] = $field->required ? ['accepted'] : ['nullable', 'boolean'];
                    break;

                case 'number':
                    $rules["fields.$key"] = array_merge($base, ['numeric']);
                    break;

                case 'date':
                    $rules["fields.$key"] = array_merge($base, ['date']);
                    break;

                case 'file':
                    // If required -> must be a file.
                    // Keep max modest; tweak as you like.
                    $rules["fields.$key"] = $field->required
                        ? ['required', 'file', 'max:10240'] // 10MB
                        : ['nullable', 'file', 'max:10240'];
                    break;

                default:
                    $rules["fields.$key"] = array_merge($base, ['string', 'max:255']);
                    break;
            }
        }

        $validated = $request->validate($rules, [], $labels);

        // 2) Normalize the values so email template can show them nicely
        $values = $validated['fields'] ?? [];

        // Ensure checkboxes appear as true/false
        foreach ($form->fields as $field) {
            if ($field->type === 'checkbox') {
                $values[$field->key] = !empty($values[$field->key]);
            }
        }

        // 3) Recipients
        $recipients = $settings['mail']['recipients'] ?? [];
        $recipients = is_array($recipients) ? $recipients : [];

        // If no recipients configured, fail gracefully
        if (count($recipients) === 0) {
            return back()
                ->withErrors(['error' => 'Contact form recipients are not configured.'])
                ->withInput();
        }

        // 4) Reply-To (from a form field key, default "email")
        $replyToFieldKey = $settings['mail']['reply_to_field'] ?? 'email';
        $replyTo = isset($values[$replyToFieldKey]) ? trim((string)$values[$replyToFieldKey]) : null;
        if ($replyTo && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $replyTo = null;
        }

        // 5) Subject (supports a few simple tokens)
        $subjectTpl = (string)($settings['mail']['subject'] ?? 'New contact form message');
        $subject = $this->applySubjectTokens($subjectTpl, $values, $form);

        // 6) From (optional override; best practice is domain email)
        $fromEmail = $settings['mail']['from_email'] ?? null;
        $fromName  = $settings['mail']['from_name'] ?? null;

        // 7) Attachments (for file fields)
        $attachments = [];
        foreach ($form->fields as $field) {
            if ($field->type !== 'file') continue;

            $key = $field->key;
            $file = $request->file("fields.$key");
            if ($file && $file->isValid()) {
                $attachments[] = [
                    'path' => $file->getRealPath(),
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                ];
            }
        }

        // 8) Send mail
        try {
            $mailable = new ContactFormSubmitted(
                form: $form,
                settings: $settings,
                fields: $form->fields,
                values: $values,
                context: $request->input('_context', null),
                mailSubject: $subject,
                fileAttachments: $attachments,
            );



            // optional from override
            if ($fromEmail) {
                $mailable->from($fromEmail, $fromName ?: null);
            }

            // optional reply-to
            if ($replyTo) {
                $mailable->replyTo($replyTo);
            }

            Mail::to($recipients)->send($mailable);

            // 9) Optional: send confirmation to the sender (if email field exists/valid)
            $senderFieldKey = $settings['mail']['reply_to_field'] ?? 'email';
            $senderEmail = isset($values[$senderFieldKey]) ? trim((string)$values[$senderFieldKey]) : null;

            if ($senderEmail && filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($senderEmail)->send(
                        new ContactFormConfirmation(
                            form: $form,
                            settings: $settings,
                            values: $values
                        )
                    );
                } catch (\Throwable $e) {
                    // Don’t fail the whole submission if confirmation email fails
                    report($e);
                    Log::warning('Contact form confirmation email failed: ' . $e->getMessage(), [
                        'form_id' => $form->id,
                        'to' => $senderEmail,
                    ]);
                }
            }


            return back()->with('success', $settings['success_message'] ?? 'Thanks!');

        } catch (\Throwable $e) {
            report($e);
            $id = (string) \Illuminate\Support\Str::uuid();

            Log::error("Contact form send failed [$id]: ".$e->getMessage(), ['exception' => $e]);

            return back()
                ->withErrors(['error' => "Failed to send message. Error ID: $id"])
                ->withInput();
            }

    }

    private function applySubjectTokens(string $subject, array $values, Form $form): string
    {
        // Tokens:
        // {{form_name}}
        // {{field:key}} e.g. {{field:name}}
        $out = str_replace('{{form_name}}', (string)$form->name, $subject);

        $out = preg_replace_callback('/\{\{\s*field:([a-zA-Z0-9_\-]+)\s*\}\}/', function ($m) use ($values) {
            $k = $m[1];
            $v = $values[$k] ?? '';
            if (is_bool($v)) return $v ? 'yes' : 'no';
            if (is_array($v)) return json_encode($v);
            return Str::limit((string)$v, 60);
        }, $out);

        return trim($out) !== '' ? $out : 'New contact form message';
    }
}
