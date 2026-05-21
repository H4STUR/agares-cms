{{-- resources/views/pages/admin/inputs/faq.blade.php --}}

@php
  use App\Models\FaqItem;

  $settings = [];
  if (is_string($value) && $value !== '') {
    $arr = json_decode($value, true);
    if (is_array($arr)) $settings = $arr;
  }

  $heading = old('heading', $settings['heading'] ?? null);

  $items = FaqItem::where('input_instance_id', $instanceId)
    ->orderBy('sort_order')
    ->get();

  $accordionId = 'faq-settings-' . ($instanceId ?? uniqid());
@endphp

<div class="bg-body-tertiary border rounded-4 p-3">

  {{-- SETTINGS --}}
  <div class="accordion mb-3" id="{{ $accordionId }}">
    <div class="accordion-item">
      <h2 class="accordion-header" id="{{ $accordionId }}-heading">
        <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#{{ $accordionId }}-collapse"
                aria-expanded="false"
                aria-controls="{{ $accordionId }}-collapse">
          <i class="bi bi-gear me-2"></i> {{ __('FAQ settings') }}
        </button>
      </h2>

      <div id="{{ $accordionId }}-collapse"
           class="accordion-collapse collapse"
           aria-labelledby="{{ $accordionId }}-heading"
           data-bs-parent="#{{ $accordionId }}">
        <div class="accordion-body">

          <form action="{{ route('admin.inputInstances.faq.settings', $instanceId) }}" method="POST">
            @csrf

            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label">{{ __('Heading (optional)') }}</label>
                <input class="form-control" name="heading" value="{{ $heading }}" placeholder="FAQ">
              </div>
            </div>

            <div class="mt-3 d-flex justify-content-end">
              <button class="btn btn-outline-primary" type="submit">
                <i class="bi bi-save me-1"></i> {{ __('Save settings') }}
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  {{-- ITEMS HEADER --}}
  <div class="d-flex align-items-center justify-content-between mb-2">
    <div class="fw-semibold">
      <i class="bi bi-patch-question me-1"></i> {{ __('FAQ items') }}
    </div>
    <span class="text-muted small">#{{ $instanceId }}</span>
  </div>

  {{-- Hidden move forms (initial items) --}}
  @foreach($items as $it)
    @php
      $upFormId   = "moveUpFaqItem{$it->id}";
      $downFormId = "moveDownFaqItem{$it->id}";
    @endphp

    <form id="{{ $upFormId }}" action="{{ route('admin.faqItems.move', $it->id) }}" method="POST" class="d-none">
      @csrf
      <input type="hidden" name="dir" value="up">
    </form>

    <form id="{{ $downFormId }}" action="{{ route('admin.faqItems.move', $it->id) }}" method="POST" class="d-none">
      @csrf
      <input type="hidden" name="dir" value="down">
    </form>
  @endforeach

  {{-- BULK UPDATE --}}
  <form action="{{ route('admin.inputInstances.faq.items.bulkUpdate', $instanceId) }}" method="POST" class="m-0" id="faq-bulk-form-{{ $instanceId }}">
    @csrf
    @method('PATCH')

    <div class="list-group mb-3" id="faq-items-list-{{ $instanceId }}">
      @forelse($items as $i => $it)
        @php
          $upFormId   = "moveUpFaqItem{$it->id}";
          $downFormId = "moveDownFaqItem{$it->id}";
        @endphp

        <div class="list-group-item" id="faq-row-{{ $it->id }}">
          <div class="row g-2">
            <div class="col-12 col-lg-8">
              <label class="form-label small mb-1">{{ __('Question') }}</label>
              <input class="form-control form-control-sm"
                     name="items[{{ $it->id }}][question]"
                     value="{{ old("items.$it->id.question", $it->question) }}"
                     required>
            </div>

            <div class="col-12 col-lg-4 d-flex align-items-end justify-content-between">
              <div>
                <input type="hidden" name="items[{{ $it->id }}][is_active]" value="0">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch"
                         id="faqActive{{ $it->id }}"
                         name="items[{{ $it->id }}][is_active]"
                         value="1"
                         {{ old("items.$it->id.is_active", $it->is_active) ? 'checked' : '' }}>
                  <label class="form-check-label small" for="faqActive{{ $it->id }}">
                    {{ __('Active') }}
                  </label>
                </div>
              </div>

              <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-secondary" type="submit" form="{{ $upFormId }}"
                        {{ $i === 0 ? 'disabled' : '' }}>
                  <i class="bi bi-arrow-up"></i>
                </button>

                <button class="btn btn-outline-secondary" type="submit" form="{{ $downFormId }}"
                        {{ $i === (count($items) - 1) ? 'disabled' : '' }}>
                  <i class="bi bi-arrow-down"></i>
                </button>

                {{-- Delete via confirmAction modal --}}
                <button class="btn btn-outline-danger"
                        type="button"
                        onclick="deleteFaqItem({{ $it->id }})">
                    <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label small mb-1">{{ __('Answer') }}</label>
              <textarea class="form-control form-control-sm"
                        name="items[{{ $it->id }}][answer]"
                        rows="4">{{ old("items.$it->id.answer", $it->answer) }}</textarea>
            </div>
          </div>
        </div>
      @empty
        <div class="list-group-item text-muted" id="faq-empty-{{ $instanceId }}">{{ __('No FAQ items yet.') }}</div>
      @endforelse
    </div>

    <div class="d-flex justify-content-end gap-2">
      {{-- AJAX add item (NO data-url here!) --}}
      <button class="btn btn-outline-primary"
              type="button"
              id="faq-add-btn-{{ $instanceId }}"
              data-post-url="{{ route('admin.inputInstances.faq.items.store', $instanceId) }}"
              data-move-url-template="{{ route('admin.faqItems.move', ['itemId' => '__ID__']) }}"
              data-delete-url-template="{{ route('admin.faqItems.destroy', ['itemId' => '__ID__']) }}">
        <i class="bi bi-plus-lg me-1"></i> {{ __('Add item') }}
      </button>

      <button class="btn btn-primary" type="submit">
        <i class="bi bi-save me-1"></i> {{ __('Save FAQ') }}
      </button>
    </div>
  </form>

