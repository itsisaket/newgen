{{--
 KPI stat tile for the merged Dashboard (23 ก.ย. round) - see
 public/css/drfis-dashboard.css. `tone` only colours the icon/top rule
 (decoration, not data). Optional `meter` (0-100) draws a same-ramp
 progress meter for ratio KPIs (adoption %, KPI %, ALP x/5). `href`
 turns the whole tile into a link.
--}}
@props([
 'label',
 'value',
 'unit' => null,
 'sub' => null,
 'icon' => 'insights',
 'tone' => 'green',
 'meter' => null,
 'href' => null,
])
@php($tag = $href ? 'a' : 'div')
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'kpi-tile kpi-tile--'.$tone]) }}>
 <span class="kpi-tile__icon" aria-hidden="true"><i class="material-symbols-rounded">{{ $icon }}</i></span>
 <div class="kpi-tile__body">
 <p class="kpi-tile__label">{{ $label }}</p>
 <p class="kpi-tile__value">{{ $value }}@if ($unit && $value !== '-')<span class="kpi-tile__unit">{{ $unit }}</span>@endif</p>
 @if ($meter !== null)
 <div class="kpi-meter" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round(max(0, min(100, $meter))) }}" aria-label="{{ $label }}">
 <span style="width: {{ max(0, min(100, $meter)) }}%"></span>
 </div>
 @endif
 @if ($sub)
 <p class="kpi-tile__sub">{{ $sub }}</p>
 @endif
 {{ $slot }}
 </div>
</{{ $tag }}>
