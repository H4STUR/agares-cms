<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Agares') }}</title>

  <!--favicon-->
  <link rel="icon" href="{{ asset('assets/admin/images/agares-logo.png') }}" type="image/png">

  <!--plugins-->
  <link href="{{ asset('assets/admin/theme/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/admin/theme/assets/plugins/metismenu/metisMenu.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/admin/theme/assets/plugins/metismenu/mm-vertical.css') }}">

  <!--bootstrap css-->
  <link href="{{ asset('assets/admin/theme/assets/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">

  <!--main css-->
  <link href="{{ asset('assets/admin/theme/assets/css/bootstrap-extended.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/admin/theme/sass/main.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/admin/theme/sass/dark-theme.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/admin/theme/sass/responsive.css') }}" rel="stylesheet">

  <style>
    .auth-cover-right { background-color: var(--bs-body-bg); }
  </style>
</head>

<body>
  <div class="section-authentication-cover">
    <div class="">
      <div class="row g-0">

        {{-- Left cover image --}}
        <div class="col-12 col-xl-7 col-xxl-8 auth-cover-left align-items-center justify-content-center d-none d-xl-flex border-end">
          <div class="card rounded-0 mb-0 border-0 shadow-none bg-transparent">
            <div class="card-body">
              <img src="{{ $coverImage }}"
                class="img-fluid auth-img-cover-login"
                width="650"
                alt="">
            </div>
          </div>
        </div>

        {{-- Right content --}}
        <div class="col-12 col-xl-5 col-xxl-4 auth-cover-right align-items-center justify-content-center">
          <div class="card rounded-0 m-3 mb-0 border-0 shadow-none">
            <div class="card-body p-sm-5">

              <img src="{{ asset('assets/admin/images/agares-logo.png') }}" class="logo-img mb-4" width="69" alt="Agares logo">
              
              {{-- Slot content (forms, etc.) --}}
              <div class="form-body">
                {{ $slot }}
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!--plugins-->
  <script src="{{ asset('assets/admin/theme/assets/js/jquery.min.js') }}"></script>

  <script>
    $(document).ready(function () {
      $("#show_hide_password a").on('click', function (event) {
        event.preventDefault();
        const $input = $('#show_hide_password input');
        const $icon = $('#show_hide_password i');

        if ($input.attr("type") === "text") {
          $input.attr('type', 'password');
          $icon.addClass("bi-eye-slash-fill").removeClass("bi-eye-fill");
        } else {
          $input.attr('type', 'text');
          $icon.addClass("bi-eye-fill").removeClass("bi-eye-slash-fill");
        }
      });
    });
  </script>
</body>
</html>
