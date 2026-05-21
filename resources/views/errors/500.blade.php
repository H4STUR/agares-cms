<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>500</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  {{-- Agares theme --}}
  <link rel="stylesheet" href="{{ asset('assets/frontend/theme/assets/css/styles.css') }}">
  {{-- If you keep bootstrap on frontend, keep your patch too --}}
  <link rel="stylesheet" href="{{ asset('assets/frontend/theme/assets/css/bootstrap-patch.css') }}">

  <style>
    /* Page wrapper (theme-like background + centered layout) */
    .maintenance-page {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: var(--space-2xl) var(--space-lg);
      position: relative;
    }

    /* Top-right login button */
    .maintenance-login {
      position: absolute;
      top: var(--space-lg);
      right: var(--space-lg);
      z-index: 2;
    }

    /* Main card */
    .maintenance-card {
      width: 100%;
      max-width: 980px;
      padding: var(--space-2xl);
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    /* subtle glow blob like hero */
    .maintenance-card::before {
      content: '';
      position: absolute;
      top: -40%;
      left: 50%;
      transform: translateX(-50%);
      width: 520px;
      height: 520px;
      background: radial-gradient(circle, rgba(124, 58, 237, 0.18) 0%, transparent 70%);
      pointer-events: none;
    }

    .maintenance-inner {
      position: relative;
      z-index: 1;
      display: grid;
      gap: var(--space-xl);
      align-items: center;
    }

    .maintenance-title {
      font-size: clamp(2rem, 4vw, 3rem);
      margin: 0;
      background: linear-gradient(135deg, #ffffff 0%, #9ca3bc 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .maintenance-text {
      margin: 0;
      color: var(--color-text-secondary);
      font-size: var(--text-lg);
      line-height: 1.7;
      max-width: 680px;
      margin-inline: auto;
    }

    .maintenance-img {
      width: min(520px, 92%);
      height: auto;
      margin: 0 auto;
      display: block;
      filter: drop-shadow(0 18px 40px rgba(0,0,0,.55));
    }

    .maintenance-actions {
      display: flex;
      gap: var(--space-md);
      justify-content: center;
      flex-wrap: wrap;
      margin-top: var(--space-sm);
    }

    .maintenance-note {
      color: var(--color-text-tertiary);
      font-size: var(--text-sm);
      margin-top: var(--space-sm);
    }

    @media (max-width: 768px) {
      .maintenance-card {
        padding: var(--space-xl);
      }

      .maintenance-login {
        top: var(--space-md);
        right: var(--space-md);
      }
    }
  </style>
</head>

<body>
  <div class="agares-theme">
    <main class="maintenance-page">

      <div class="card card-glass maintenance-card">
        <div class="maintenance-inner">

          <div>
            <div class="badge badge-primary" style="margin-bottom: var(--space-md);">
              500
            </div>

            <h1 class="maintenance-title">500</h1>

            <p class="maintenance-text">
              Sorry, something went wrong on our end.
            </p>
          </div>

          <img
            src="{{ asset('assets/imgs/maintenance.png') }}"
            alt="Maintenance Mode"
            class="maintenance-img"
          >

        </div>
      </div>

    </main>
  </div>
</body>
</html>
