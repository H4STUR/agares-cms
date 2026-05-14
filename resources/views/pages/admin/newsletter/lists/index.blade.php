<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ __('Newsletter lists') }}</h4>
    @can('manage newsletter lists')
      <a class="btn btn-primary" href="{{ route('admin.newsletter.lists.create') }}">
        <i class="bi bi-plus-lg me-1"></i>{{ __('Add list') }}
      </a>
    @endcan
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>{{ __('Name') }}</th>
              <th>{{ __('Slug') }}</th>
              <th>{{ __('Description') }}</th>
              <th>{{ __('Subscribers') }}</th>
              <th>{{ __('Default') }}</th>
              <th class="text-end">{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($lists as $l)
              <tr>
                <td class="fw-semibold">{{ $l->name }}</td>
                <td class="text-muted font-monospace small">{{ $l->slug }}</td>
                <td class="text-muted small">{{ \Illuminate\Support\Str::limit($l->description, 80) }}</td>
                <td><span class="badge text-bg-secondary">{{ $l->subscribers_count }}</span></td>
                <td>
                  @if($l->is_default)
                    <span class="badge text-bg-success">{{ __('default') }}</span>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                </td>
                <td class="text-end">
                  @can('view newsletter lists')
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.newsletter.lists.edit', $l) }}">{{ __('Edit') }}</a>
                  @endcan
                  @can('manage newsletter lists')
                    <form class="d-inline" method="POST" action="{{ route('admin.newsletter.lists.destroy', $l) }}"
                          onsubmit="return confirm('{{ __('Delete list? Subscribers stay; only the list assignment is removed.') }}')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                    </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No lists yet.') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @if($lists->hasPages())
    <div class="mt-3">{{ $lists->links() }}</div>
  @endif
</x-app-layout>
