<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">{{ __('Edit template') }}: {{ $template->name }}</h4>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.newsletter.templates.preview', $template) }}">{{ __('Preview') }}</a>
  </div>

  <form method="POST" action="{{ route('admin.newsletter.templates.update', $template) }}">
    @csrf
    @method('PATCH')
    @include('pages.admin.newsletter.templates._form', ['template' => $template])
    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-primary">{{ __('Save') }}</button>
      <a class="btn btn-outline-secondary" href="{{ route('admin.newsletter.templates.index') }}">{{ __('Back') }}</a>
    </div>
  </form>
</x-app-layout>
