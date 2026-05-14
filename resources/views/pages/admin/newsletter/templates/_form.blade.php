<div class="card">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">{{ __('Name') }}</label>
        <input class="form-control @error('name') is-invalid @enderror"
               name="name"
               value="{{ old('name', $template?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-md-6">
        <label class="form-label">{{ __('Subject') }}</label>
        <input class="form-control @error('subject') is-invalid @enderror"
               name="subject"
               value="{{ old('subject', $template?->subject) }}">
        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-12">
        <label class="form-label">{{ __('Description') }}</label>
        <textarea class="form-control" name="description" rows="2">{{ old('description', $template?->description) }}</textarea>
      </div>

      <div class="col-12">
        <label class="form-label">{{ __('Body') }}</label>
        @include('pages.admin.inputs.text_editor', [
          'name'  => 'body',
          'value' => old('body', $template?->body),
        ])
        <div class="form-text">{{ __('Allowed HTML is sanitized when previewed and when sent.') }}</div>
      </div>

      <div class="col-12">
        <div class="form-check">
          <input type="hidden" name="is_active" value="0">
          <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                 @checked(old('is_active', $template?->is_active ?? true))>
          <label class="form-check-label" for="is_active">{{ __('Template is active (selectable when creating campaigns)') }}</label>
        </div>
      </div>
    </div>
  </div>
</div>
