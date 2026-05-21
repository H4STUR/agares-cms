{{-- 
implementation of FAQ input field for frontend display

  @php $faq = $data['faq'] ?? null; @endphp

  @if($faq && $faq->field?->field_type === 'faq')
    @include('pages.frontend.partials.faq', ['instance' => $faq])
  @endif
--}}

@php
  use App\Models\FaqItem;

  /** @var \App\Models\InputInstance $instance */

  // settings stored in input_instances.value as JSON: {"heading":"FAQ"}
  $settings = [];
  if (is_string($instance->value) && trim($instance->value) !== '') {
    $arr = json_decode($instance->value, true);
    if (is_array($arr)) $settings = $arr;
  }

  $heading = $settings['heading'] ?? null;

  // load items
  $items = FaqItem::where('input_instance_id', $instance->id)
    ->where('is_active', true)
    ->orderBy('sort_order')
    ->get();

  $domId = 'ag-faq-' . ($instance->id ?? uniqid());
@endphp

@if($items->isEmpty())
  {{-- You can hide completely instead if you prefer --}}
  <div class="text-muted">
    {{ __('No FAQ items yet.') }}
  </div>
@else
  <div class="ag-faq" id="{{ $domId }}">

    @if($heading)
      <h3 class="mb-3">{{ $heading }}</h3>
    @endif

    <div class="accordion" id="{{ $domId }}-accordion">
      @foreach($items as $i => $it)
        @php
          $itemId = $domId . '-item-' . $it->id;
          $collapseId = $itemId . '-collapse';
          $headingId = $itemId . '-heading';
          $openFirst = $i === 0; // change to false if you want all collapsed by default
        @endphp

        <div class="accordion-item">
          <h2 class="accordion-header" id="{{ $headingId }}">
            <button class="accordion-button {{ $openFirst ? '' : 'collapsed' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}"
                    aria-expanded="{{ $openFirst ? 'true' : 'false' }}"
                    aria-controls="{{ $collapseId }}">
              {{ $it->question }}
            </button>
          </h2>

          <div id="{{ $collapseId }}"
               class="accordion-collapse collapse {{ $openFirst ? 'show' : '' }}"
               aria-labelledby="{{ $headingId }}"
               data-bs-parent="#{{ $domId }}-accordion">
            <div class="accordion-body">
              {{-- If you want to allow HTML in answers, change to: {!! $it->answer !!} --}}
              {!! nl2br(e($it->answer ?? '')) !!}
            </div>
          </div>
        </div>
      @endforeach
    </div>

  </div>
@endif