</div>

@push('scripts')
<script>
  // Delete uses your global modal
  function confirmDeleteFaqItem(itemId, url) {
    window.confirmAction({
      action: url,
      method: 'DELETE',
      title: 'Delete FAQ item',
      body: 'Are you sure you want to delete',
      name: `#${itemId}`,
      danger: true,
      submitText: 'Yes, delete'
    });
  }

(function () {
  const instanceId = '{{ $instanceId }}';
  const btn  = document.getElementById('faq-add-btn-' + instanceId);
  const list = document.getElementById('faq-items-list-' + instanceId);
  if (!btn || !list) return;

  const csrf = '{{ csrf_token() }}';

  const esc = (s) => String(s ?? '')
    .replaceAll('&','&amp;')
    .replaceAll('<','&lt;')
    .replaceAll('>','&gt;')
    .replaceAll('"','&quot;')
    .replaceAll("'","&#039;");

  const postUrl = btn.getAttribute('data-post-url'); // ✅ NOT data-url
  const moveUrlTpl = btn.getAttribute('data-move-url-template');
  const delUrlTpl  = btn.getAttribute('data-delete-url-template');
  const makeMoveUrl = (id) => moveUrlTpl.replace('__ID__', String(id));
  const makeDelUrl  = (id) => delUrlTpl.replace('__ID__', String(id));

  btn.addEventListener('click', async (e) => {
    e.preventDefault();
    e.stopPropagation();

    if (btn.disabled) return;
    btn.disabled = true;

    try {
      const res = await fetch(postUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          question: 'Example question',
          answer: 'Example answer',
        }),
      });

      const data = await res.json().catch(() => null);

      if (!res.ok || !data || data.success !== true || !data.item || !data.item.id) {
        alert((data && data.message) ? data.message : 'Failed to add FAQ item.');
        return;
      }

      const it = data.item;

      // remove empty placeholder if present
      const empty = document.getElementById('faq-empty-' + instanceId);
      if (empty) empty.remove();

      // create move forms for the new item (so arrows still work)
      const upFormId   = 'moveUpFaqItem' + it.id;
      const downFormId = 'moveDownFaqItem' + it.id;

      btn.insertAdjacentHTML('afterend', `
        <form id="${upFormId}" action="${esc(makeMoveUrl(it.id))}" method="POST" class="d-none">
          <input type="hidden" name="_token" value="${esc(csrf)}">
          <input type="hidden" name="dir" value="up">
        </form>
        <form id="${downFormId}" action="${esc(makeMoveUrl(it.id))}" method="POST" class="d-none">
          <input type="hidden" name="_token" value="${esc(csrf)}">
          <input type="hidden" name="dir" value="down">
        </form>
      `);

      const rowHtml = `
        <div class="list-group-item" id="faq-row-${it.id}">
          <div class="row g-2">
            <div class="col-12 col-lg-8">
              <label class="form-label small mb-1">{{ __('Question') }}</label>
              <input class="form-control form-control-sm"
                     name="items[${it.id}][question]"
                     value="${esc(it.question)}"
                     required>
            </div>

            <div class="col-12 col-lg-4 d-flex align-items-end justify-content-between">
              <div>
                <input type="hidden" name="items[${it.id}][is_active]" value="0">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch"
                         id="faqActive${it.id}"
                         name="items[${it.id}][is_active]"
                         value="1" checked>
                  <label class="form-check-label small" for="faqActive${it.id}">
                    {{ __('Active') }}
                  </label>
                </div>
              </div>

              <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-secondary" type="submit" form="${upFormId}">
                  <i class="bi bi-arrow-up"></i>
                </button>
                <button class="btn btn-outline-secondary" type="submit" form="${downFormId}">
                  <i class="bi bi-arrow-down"></i>
                </button>
                <button class="btn btn-outline-danger"
                        type="button"
                        onclick="confirmDeleteFaqItem(${it.id}, '${esc(makeDelUrl(it.id))}')">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label small mb-1">{{ __('Answer') }}</label>
              <textarea class="form-control form-control-sm"
                        name="items[${it.id}][answer]"
                        rows="4">${esc(it.answer ?? '')}</textarea>
            </div>
          </div>
        </div>
      `;

      list.insertAdjacentHTML('beforeend', rowHtml);

      const q = list.querySelector(`input[name="items[${it.id}][question]"]`);
      if (q) q.focus();

    } catch (err) {
      console.error(err);
      alert('Failed to add FAQ item.');
    } finally {
      btn.disabled = false;
    }
  });
})();
</script>

<script>
  async function deleteFaqItem(itemId) {
    // Use the SAME modal as other deletes
    window.confirmAction({
      action: `/admin/faq-items/${encodeURIComponent(itemId)}`,
      method: 'DELETE',
      title: 'Delete FAQ item',
      body: 'Are you sure you want to delete',
      name: `#${itemId}`,
      danger: true,
      submitText: 'Yes, delete'
    });

    // Then perform delete (same approach as deleteInstance)
    const url = `/admin/faq-items/${encodeURIComponent(itemId)}`;

    const res = await fetch(url, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });

    // Controller returns redirect/back, not JSON, so just reload
    location.reload();
  }
</script>

@endpush
