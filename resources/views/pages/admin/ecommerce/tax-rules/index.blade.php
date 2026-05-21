<x-app-layout>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Tax rules</h4>
    <a class="btn btn-primary" href="{{ route('admin.ecommerce.tax-rules.create') }}">Add tax rule</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Country</th>
              <th>Region</th>
              <th>Rate</th>
              <th>Prices incl. tax</th>
              <th>Priority</th>
              <th>Enabled</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rules as $r)
              <tr>
                <td>{{ $r->country ?? '-' }}</td>
                <td>{{ $r->region ?? '-' }}</td>
                <td>{{ $r->rate }}%</td>
                <td>{!! $r->prices_include_tax ? '<span class="badge text-bg-success">Yes</span>' : '<span class="badge text-bg-secondary">No</span>' !!}</td>
                <td>{{ $r->priority }}</td>
                <td>{!! $r->enabled ? '<span class="badge text-bg-success">Yes</span>' : '<span class="badge text-bg-secondary">No</span>' !!}</td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.ecommerce.tax-rules.edit', $r) }}">Edit</a>
                  <form class="d-inline" method="POST" action="{{ route('admin.ecommerce.tax-rules.destroy', $r) }}"
                        onsubmit="return confirm('Delete tax rule?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center text-muted py-4">No tax rules yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $rules->links() }}
    </div>
  </div>
</x-app-layout>
