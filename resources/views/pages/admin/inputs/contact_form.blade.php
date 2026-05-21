{{-- resources/views/pages/admin/inputs/contact_form.blade.php --}}

@php
    use App\Models\Form;

    /**
     * Expected variables:
     * - $value (string|null)       -> InputInstance value, JSON like {"form_id": 123}
     * - $instanceId (int|null)     -> InputInstance id (for unique DOM ids)
     */

    $formId = null;

    if (is_string($value) && $value !== '') {
        $arr = json_decode($value, true);
        $formId = is_array($arr) ? ($arr['form_id'] ?? null) : null;
    } elseif (is_array($value)) {
        $formId = $value['form_id'] ?? null;
    }

    /** @var \App\Models\Form|null $form */
    $form = $formId ? Form::with('fields')->find($formId) : null;

    // settings + defaults
    $settings = $form ? $form->settingsWithDefaults() : [
        'mail' => [
            'recipients' => [],
            'from_email' => null,
            'from_name'  => null,
            'reply_to_field' => 'email',
            'subject' => 'New contact form message',
        ],
        'success_message' => 'Thanks! We will contact you soon.',
    ];

    $recipientsStr = implode('; ', $settings['mail']['recipients'] ?? []);

    $accordionId = 'cf-settings-' . ($instanceId ?? ($form?->id ?? uniqid()));
@endphp

@if(!$form)
    <div class="alert alert-warning mb-0">
        <div class="fw-semibold">{{ __('Contact form is missing') }}</div>
        <div class="text-muted small">
            {{ __('This input does not have a valid form_id. Delete and add the contact form again.') }}
        </div>
    </div>
