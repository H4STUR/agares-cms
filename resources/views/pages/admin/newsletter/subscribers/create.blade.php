<x-app-layout>
  <h4 class="mb-3">{{ __('Add subscriber') }}</h4>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.newsletter.subscribers.store') }}">
        @csrf

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('Email') }}</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">{{ __('Name') }}</label>
            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">{{ __('Status') }}</label>
            <select name="status" class="form-select" required>
              @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', 'active') === $st)>{{ $st }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">{{ __('Lists') }}</label>
            <select name="lists[]" class="form-select" multiple size="5">
              @foreach($lists as $l)
                <option value="{{ $l->id }}" @selected(in_array($l->id, (array) old('lists', [])))>
                  {{ $l->name }}@if($l->is_default) ({{ __('default') }})@endif
                </option>
              @endforeach
            </select>
            <div class="form-text">{{ __('Hold Ctrl/Cmd to select multiple.') }}</div>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary">{{ __('Create') }}</button>
          <a class="btn btn-outline-secondary" href="{{ route('admin.newsletter.subscribers.index') }}">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
