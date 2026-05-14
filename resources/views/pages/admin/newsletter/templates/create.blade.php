<x-app-layout>
  <h4 class="mb-3">{{ __('Create template') }}</h4>

  <form method="POST" action="{{ route('admin.newsletter.templates.store') }}">
    @csrf
    @include('pages.admin.newsletter.templates._form', ['template' => null])
    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-primary">{{ __('Create') }}</button>
      <a class="btn btn-outline-secondary" href="{{ route('admin.newsletter.templates.index') }}">{{ __('Cancel') }}</a>
    </div>
  </form>
</x-app-layout>
