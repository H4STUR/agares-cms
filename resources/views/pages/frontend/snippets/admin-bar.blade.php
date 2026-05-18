@php
  $site = $data['site'] ?? null;
@endphp

@if(auth()->check() && auth()->user()->can('admin nav'))

<style>/* Make room so navbar isn't covered */
  /* .agares-theme {
    padding-top: 44px;
  } */

  .adminbar-logo{
    height: 25px;
  }
</style>

  <div class="adminbar" role="navigation" aria-label="Admin quick bar">
    <div class="adminbar-inner">

      <div class="adminbar-left">
        <a class="adminbar-brand" href="{{ url('/admin') }}" target="_blank">
          <img class="adminbar-logo" src="{{ asset('assets/admin/images/agares-logo.png') }}" height="25" alt="logo"> 
        </a>

        @if($site)
          <span class="adminbar-sep">•</span>
          <span class="adminbar-muted">
            Viewing: <strong>{{ $site->name }}</strong>
          </span>
        @endif
      </div>

      <div class="adminbar-right">
        @if($site && auth()->user()->can('manage sites'))
          <a class="adminbar-link" href="{{ route('admin.sites.show', $site->id) }}" target="_blank">
            Page info
          </a>
        @endif
        @if($site && auth()->user()->can('manage sites'))
          <a class="adminbar-link" href="{{ route('admin.sites.edit', $site->id) }}" target="_blank">
            Edit page
          </a>
        @endif

        @can('view sites')
          <a class="adminbar-link" href="{{ route('admin.sites') }}" target="_blank">Pages</a>
        @endcan

        {{-- @can('view menus')
          <a class="adminbar-link" href="{{ route('admin.menus') }}" target="_blank">Menus</a>
        @endcan --}}

        @can('view media')
          <a class="adminbar-link" href="{{ route('admin.media') }}" target="_blank">Media</a>
        @endcan

        @can('view settings')
          <a class="adminbar-link" href="{{ route('admin.settings') }}" target="_blank">Settings</a>
        @endcan

        <span class="adminbar-sep">|</span>

        <a class="adminbar-link" href="{{ url('/admin') }}" target="_blank">Dashboard</a>

        <form method="POST" action="{{ route('logout') }}" class="adminbar-logout">
          @csrf
          <button type="submit" class="adminbar-link adminbar-btn">Logout ({{auth()->user()->name}})</button>
        </form>
      </div>

    </div>
  </div>
@endif
