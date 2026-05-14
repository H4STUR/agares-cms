<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">{{ __('Preview') }}: {{ $template->name }}</h4>
    <div class="d-flex gap-2">
      @can('view newsletter templates')
        <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.newsletter.templates.edit', $template) }}">{{ __('Edit') }}</a>
      @endcan
      <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.newsletter.templates.index') }}">{{ __('Back') }}</a>
    </div>
  </div>

  <div class="alert alert-warning small">
    <i class="bi bi-info-circle me-1"></i>
    {{ __('This is a local preview only — nothing is sent.') }}
  </div>

  <div class="card mb-3">
    <div class="card-header">
      <strong>{{ __('Subject') }}:</strong> {{ $template->subject ?: __('(no subject)') }}
    </div>
    <div class="card-body" style="background:#fff;color:#212529;">
      {!! safe_html((string) $template->body) !!}
    </div>
  </div>
</x-app-layout>
