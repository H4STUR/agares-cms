<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ __('Ecommerce settings') }}</h4>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.ecommerce.settings.store') }}">
        @csrf
        <div class="row g-2">
          <div class="col-md-3"><input class="form-control" name="key" placeholder="key" required></div>
          <div class="col-md-3"><input class="form-control" name="value" placeholder="value"></div>
          <div class="col-md-2"><input class="form-control" name="category" placeholder="category" value="general" required></div>
          <div class="col-md-2">
            <select class="form-select" name="type" required>
              @foreach(['string','integer','boolean','json'] as $t)
                <option value="{{ $t }}">{{ $t }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('Add/Save') }}</button></div>
          <div class="col-12"><input class="form-control" name="description" placeholder="description"></div>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>{{ __('Key') }}</th>
              <th>{{ __('Value') }}</th>
              <th>{{ __('Category') }}</th>
              <th>{{ __('Type') }}</th>
              <th>{{ __('Description') }}</th>
              <th class="text-end">{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($ecommerceSettings as $s)
              <tr>
                <td class="fw-semibold">{{ $s->key }}</td>
                <td style="max-width: 260px;">
                  <form method="POST" action="{{ route('admin.ecommerce.settings.update', $s) }}" class="d-flex gap-2">
                    @csrf
                    @method('PATCH')
                    <input class="form-control form-control-sm" name="value" value="{{ $s->value }}">
                </td>
                <td><input class="form-control form-control-sm" name="category" value="{{ $s->category }}"></td>
                <td>
                  <select class="form-select form-select-sm" name="type">
                    @foreach(['string','integer','boolean','json'] as $t)
                      <option value="{{ $t }}" @selected($s->type === $t)>{{ $t }}</option>
                    @endforeach
                  </select>
                </td>
                <td><input class="form-control form-control-sm" name="description" value="{{ $s->description }}"></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary">{{ __('Save') }}</button>
                  </form>

                  <form method="POST" action="{{ route('admin.ecommerce.settings.destroy', $s) }}" class="d-inline"
                        onsubmit="return confirm('{{ __('Delete setting?') }}')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</x-app-layout>
