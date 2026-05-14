<x-app-layout>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h4 class="mb-0">{{ __('Newsletter') }}</h4>
      <p class="text-muted mb-0 small">{{ __('Manage your subscribers and lists.') }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      @can('manage newsletter subscribers')
        <a class="btn btn-sm btn-primary" href="{{ route('admin.newsletter.subscribers.create') }}">
          <i class="bi bi-plus-lg me-1"></i>{{ __('Add Subscriber') }}
        </a>
      @endcan
      @can('view newsletter subscribers')
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.newsletter.subscribers.index') }}">{{ __('Subscribers') }}</a>
      @endcan
      @can('view newsletter lists')
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.newsletter.lists.index') }}">{{ __('Lists') }}</a>
      @endcan
      @can('view newsletter templates')
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.newsletter.templates.index') }}">{{ __('Templates') }}</a>
      @endcan
      @can('view newsletter campaigns')
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.newsletter.campaigns.index') }}">{{ __('Campaigns') }}</a>
      @endcan
      @can('view newsletter settings')
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.newsletter.settings.index') }}">{{ __('Settings') }}</a>
      @endcan
    </div>
  </div>

  @include('pages.admin.newsletter.campaigns._safety_notice')

  <div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="wh-48 bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-people fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Total subscribers') }}</p>
              <h5 class="mb-0 fw-bold">{{ number_format($totalSubscribers) }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="wh-48 bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-check-circle fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Active') }}</p>
              <h5 class="mb-0 fw-bold">{{ number_format($activeSubscribers) }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="wh-48 bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-hourglass-split fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Pending') }}</p>
              <h5 class="mb-0 fw-bold">{{ number_format($pendingSubscribers) }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="wh-48 bg-secondary bg-opacity-10 text-secondary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-person-dash fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Unsubscribed') }}</p>
              <h5 class="mb-0 fw-bold">{{ number_format($unsubscribedSubscribers) }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">{{ __('Recent subscribers') }}</span>
          @can('view newsletter subscribers')
            <a href="{{ route('admin.newsletter.subscribers.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
          @endcan
        </div>
        <div class="card-body p-0">
          @if($recentSubscribers->isEmpty())
            <p class="text-muted text-center py-4 mb-0">{{ __('No subscribers yet.') }}</p>
          @else
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr>
                    <th class="ps-3">{{ __('Email') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="pe-3">{{ __('Date') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentSubscribers as $s)
                    @php
                      $badge = match($s->status) {
                        'active'       => 'text-bg-success',
                        'pending'      => 'text-bg-warning',
                        'unsubscribed' => 'text-bg-secondary',
                        'bounced','complained' => 'text-bg-danger',
                        default        => 'text-bg-secondary',
                      };
                    @endphp
                    <tr>
                      <td class="ps-3 fw-semibold">{{ $s->email }}</td>
                      <td class="text-muted">{{ $s->name }}</td>
                      <td><span class="badge {{ $badge }}">{{ $s->status }}</span></td>
                      <td class="pe-3 text-muted small">{{ optional($s->created_at)->format('d M, H:i') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-5 d-flex flex-column gap-3">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">{{ __('Lists') }}</span>
          @can('view newsletter lists')
            <a href="{{ route('admin.newsletter.lists.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Manage') }}</a>
          @endcan
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="wh-48 bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-collection fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Total lists') }}</p>
              <h5 class="mb-0 fw-bold">{{ number_format($totalLists) }}</h5>
            </div>
          </div>
          <p class="text-muted small mt-3 mb-0">
            {{ __('Lists let you group subscribers. The default list is auto-assigned to public signups.') }}
          </p>
        </div>
      </div>

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">{{ __('Templates') }}</span>
          @can('view newsletter templates')
            <a href="{{ route('admin.newsletter.templates.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Manage') }}</a>
          @endcan
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="wh-48 bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-file-earmark-text fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Total / active') }}</p>
              <h5 class="mb-0 fw-bold">{{ number_format($totalTemplates) }} / {{ number_format($activeTemplates) }}</h5>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">{{ __('Campaigns') }}</span>
          @can('view newsletter campaigns')
            <a href="{{ route('admin.newsletter.campaigns.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Manage') }}</a>
          @endcan
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center gap-3">
            <div class="wh-48 bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
              <i class="bi bi-megaphone fs-5"></i>
            </div>
            <div class="overflow-hidden">
              <p class="mb-0 small text-muted text-truncate">{{ __('Total / draft') }}</p>
              <h5 class="mb-0 fw-bold">{{ number_format($totalCampaigns) }} / {{ number_format($draftCampaigns) }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-0">
    <div class="col-12 col-xl-7">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">{{ __('Recent campaigns') }}</span>
          @can('view newsletter campaigns')
            <a href="{{ route('admin.newsletter.campaigns.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
          @endcan
        </div>
        <div class="card-body p-0">
          @if($recentCampaigns->isEmpty())
            <p class="text-muted text-center py-4 mb-0">{{ __('No campaigns yet.') }}</p>
          @else
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr>
                    <th class="ps-3">{{ __('Title / Subject') }}</th>
                    <th>{{ __('Template') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="pe-3">{{ __('Updated') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentCampaigns as $c)
                    @php
                      $badge = match($c->status) {
                        'draft'     => 'text-bg-secondary',
                        'ready'     => 'text-bg-info',
                        'test_sent' => 'text-bg-warning',
                        'delegated' => 'text-bg-primary',
                        'cancelled' => 'text-bg-danger',
                        default     => 'text-bg-secondary',
                      };
                    @endphp
                    <tr>
                      <td class="ps-3">
                        <div class="fw-semibold">{{ $c->title ?: $c->subject }}</div>
                        @if($c->title)<div class="text-muted small">{{ \Illuminate\Support\Str::limit($c->subject, 60) }}</div>@endif
                      </td>
                      <td class="text-muted small">{{ optional($c->template)->name ?: '—' }}</td>
                      <td><span class="badge {{ $badge }}">{{ $c->status }}</span></td>
                      <td class="pe-3 text-muted small">{{ optional($c->updated_at)->format('d M, H:i') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">{{ __('Recent templates') }}</span>
          @can('view newsletter templates')
            <a href="{{ route('admin.newsletter.templates.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
          @endcan
        </div>
        <div class="card-body p-0">
          @if($recentTemplates->isEmpty())
            <p class="text-muted text-center py-4 mb-0">{{ __('No templates yet.') }}</p>
          @else
            <ul class="list-group list-group-flush">
              @foreach($recentTemplates as $t)
                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                  <div>
                    <div class="fw-semibold small">{{ $t->name }}</div>
                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($t->subject, 60) ?: '—' }}</div>
                  </div>
                  @if($t->is_active)
                    <span class="badge text-bg-success">{{ __('active') }}</span>
                  @else
                    <span class="badge text-bg-secondary">{{ __('inactive') }}</span>
                  @endif
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>
  </div>

</x-app-layout>
