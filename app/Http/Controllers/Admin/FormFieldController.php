<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FormFieldController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage sites', only: ['store', 'destroy', 'move', 'update', 'bulkUpdate']),
        ];
    }


  public function store(Request $request, Form $form)
  {
    $data = $request->validate([
      'key' => 'required|string|max:100',
      'type' => 'required|string|max:50',
      'label' => 'nullable|string|max:255',
      'placeholder' => 'nullable|string|max:255',
      'required' => 'nullable|boolean',
    ]);

    // basic type allowlist
    $allowed = ['text','email','tel','textarea','checkbox','number','date','file'];
    if (!in_array($data['type'], $allowed, true)) {
      return back()->with('error', 'Invalid field type.');
    }

    $maxOrder = (int) $form->fields()->max('sort_order');
    $nextOrder = $maxOrder ? $maxOrder + 10 : 10;

    FormField::create([
      'form_id' => $form->id,
      'key' => $data['key'],
      'type' => $data['type'],
      'label' => $data['label'] ?: ucfirst($data['key']),
      'placeholder' => $data['placeholder'] ?? null,
      'required' => (bool)($data['required'] ?? false),
      'sort_order' => $nextOrder,
    ]);

    return back()->with('success', 'Field added.');
  }

    public function update(Request $request, FormField $field)
    {
        $data = $request->validate([
            'required'    => 'nullable|boolean',
            'label'       => 'nullable|string|max:255',
            'placeholder' => 'nullable|string|max:255',
        ]);

        // checkbox fields usually don't need placeholder, but allow it anyway (it will just be unused in frontend)
        $field->required = (bool)($data['required'] ?? false);

        if (array_key_exists('label', $data)) {
            $field->label = $data['label'] !== null ? trim($data['label']) : null;
        }

        if (array_key_exists('placeholder', $data)) {
            $field->placeholder = $data['placeholder'] !== null ? trim($data['placeholder']) : null;
        }

        $field->save();

        return back()->with('success', 'Field updated.');
    }

    public function bulkUpdate(Request $request, \App\Models\Form $form)
    {
        $data = $request->validate([
            'fields' => 'required|array',
            'fields.*.label' => 'nullable|string|max:255',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.required' => 'nullable|boolean',
        ]);

        foreach ($data['fields'] as $id => $payload) {
            $field = $form->fields()->where('id', (int)$id)->first();
            if (!$field) continue;

            $field->label = $payload['label'] ?? null;
            $field->placeholder = $payload['placeholder'] ?? null;
            $field->required = (bool)($payload['required'] ?? false);
            $field->save();
        }

        return back()->with('success', 'Fields saved.');
    }



  public function destroy(FormField $field)
  {
    $field->delete();
    return back()->with('success', 'Field deleted.');
  }

  public function move(Request $request, FormField $field)
  {
    $dir = $request->input('dir'); // up|down
    $form = $field->form;

    $fields = $form->fields()->get()->values();
    $idx = $fields->search(fn($f) => $f->id === $field->id);
    if ($idx === false) return back();

    $swapIdx = $dir === 'up' ? $idx - 1 : $idx + 1;
    if ($swapIdx < 0 || $swapIdx >= $fields->count()) return back();

    $a = $fields[$idx];
    $b = $fields[$swapIdx];

    // swap sort_order
    $tmp = $a->sort_order;
    $a->sort_order = $b->sort_order;
    $b->sort_order = $tmp;
    $a->save();
    $b->save();

    return back()->with('success', 'Field reordered.');
  }
}
