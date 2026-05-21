{{-- 
Contact form input field for frontend display 
  @php $cf = $data['contact'] ?? null; @endphp

  @if($cf && $cf->field?->field_type === 'contact_form')
      @include('pages.frontend.partials.contact_form', ['instance' => $cf])
  @endif
--}}

@php
  use App\Models\Form;

  /** @var \App\Models\InputInstance $instance */
  $formId = null;

  if (is_string($instance->value) && trim($instance->value) !== '') {
    $arr = json_decode($instance->value, true);
    $formId = is_array($arr) ? ($arr['form_id'] ?? null) : null;
  }

  $form = $formId ? Form::with('fields')->find((int)$formId) : null;
  $settings = $form ? $form->settingsWithDefaults() : null;

  $action = $form ? route('forms.submit', $form->id) : '#';

  $context = [
    'owner_type'  => $instance->owner_type ?? null,
    'owner_id'    => $instance->owner_id ?? null,
    'instance_id' => $instance->id ?? null,
  ];

  $domFormId = $form ? ('ag-contact-form-' . $form->id . '-' . ($instance->id ?? 'x')) : 'ag-contact-form';
@endphp

@if(!$form)
  <div class="alert alert-warning">
    Contact form is not configured.
  </div>
@else

    {{-- Flash messages + validation errors --}}
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Please fix the errors below:</div>
        <ul class="mb-0">
        @foreach ($errors->all() as $msg)
            <li>{{ $msg }}</li>
        @endforeach
        </ul>
    </div>
    @endif


  <form id="{{ $domFormId }}"
        method="POST"
        action="{{ $action }}"
        enctype="multipart/form-data"
        class="ag-contact-form"
        novalidate>
    @csrf

    <input type="hidden" name="_context" value='@json($context)'>

    @foreach($form->fields as $field)
      @php
        $key = $field->key;
        $name = "fields[$key]";
        $id = "cf{$form->id}_{$key}";
        $required = (bool) $field->required;

        $typeMap = [
          'text'   => 'text',
          'email'  => 'email',
          'tel'    => 'tel',
          'number' => 'number',
          'date'   => 'date',
        ];
        $htmlType = $typeMap[$field->type] ?? 'text';

        $labelText = trim(strip_tags($field->label ?? $key));
        $msgRequired = $labelText ? "{$labelText} is required." : "This field is required.";

        $isLiveHelp = in_array($field->type, ['email', 'tel'], true);
        $helpId = $id . '_help';
      @endphp

      @if($field->type === 'textarea')
        <div class="mb-3">
          <label class="form-label" for="{{ $id }}">{!! safe_label($field->label) !!}</label>

          <textarea class="form-control form-input"
                    id="{{ $id }}"
                    name="{{ $name }}"
                    placeholder="{{ $field->placeholder ?? '' }}"
                    {{ $required ? 'required' : '' }}>{{ old("fields.$key") }}</textarea>

          <div class="invalid-feedback">{{ $msgRequired }}</div>
        </div>

      @elseif($field->type === 'checkbox')
        <div class="mb-3">
          <div class="form-check">
            <input class="form-check-input"
                   type="checkbox"
                   id="{{ $id }}"
                   name="{{ $name }}"
                   value="1"
                   {{ old("fields.$key") ? 'checked' : '' }}
                   {{ $required ? 'required' : '' }}>
            <label class="form-check-label" for="{{ $id }}">{!! safe_label($field->label) !!}</label>
            <div class="invalid-feedback">{{ $msgRequired }}</div>
          </div>
        </div>

      @elseif($field->type === 'file')
        <div class="mb-3">
          <label class="form-label" for="{{ $id }}">{!! safe_label($field->label) !!}</label>

          <input class="form-control form-input"
                 type="file"
                 id="{{ $id }}"
                 name="{{ $name }}"
                 {{ $required ? 'required' : '' }}>

          @if($field->placeholder)
            <div class="form-text text-muted">{{ $field->placeholder }}</div>
          @endif

          <div class="invalid-feedback">{{ $msgRequired }}</div>
        </div>

      @else
        <div class="mb-3">
          <label class="form-label" for="{{ $id }}">{!! safe_label($field->label) !!}</label>

          <input class="form-control form-input"
                 type="{{ $htmlType }}"
                 id="{{ $id }}"
                 name="{{ $name }}"
                 value="{{ old("fields.$key") }}"
                 placeholder="{{ $field->placeholder ?? '' }}"
                 data-ag-type="{{ $field->type }}"
                 {{ $required ? 'required' : '' }}>

          @if($field->type === 'email')
            <div class="invalid-feedback">
              {{ $required ? $msgRequired . ' ' : '' }}Please enter a valid email address.
            </div>
            <div id="{{ $helpId }}" class="form-text" aria-live="polite"></div>

          @elseif($field->type === 'tel')
            <div class="invalid-feedback">
              {{ $required ? $msgRequired . ' ' : '' }}Please enter a valid phone number (e.g. +48 600 700 800).
            </div>
            <div id="{{ $helpId }}" class="form-text" aria-live="polite"></div>

          @else
            <div class="invalid-feedback">{{ $msgRequired }}</div>
          @endif
        </div>
      @endif
    @endforeach

    <button type="submit" class="btn btn-primary">
      Send
    </button>
  </form>

  @once
    @push('scripts')
      <script>
        (function () {
          // allow only: + digits space ( ) . -
          const PHONE_ALLOWED_RE = /^[+\d\s().-]*$/;

          function sanitizePhone(val) {
            // Strip everything not allowed
            return String(val || '').replace(/[^\d+\s().-]/g, '');
          }

          function isValidPhone(val) {
            const s = String(val || '').trim();
            if (s === '') return true; // required handled elsewhere

            if (!PHONE_ALLOWED_RE.test(s)) return false;

            const digits = s.replace(/\D/g, '');
            if (digits.length < 7 || digits.length > 15) return false;

            // if + exists, must be first
            if (s.includes('+') && s[0] !== '+') return false;

            return true;
          }

          function setValidity(el, ok) {
            el.classList.toggle('is-valid', ok);
            el.classList.toggle('is-invalid', !ok);
          }

          function setHelp(el, msg, variant) {
            const helpId = el.id + '_help';
            const help = document.getElementById(helpId);
            if (!help) return;

            help.textContent = msg || '';

            // Reset classes
            help.classList.remove('text-muted', 'text-success', 'text-danger');
            if (!msg) return;

            if (variant === 'ok') help.classList.add('text-success');
            else if (variant === 'bad') help.classList.add('text-danger');
            else help.classList.add('text-muted');
          }

          function validateInput(el, {live=false} = {}) {
            const agType = el.dataset.agType || el.type;
            const v = (el.value || '').trim();

            // checkbox required
            if (el.type === 'checkbox') {
              const ok = !(el.required && !el.checked);
              setValidity(el, ok);
              return ok;
            }

            // file required
            if (el.type === 'file') {
              const ok = !(el.required && (!el.files || el.files.length === 0));
              if (!ok) setValidity(el, false);
              else el.classList.remove('is-invalid'); // keep neutral unless chosen
              return ok;
            }

            // required empty
            if (el.required && v === '') {
              setValidity(el, false);
              if (agType === 'email' || agType === 'tel') setHelp(el, '', '');
              return false;
            }

            // optional empty -> neutral
            if (!el.required && v === '') {
              el.classList.remove('is-valid', 'is-invalid');
              if (agType === 'email' || agType === 'tel') setHelp(el, '', '');
              return true;
            }

            // email
            if (agType === 'email' || el.type === 'email') {
              const ok = el.checkValidity();
              setValidity(el, ok);

              if (live) {
                if (ok) setHelp(el, 'Looks good ✓', 'ok');
                else setHelp(el, 'Enter a valid email (e.g. name@example.com)', 'bad');
              }
              return ok;
            }

            // phone
            if (agType === 'tel' || el.type === 'tel') {
              const ok = isValidPhone(v);
              setValidity(el, ok);

              if (live) {
                const digits = v.replace(/\D/g, '').length;
                if (ok) setHelp(el, 'Looks good ✓', 'ok');
                else if (digits > 0 && digits < 7) setHelp(el, `Too short (${digits} digits)`, 'bad');
                else setHelp(el, 'Use digits and + (spaces/dashes allowed)', 'bad');
              }
              return ok;
            }

            // other: native
            const ok = el.checkValidity();
            setValidity(el, ok);
            return ok;
          }

          function bindForm(form) {
            const inputs = Array.from(form.querySelectorAll('.form-input'));

            inputs.forEach((el) => {
              const agType = el.dataset.agType || el.type;

              // Phone: block letters in real-time (typing + paste)
              if (agType === 'tel' || el.type === 'tel') {
                el.addEventListener('beforeinput', (e) => {
                  // only for insert text
                  if (e.inputType && e.inputType.startsWith('insert') && typeof e.data === 'string') {
                    if (!PHONE_ALLOWED_RE.test(e.data)) {
                      e.preventDefault();
                    }
                  }
                });

                el.addEventListener('input', () => {
                  const sanitized = sanitizePhone(el.value);
                  if (sanitized !== el.value) {
                    const pos = el.selectionStart || el.value.length;
                    el.value = sanitized;
                    // keep caret reasonably stable
                    try { el.setSelectionRange(pos - 1, pos - 1); } catch(e) {}
                  }
                  validateInput(el, {live:true});
                });

                el.addEventListener('blur', () => validateInput(el, {live:true}));
                return;
              }

              // Email: live help on input
              if (agType === 'email' || el.type === 'email') {
                el.addEventListener('input', () => validateInput(el, {live:true}));
                el.addEventListener('blur', () => validateInput(el, {live:true}));
                return;
              }

              // Other fields: validate on blur (cleaner)
              el.addEventListener('blur', () => validateInput(el));
              el.addEventListener('change', () => validateInput(el));
            });

            form.addEventListener('submit', (e) => {
              let ok = true;
              inputs.forEach((el) => { if (!validateInput(el, {live:true})) ok = false; });

              if (!ok) {
                e.preventDefault();
                e.stopPropagation();

                const first = form.querySelector('.is-invalid');
                if (first) first.focus({ preventScroll: false });
              }
            });
          }

          document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('form.ag-contact-form').forEach(bindForm);
          });
        })();
      </script>
    @endpush
  @endonce
@endif
