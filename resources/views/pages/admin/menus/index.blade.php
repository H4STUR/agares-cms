<x-app-layout>

  {{-- Header --}}
  <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Dashboard</div>
    <div class="ps-3">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 p-0">
          <li class="breadcrumb-item">
            <a href="javascript:;"><i class="bx bx-home-alt"></i></a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">{{ __('Menus') }}</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="container-fluid">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
      <div>
        <h4 class="mb-0">{{ __('Sites & Menus') }}</h4>
        <div class="text-muted small">
          {{ __('Manage your menus and reorder sites within each menu. Hierarchy is based on parent/child sites.') }}
        </div>
      </div>

      <a href="{{ route('admin.sites.create') }}" class="btn btn-success d-flex align-items-center gap-2">
        <i class="material-icons-outlined">add</i>
        <span>{{ __('Add New Site') }}</span>
      </a>
    </div>

    {{-- Menus accordion --}}
    <div class="accordion" id="menusAccordion">
      @foreach($menus as $index => $menu)

        @php
          // ordered by pivot menu_order (as per controller)
          $menuSites = $menu->sites ?? collect();
          $roots = $menuSites->whereNull('parent_id')
              ->sortBy(fn($s) => (int)($s->pivot->menu_order ?? 0))
              ->values();

          // "first menu expanded by default"
          $expanded = $index === 0;
        @endphp

        <div class="accordion-item mb-3">
          <h2 class="accordion-header" id="menuHeading{{ $menu->id }}">
            <div class="d-flex align-items-stretch gap-2">

                {{-- MAIN ACCORDION TOGGLE --}}
                <button class="accordion-button {{ $expanded ? '' : 'collapsed' }} flex-grow-1"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#menuCollapse{{ $menu->id }}"
                        aria-expanded="{{ $expanded ? 'true' : 'false' }}"
                        aria-controls="menuCollapse{{ $menu->id }}">

                <div class="d-flex align-items-center gap-3 w-100">
                    <span class="badge rounded-pill text-bg-secondary">
                    #{{ $menu->id }}
                    </span>

                    <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $menu->name }}</div>
                    <small class="text-muted">{{ $menuSites->count() }} {{ __('sites') }}</small>
                    </div>

                
                </div>
                </button>

                {{-- DELETE (outside the accordion button, so it looks clean) --}}
                @if(empty($menu->is_system))
                <button type="button"
                        class="btn btn-outline-danger btn-sm px-3"
                        data-confirm
                        data-action="{{ route('admin.menus.destroy', $menu->id) }}"
                        data-method="DELETE"
                        data-variant="danger"
                        data-title="{{ __('Delete menu') }}"
                        data-body="{{ __('Delete this menu and delete ALL its pages?') }}"
                        data-name="{{ $menu->name }}"
                        data-confirm-text="{{ __('Yes, delete') }}">
                    <i class="bi bi-trash"></i>
                </button>
                @endif


            </div>
        </h2>



          {{-- IMPORTANT: no data-bs-parent => multiple can be open --}}
          <div id="menuCollapse{{ $menu->id }}"
               class="accordion-collapse collapse {{ $expanded ? 'show' : '' }}"
               aria-labelledby="menuHeading{{ $menu->id }}">
            <div class="accordion-body">

              @if($menuSites->isEmpty())
                <div class="text-muted">{{ __('No sites found in this menu.') }}</div>
              @else

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                  <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ __('Use arrows to reorder within the same parent group. Indentation shows hierarchy.') }}
                  </div>

                  {{-- Optional quick actions (you can add later) --}}
                  {{-- <a class="btn btn-sm btn-outline-secondary" href="#">Export</a> --}}
                </div>

                <ul class="list-unstyled mb-0 site-tree">
                  @include('pages.admin.snippets.menu-order', [
                    'menu'      => $roots,
                    'menuSites' => $menuSites,
                    'depth'     => 0,
                    'menuId'    => $menu->id,
                  ])
                </ul>
              @endif

            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.menus.store') }}" class="d-flex gap-2">
            @csrf
            <input type="text"
                    name="name"
                    class="form-control"
                    placeholder="New menu name..."
                    required>
            <button class="btn btn-primary" type="submit">
                 Add
            </button>
            </form>
        </div>
    </div>

  </div>

  @push('styles')
  <style>

  .accordion-button { border-radius: .6rem; }
  .accordion-item { border-radius: .75rem; overflow: hidden; }
  .accordion-header .btn { border-radius: .6rem; }

  .site-card .card {
    border-radius: .75rem;
    background: rgba(255,255,255,.02);
  }

  .site-card .card:hover {
    background: rgba(255,255,255,.04);
  }

  .site-card .btn-group form { display: inline-block; }

    .accordion-header .btn {
  line-height: 1;
}

    /* ---- Tree look & feel ---- */
    .site-tree { margin: 0; padding: 0; }

    .site-tree .children-list-group {
      margin-left: 0;
      padding-left: 0;
    }

    /* Card row in snippet: add better visuals */
    .site-row {
      background: rgba(255,255,255,0.02);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      padding: 12px 12px;
      transition: transform .12s ease, border-color .12s ease;
    }

    .site-row:hover {
      border-color: rgba(255,255,255,0.18);
      transform: translateY(-1px);
    }

    .tree-indent{
    margin-left: 0;
    padding-left: 25px;                 /* the dent amount per level */
    }

    /* Optional: tiny spacing so nested blocks breathe */
    .children-list-group > li{
    margin-bottom: 10px;
    }

    .site-slug {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 12px;
      color: rgba(255,255,255,0.55);
    }

    /* Buttons tighter */
    .site-actions .btn { border-radius: 10px; }

    @media (max-width: 992px) {
      .site-actions { width: 100%; justify-content: flex-end; }
    }
  </style>
  @endpush

</x-app-layout>
