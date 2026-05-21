@foreach ($menu as $site)
    @php
        $depth = $depth ?? 0;

        // Normalize "root" to 0 for comparisons
        $parentGroupId = is_null($site->parent_id) ? 0 : (int) $site->parent_id;

        // Children of this site (menuSites already contains all sites attached to the menu)
        $children = $menuSites
            ->filter(function ($c) use ($site) {
                $pid = is_null($c->parent_id) ? 0 : (int) $c->parent_id;
                return $pid === (int) $site->id;
            })
            ->sortBy(fn($c) => (int) ($c->pivot->menu_order ?? 0))
            ->values();

        // Siblings = same normalized parentGroupId (0 == root)
        $siblings = $menuSites
            ->filter(function ($s) use ($parentGroupId) {
                $pid = is_null($s->parent_id) ? 0 : (int) $s->parent_id;
                return $pid === (int) $parentGroupId;
            })
            ->sortBy(fn($s) => (int) ($s->pivot->menu_order ?? 0))
            ->values();

        // Determine if current site is first/last among siblings
        $currentIndex = $siblings->search(fn($s) => (int) $s->id === (int) $site->id);
        $isFirst = ($currentIndex === 0);
        $isLast  = ($currentIndex !== false) && ($currentIndex === ($siblings->count() - 1));
    @endphp


    <li class="mb-2">
        <div class="site-row d-flex align-items-center justify-content-between gap-3">
            <div>
                <div class="fw-semibold"><a href="{{ route('admin.sites.show', $site->id) }}">{{ $site->name }}</a></div>
                <div class="site-slug">/{{ $site->slug }}</div>
            </div>

            <div class="d-flex align-items-center gap-1 site-actions">
                <a href="{{ route('admin.sites.edit', $site->id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil"></i>
                </a>

                {{-- Up --}}
                <form method="POST" action="{{ route('admin.sites.moveUp', ['menu' => $menuId, 'site' => $site->id]) }}">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $parentGroupId }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Up" {{ $isFirst ? 'disabled' : '' }}>
                        <i class="bi bi-arrow-up"></i>
                    </button>
                </form>

                {{-- Down --}}
                <form method="POST" action="{{ route('admin.sites.moveDown', ['menu' => $menuId, 'site' => $site->id]) }}">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $parentGroupId }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Down" {{ $isLast ? 'disabled' : '' }}>
                        <i class="bi bi-arrow-down"></i>
                    </button>
                </form>
            </div>
        </div>


        @if($children->isNotEmpty())
            <div class="tree-indent mt-2">
                <ul class="list-unstyled mb-0 children-list-group">
                    @include('pages.admin.snippets.menu-order', [
                        'menu'      => $children,
                        'menuSites' => $menuSites,
                        'depth'     => $depth + 1,
                        'menuId'    => $menuId,
                    ])
                </ul>
            </div>
        @endif

    </li>
@endforeach
