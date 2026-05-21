{{-- x-notification.blade.php --}}

@php
  $successMsg = session('success');
  $errorMsg   = session('error');

  $validationMsg = null;
  if ($errors->any()) {
      // keep <br> formatting for Lobibox
      $validationMsg = implode('<br>', $errors->all());
  }
@endphp

@if($successMsg)
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      Lobibox.notify('success', {
        pauseDelayOnHover: true,
        size: 'mini',
        rounded: true,
        icon: 'bi bi-check2-circle',
        delayIndicator: false,
        continueDelayOnInactiveTab: false,
        position: 'top right',
        msg: @json($successMsg),
      });
    });
  </script>
@endif

@if($errorMsg)
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      Lobibox.notify('error', {
        pauseDelayOnHover: true,
        size: 'mini',
        rounded: true,
        icon: 'bi bi-x-circle',
        delayIndicator: false,
        continueDelayOnInactiveTab: false,
        position: 'top right',
        msg: @json($errorMsg),
      });
    });
  </script>
@endif

@if($validationMsg)
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      Lobibox.notify('error', {
        pauseDelayOnHover: true,
        size: 'mini',
        rounded: true,
        icon: 'bx bx-error',
        delayIndicator: false,
        continueDelayOnInactiveTab: false,
        position: 'top right',
        // Lobibox supports HTML in msg in many configs; if yours doesn't, we can swap <br> to \n.
        msg: @json($validationMsg),
      });
    });
  </script>
@endif


<!-- Reusable Confirmation Modal (Delete / Duplicate / Any Action) -->
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-warning">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="confirmActionLabel">{{ __('Confirm action') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form method="POST" id="confirmActionForm">
        @csrf
        <input type="hidden" name="_method" id="confirmActionMethod" value="POST">

        <div class="modal-body">
          <p class="mb-0" id="confirmActionBody">{{ __('Are you sure you want to continue?') }}</p>
          <div class="mt-2">
            <span id="confirmActionName" class="fw-semibold"></span>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            {{ __('Cancel') }}
          </button>

          <button type="submit" class="btn btn-warning" id="confirmActionSubmit">
            <span class="me-1" id="confirmActionSpinner" style="display:none;">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </span>
            <span id="confirmActionSubmitText">{{ __('Yes, continue') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  #confirmActionModal { z-index: 2000; }
</style>


@push('scripts')
<style>
  /* Smooth modal entrance */
  .modal.fade .modal-dialog {
    transform: translateY(8px) scale(0.98);
    transition: transform .18s ease, opacity .18s ease;
  }
  .modal.show .modal-dialog {
    transform: translateY(0) scale(1);
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl  = document.getElementById('confirmActionModal');
  if (!modalEl) return;

  const form     = document.getElementById('confirmActionForm');
  const methodEl = document.getElementById('confirmActionMethod');
  const titleEl  = document.getElementById('confirmActionLabel');
  const bodyEl   = document.getElementById('confirmActionBody');
  const nameEl   = document.getElementById('confirmActionName');

  const submitBtn = document.getElementById('confirmActionSubmit');
  const submitTxt = document.getElementById('confirmActionSubmitText');
  const spinner   = document.getElementById('confirmActionSpinner');

  const bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });

  function applyVariant(variant) {
    const content = modalEl.querySelector('.modal-content');
    const header  = modalEl.querySelector('.modal-header');

    // reset
    content.className = 'modal-content';
    header.className  = 'modal-header';
    submitBtn.className = 'btn';

    switch ((variant || 'warning').toLowerCase()) {
      case 'danger':
        content.classList.add('border-danger');
        header.classList.add('bg-danger', 'text-white');
        submitBtn.classList.add('btn-danger');
        break;

      case 'primary':
        content.classList.add('border-primary');
        header.classList.add('bg-primary', 'text-white');
        submitBtn.classList.add('btn-primary');
        break;

      case 'success': 
        content.classList.add('border-success');
        header.classList.add('bg-success', 'text-white');
        submitBtn.classList.add('btn-success');
        break;

      default:
        content.classList.add('border-warning');
        header.classList.add('bg-warning', 'text-dark');
        submitBtn.classList.add('btn-warning');
        break;
    }
  }

  function openConfirm(opts) {
    const action = opts.action;
    if (!action) return;

    const method = (opts.method || 'POST').toUpperCase();
    const title  = opts.title || @json(__('Confirm action'));
    const body   = opts.body  || @json(__('Are you sure you want to continue?'));
    const name   = opts.name  || '';
    const variant = opts.variant || (method === 'DELETE' ? 'danger' : 'warning');
    const confirmText = opts.confirmText || (method === 'DELETE'
      ? @json(__('Yes, delete'))
      : @json(__('Yes, continue'))
    );

    form.action = action;
    methodEl.value = method;

    titleEl.textContent = title;
    bodyEl.textContent  = body;
    nameEl.textContent  = name;

    applyVariant(variant);

    submitTxt.textContent = confirmText;
    spinner.style.display = 'none';
    submitBtn.disabled = false;

    bsModal.show();

    // focus confirm button for smooth UX
    setTimeout(() => submitBtn.focus(), 100);
  }

  // Spinner + disable on submit
  form.addEventListener('submit', () => {
    spinner.style.display = '';
    submitBtn.disabled = true;
  });

  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-confirm]');
    if (!el) return;

    e.preventDefault();

    const action =
      el.getAttribute('data-action') ||
      el.getAttribute('data-url') ||
      el.getAttribute('href');

    const method = el.getAttribute('data-method') || 'POST';
    const title  = el.getAttribute('data-title')  || @json(__('Confirm action'));
    const body   = el.getAttribute('data-body')   || @json(__('Are you sure you want to continue?'));
    const name   = el.getAttribute('data-name')   || '';
    const variant = el.getAttribute('data-variant') || (String(method).toUpperCase() === 'DELETE' ? 'danger' : 'warning');
    const confirmText = el.getAttribute('data-confirm-text') || null;

    openConfirm({
      action,
      method,
      title,
      body,
      name,
      variant,
      confirmText
    });
  });

  window.confirmAction = function(opts) {
    openConfirm(opts || {});
  };
});
</script>
@endpush
