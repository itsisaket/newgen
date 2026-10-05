{{--
 Chart card for the merged Dashboard (23 ก.ย. round). `chart` is the
 spec array read by public/js/drfis-charts.js (see that file's
 doc-comment for the keys). Every chart gets a "ตาราง" toggle that
 swaps the canvas for a table-view twin, so no value is hover-only.
 Anything passed in the slot renders under the chart (e.g. a note).
--}}
@props(['title', 'subtitle' => null, 'chart' => []])
@php($chartId = 'viz-'.\Illuminate\Support\Str::random(8))
<div {{ $attributes->merge(['class' => 'viz-card']) }}>
 <div class="viz-card__head">
 <div>
 <h6 class="viz-card__title">{{ $title }}</h6>
 @if ($subtitle)
 <p class="viz-card__sub">{{ $subtitle }}</p>
 @endif
 </div>
 <button type="button" class="viz-card__toggle" data-viz-toggle="{{ $chartId }}" aria-pressed="false" aria-controls="{{ $chartId }}-table">
 <i class="material-symbols-rounded" aria-hidden="true">table_rows</i><span data-label>ตาราง</span>
 </button>
 </div>
 <div class="viz-card__body">
 <div class="viz-canvas-wrap" id="{{ $chartId }}-wrap">
 <canvas id="{{ $chartId }}" role="img" aria-label="{{ $title }}" data-chart="{{ json_encode($chart, JSON_UNESCAPED_UNICODE) }}"></canvas>
 </div>
 <div id="{{ $chartId }}-table" hidden></div>
 {{ $slot }}
 </div>
</div>
