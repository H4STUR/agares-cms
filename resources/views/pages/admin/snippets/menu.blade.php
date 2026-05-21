@foreach ($menu as $site)
    @php
        $depth = $depth ?? 0;

        // Active (selected) site highlight
        $isActive = !empty($activeSiteId) && (int)$activeSiteId === (int)$site->id;

        // Indentation + optional "tree line" feel
        $padLeft = 12 + ($depth * 18);

        // Children from THIS menu’s sites collection, ordered by pivot menu_order
        $children = $menuSites
            ->where('parent_id', $site->id)
            ->sortBy(fn($c) => (int)($c->pivot->menu_order ?? 0))
            ->values();
    @endphp

    <li class="list-group-item d-flex align-items-center justify-content-between {{ $isActive ? 'active' : '' }}"
        style="padding-left: {{ $padLeft }}px;">

        <div class="d-flex align-items-center gap-2">
            {{-- Depth indicator --}}
            @if($depth > 0)
                <span class="text-muted" aria-hidden="true">↳</span>
            @endif

            {{-- Link (keep your route target) --}}
            <a
                href="{{ route('admin.sites.show', $site->id) }}"
                class="text-decoration-none {{ $isActive ? 'text-white' : '' }}"
            >
                {{ $site->name }}
            </a>
        </div>

        {{-- Optional: slug or icons --}}
        <small class="{{ $isActive ? 'text-white-50' : 'text-muted' }}">
            {{ $site->slug }}
        </small>
    </li>

    @if($children->isNotEmpty())
        @include('pages.admin.snippets.menu', [
            'menu' => $children,
            'menuSites' => $menuSites,
            'depth' => $depth + 1,
            'activeSiteId' => $activeSiteId,
        ])
    @endif
@endforeach
