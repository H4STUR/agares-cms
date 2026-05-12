<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FormController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage sites', only: ['updateSettings']),
        ];
    }


  public function updateSettings(Request $request, Form $form)
  {
    $data = $request->validate([
      'recipients' => 'nullable|string', // "a@x.com; b@y.com"
      'from_email' => 'nullable|email',
      'from_name'  => 'nullable|string|max:255',
      'reply_to_field' => 'nullable|string|max:100',
      'subject' => 'nullable|string|max:255',
      'success_message' => 'nullable|string|max:1000',
    ]);

    $settings = $form->settingsWithDefaults();

    // normalize recipients to array
    $recipientsRaw = $data['recipients'] ?? '';
    $emails = array_values(array_filter(array_map('trim', preg_split('/[;,]+/', $recipientsRaw))));
    $settings['mail']['recipients'] = $emails;

    $settings['mail']['from_email'] = $data['from_email'] ?? null;
    $settings['mail']['from_name']  = $data['from_name'] ?? null;
    $settings['mail']['reply_to_field'] = $data['reply_to_field'] ?? 'email';
    $settings['mail']['subject'] = $data['subject'] ?? $settings['mail']['subject'];

    $settings['success_message'] = $data['success_message'] ?? $settings['success_message'];

    $form->update(['settings' => $settings]);

    return back()->with('success', 'Form settings saved.');
  }
}