@else
    <div class="bg-body-tertiary border rounded-4 p-3">

        {{-- SETTINGS (collapsed accordion) --}}
        <div class="accordion mb-3" id="{{ $accordionId }}">
            <div class="accordion-item">
                <h2 class="accordion-header" id="{{ $accordionId }}-heading">
                    <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $accordionId }}-collapse"
                            aria-expanded="false"
                            aria-controls="{{ $accordionId }}-collapse">
                        <i class="bi bi-gear me-2"></i> {{ __('Email settings') }}
                    </button>
                </h2>

                <div id="{{ $accordionId }}-collapse"
                     class="accordion-collapse collapse"
                     aria-labelledby="{{ $accordionId }}-heading"
                     data-bs-parent="#{{ $accordionId }}">
                    <div class="accordion-body">

                        <form action="{{ route('admin.forms.settings', $form->id) }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Recipients (separate with ;)') }}</label>
                                    <input class="form-control"
                                           name="recipients"
                                           value="{{ old('recipients', $recipientsStr) }}"
                                           placeholder="a@x.com; b@y.com">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Subject') }}</label>
                                    <input class="form-control"
                                           name="subject"
                                           value="{{ old('subject', $settings['mail']['subject'] ?? '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('From email (optional)') }}</label>
                                    <input class="form-control"
                                           name="from_email"
                                           value="{{ old('from_email', $settings['mail']['from_email'] ?? '') }}">
                                    <div class="form-text text-muted">
                                        {{ __('Prefer a domain email for deliverability.') }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('From name (optional)') }}</label>
                                    <input class="form-control"
                                           name="from_name"
                                           value="{{ old('from_name', $settings['mail']['from_name'] ?? '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Reply-To field key') }}</label>
                                    <input class="form-control"
                                           name="reply_to_field"
                                           value="{{ old('reply_to_field', $settings['mail']['reply_to_field'] ?? 'email') }}">
                                    <div class="form-text text-muted">{{ __('Usually: email') }}</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">{{ __('Success message') }}</label>
                                    <input class="form-control"
                                           name="success_message"
                                           value="{{ old('success_message', $settings['success_message'] ?? '') }}">
                                </div>
                            </div>

                            <div class="mt-3 d-flex justify-content-end">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="bi bi-save me-1"></i> {{ __('Save settings') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        {{-- FIELDS --}}
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-semibold">
                <i class="bi bi-ui-checks-grid me-1"></i> {{ __('Form fields') }}
            </div>
            <span class="text-muted small">{{ __('Form ID') }}: #{{ $form->id }}</span>
        </div>

        {{-- Hidden forms for move/delete actions (OUTSIDE the bulk update form to avoid nesting) --}}
        @foreach($form->fields as $f)
            @php
                $upFormId   = "moveUpField{$f->id}";
                $downFormId = "moveDownField{$f->id}";
                $delFormId  = "delField{$f->id}";
            @endphp

            <form id="{{ $upFormId }}" action="{{ route('admin.forms.fields.move', $f->id) }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="dir" value="up">
            </form>

            <form id="{{ $downFormId }}" action="{{ route('admin.forms.fields.move', $f->id) }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="dir" value="down">
            </form>

            <form id="{{ $delFormId }}" action="{{ route('admin.forms.fields.destroy', $f->id) }}" method="POST" class="d-none"
                  onsubmit="return confirm('Delete this field?');">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        {{-- Bulk update fields form (NO autosave) --}}
        <form action="{{ route('admin.forms.fields.bulkUpdate', $form->id) }}" method="POST" class="m-0">
            @csrf
            @method('PATCH')

            <div class="list-group mb-3">
                @forelse($form->fields as $i => $f)
                    @php
                        $isCheckbox = $f->type === 'checkbox';
                        $upFormId   = "moveUpField{$f->id}";
                        $downFormId = "moveDownField{$f->id}";
                        $delFormId  = "delField{$f->id}";
                    @endphp

                    <div class="list-group-item">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">

                            <div class="flex-grow-1">
                                <div class="fw-semibold mb-2">
                                    <span class="text-muted">Key: {{ $f->key }}</span>
                                    <span class="text-muted small ms-2">• Type: {{ $f->type }}</span>
                                </div>

                                <div class="row g-2">
                                    <div class="{{ $isCheckbox? 'col-md-12' : 'col-md-6' }}">
                                        <label class="form-label small mb-1">{{ __('Label') }}</label>
                                        <input class="form-control form-control-sm"
                                               name="fields[{{ $f->id }}][label]"
                                               value="{{ old("fields.$f->id.label", $f->label) }}"
                                               placeholder="{{ __('Label shown to user') }}">
                                    </div>

                                    @if(!$isCheckbox)
                                        <div class="col-md-6">
                                            <label class="form-label small mb-1">{{ __('Placeholder') }}</label>
                                            <input class="form-control form-control-sm"
                                                   name="fields[{{ $f->id }}][placeholder]"
                                                   value="{{ old("fields.$f->id.placeholder", $f->placeholder) }}"
                                                   placeholder="{{ __('Optional placeholder') }}">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                {{-- Required toggle (saved with Save fields) --}}
                                <input type="hidden" name="fields[{{ $f->id }}][required]" value="0">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="requiredSwitch{{ $f->id }}"
                                           name="fields[{{ $f->id }}][required]"
                                           value="1"
                                           {{ $f->required ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="requiredSwitch{{ $f->id }}">
                                        {{ __('Required') }}
                                    </label>
                                </div>

                                {{-- Move / Delete --}}
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-secondary"
                                            type="submit"
                                            form="{{ $upFormId }}"
                                            {{ $i === 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>

                                    <button class="btn btn-outline-secondary"
                                            type="submit"
                                            form="{{ $downFormId }}"
                                            {{ $i === (count($form->fields) - 1) ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>

                                    <button class="btn btn-outline-danger"
                                            type="submit"
                                            form="{{ $delFormId }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                @empty
                    <div class="list-group-item text-muted">
                        {{ __('No fields yet.') }}
                    </div>
                @endforelse
            </div>

            {{-- SAVE FIELDS (under loop) --}}
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-save me-1"></i> {{ __('Save fields') }}
                </button>
            </div>
        </form>

        {{-- ADD FIELD --}}
        <div class="card border-0 shadow-sm rounded-4 mt-3">
            <div class="card-body">
                <div class="fw-semibold mb-2">{{ __('Add field') }}</div>

                <form action="{{ route('admin.forms.fields.store', $form->id) }}" method="POST" class="row g-2 align-items-end">
                    @csrf

                    <div class="col-md-2">
                        <label class="form-label">{{ __('Key') }}</label>
                        <input class="form-control" name="key" placeholder="e.g. company" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ __('Type') }}</label>
                        <select class="form-select" name="type" required>
                            @foreach(['text','email','tel','textarea','checkbox','number','date','file'] as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ __('Label') }}</label>
                        <input class="form-control" name="label" placeholder="Label shown to user">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ __('Placeholder') }}</label>
                        <input class="form-control" name="placeholder" placeholder="Optional placeholder">
                    </div>

                    {{-- <div class="col-md-1">
                        <label class="form-label">{{ __('Req') }}</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="required" value="1" id="newReq{{ $form->id }}">
                            <label class="form-check-label small" for="newReq{{ $form->id }}">{{ __('Yes') }}</label>
                        </div>
                    </div> --}}

                    <div class="col-md-2">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-plus-lg me-1"></i> {{ __('Add field') }}
                        </button>
                    </div>
                </form>

                <div class="form-text text-muted mt-2">
                    {{ __('Supported types: text, email, tel, textarea, checkbox, number, date, file.') }}
                </div>
            </div>
        </div>

    </div>
@endif
