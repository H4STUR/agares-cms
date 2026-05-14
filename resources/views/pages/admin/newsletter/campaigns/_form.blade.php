@php
  $current = $campaign ?? null;
@endphp

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('Title (internal)') }}</label>
            <input class="form-control" name="title" value="{{ old('title', $current?->title) }}">
            <div class="form-text">{{ __('Internal label only — not sent.') }}</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">{{ __('Status') }}</label>
            <select name="status" class="form-select" required>
              @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $current?->status ?? 'draft') === $st)>{{ $st }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">{{ __('Subject') }} *</label>
            <input class="form-control @error('subject') is-invalid @enderror"
                   name="subject" value="{{ old('subject', $current?->subject) }}" required>
            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label d-flex justify-content-between align-items-center">
              <span>{{ __('Body') }}</span>
              @if($templates->isNotEmpty())
                <span class="small text-muted">{{ __('Pick a template below to prefill subject + body.') }}</span>
              @endif
            </label>
            @include('pages.admin.inputs.text_editor', [
              'name'  => 'body',
              'value' => old('body', $current?->body),
            ])
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header fw-semibold">{{ __('Template') }}</div>
      <div class="card-body">
        <select name="template_id" class="form-select" id="template-picker">
          <option value="">{{ __('— No template —') }}</option>
          @foreach($templates as $t)
            <option value="{{ $t->id }}"
              data-subject="{{ $t->subject }}"
              data-body="{{ e($t->body) }}"
              @selected(old('template_id', $current?->template_id ?? request()->integer('template_id')) == $t->id)>
              {{ $t->name }}
            </option>
          @endforeach
        </select>
        @if($templates->isNotEmpty())
          <button type="button" class="btn btn-sm btn-outline-primary mt-2 w-100" id="apply-template">
            {{ __('Apply selected template') }}
          </button>
          <p class="form-text mb-0">{{ __('Overwrites the current subject and body.') }}</p>
        @else
          <p class="form-text mb-0">{{ __('No active templates.') }}</p>
        @endif
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header fw-semibold">{{ __('Recipient lists') }}</div>
      <div class="card-body">
        @php
          $assignedListIds = old('lists', $current?->lists?->pluck('id')->all() ?? []);
        @endphp
        @if($lists->isEmpty())
          <p class="text-muted small mb-0">{{ __('No newsletter lists exist yet. Create one before delegating a campaign.') }}</p>
        @else
          @foreach($lists as $l)
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="list-{{ $l->id }}"
                     name="lists[]" value="{{ $l->id }}"
                     @checked(in_array($l->id, (array) $assignedListIds))>
              <label class="form-check-label small" for="list-{{ $l->id }}">
                {{ $l->name }} @if($l->is_default)<span class="badge text-bg-light border">{{ __('default') }}</span>@endif
              </label>
            </div>
          @endforeach
          <p class="form-text mb-0 mt-2">
            {{ __('Required for delegation. Only active subscribers in the selected lists are sent. Pending / unsubscribed / bounced / complained are always excluded.') }}
          </p>
        @endif
      </div>
    </div>

    <div class="card">
      <div class="card-header fw-semibold">{{ __('From / Reply-To') }}</div>
      <div class="card-body">
        <div class="mb-2">
          <label class="form-label small">{{ __('From name') }}</label>
          <input class="form-control form-control-sm" name="from_name"
                 value="{{ old('from_name', $current?->from_name) }}"
                 placeholder="{{ $defaults['from_name'] ?: __('(uses MAIL_FROM_NAME)') }}">
        </div>
        <div class="mb-2">
          <label class="form-label small">{{ __('From email') }}</label>
          <input type="email" class="form-control form-control-sm @error('from_email') is-invalid @enderror"
                 name="from_email"
                 value="{{ old('from_email', $current?->from_email) }}"
                 placeholder="{{ $defaults['from_email'] ?: __('(uses MAIL_FROM_ADDRESS)') }}">
          @error('from_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-0">
          <label class="form-label small">{{ __('Reply-To') }}</label>
          <input type="email" class="form-control form-control-sm @error('reply_to') is-invalid @enderror"
                 name="reply_to"
                 value="{{ old('reply_to', $current?->reply_to) }}"
                 placeholder="{{ $defaults['reply_to'] ?: __('(no reply-to)') }}">
          @error('reply_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const apply = document.getElementById('apply-template');
    if (!apply) return;
    apply.addEventListener('click', function () {
      const sel  = document.getElementById('template-picker');
      const opt  = sel.options[sel.selectedIndex];
      if (!opt || !opt.value) return;
      const subj = opt.getAttribute('data-subject') || '';
      const body = opt.getAttribute('data-body')    || '';

      const subjectInput = document.querySelector('input[name="subject"]');
      if (subjectInput) subjectInput.value = subj;

      // TinyMCE: set body via API; falls back to textarea if editor not ready.
      if (window.tinymce) {
        const ed = window.tinymce.get('input_body');
        if (ed) { ed.setContent(body); return; }
      }
      const ta = document.querySelector('textarea[name="body"]');
      if (ta) ta.value = body;
    });
  });
</script>
@endpush
