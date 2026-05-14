<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">{{ __('Newsletter templates') }}</h4>
    @can('manage newsletter templates')
      <a class="btn btn-primary" href="{{ route('admin.newsletter.templates.create') }}">
        <i class="bi bi-plus-lg me-1"></i>{{ __('Add template') }}
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
              <th>{{ __('Subject') }}</th>
              <th>{{ __('Active') }}</th>
              <th>{{ __('Created by') }}</th>
              <th>{{ __('Updated') }}</th>
              <th class="text-end">{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($templates as $t)
              <tr>
                <td class="fw-semibold">{{ $t->name }}</td>
                <td class="text-muted small">{{ \Illuminate\Support\Str::limit($t->subject, 70) ?: '—' }}</td>
                <td>
                  @if($t->is_active)
                    <span class="badge text-bg-success">{{ __('active') }}</span>
                  @else
                    <span class="badge text-bg-secondary">{{ __('inactive') }}</span>
                  @endif
                </td>
                <td class="text-muted small">{{ optional($t->creator)->name ?: '—' }}</td>
                <td class="text-muted small">{{ optional($t->updated_at)->format('d M Y H:i') }}</td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.newsletter.templates.preview', $t) }}">{{ __('Preview') }}</a>
                  @can('view newsletter templates')
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.newsletter.templates.edit', $t) }}">{{ __('Edit') }}</a>
                  @endcan
                  @can('manage newsletter templates')
                    <form class="d-inline" method="POST" action="{{ route('admin.newsletter.templates.destroy', $t) }}"
                          onsubmit="return confirm('{{ __('Delete template?') }}')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                    </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No templates yet.') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @if($templates->hasPages())
    <div class="mt-3">{{ $templates->links() }}</div>
  @endif
</x-app-layout>
