{{--
 Stacked part-to-whole bar (23 ก.ย. round) - HTML, not a chart library:
 segments are separated by a 2px surface gap, and every segment is also
 listed in the legend with its count and % (so colour never carries the
 meaning alone).

 type="workflow" - //... Draft -> Submitted -> Verified -> Approved
 as a validated one-hue ordinal ramp (light -> dark =
 further along the workflow), plus "ขอแก้ไข" in gold.
 type="household" - ใช้งานอยู่ / ระงับการใช้งาน / ถอนตัว with the reserved
 status colours (good / neutral / critical).
--}}
@props(['counts' => [], 'type' => 'workflow', 'title' => null])
@php
 $palettes = [
 'workflow' => [
 'draft' => ['ร่าง', 'var(--viz-ord-1)'],
 'submitted' => ['ส่งแล้ว', 'var(--viz-ord-2)'],
 'verified' => ['ตรวจสอบแล้ว', 'var(--viz-ord-3)'],
 'approved' => ['อนุมัติแล้ว', 'var(--viz-ord-4)'],
 'revision_requested' => ['ขอแก้ไข', 'var(--viz-flag)'],
 ],
 'household' => [
 'active' => ['ใช้งานอยู่', 'var(--viz-status-good)'],
 'inactive' => ['ระงับการใช้งาน', 'var(--viz-neutral)'],
 'withdrawn' => ['ถอนตัว', 'var(--viz-status-critical)'],
 ],
 ];
 $segments = collect($palettes[$type] ?? $palettes['workflow'])
 ->map(fn ($meta, $key) => ['label' => $meta[0], 'color' => $meta[1], 'count' => (int) ($counts[$key] ?? 0)]);
 $total = $segments->sum('count');
@endphp
<div class="status-block">
 @if ($title)
 <div class="status-block__head">
 <span>{{ $title }}</span>
 <small>รวม {{ number_format($total) }} รายการ</small>
 </div>
 @endif
 @if ($total === 0)
 <p class="text-sm text-secondary mb-0">ยังไม่มีข้อมูล</p>
 @else
 <div class="status-bar" role="img" aria-label="{{ $title ?? 'สัดส่วนสถานะ' }}: {{ $segments->filter(fn ($s) => $s['count'] > 0)->map(fn ($s) => $s['label'].' '.$s['count'])->implode(', ') }}">
 @foreach ($segments as $segment)
 @if ($segment['count'] > 0)
 <span class="status-bar__seg" style="flex: {{ $segment['count'] }} 1 0; background: {{ $segment['color'] }};" title="{{ $segment['label'] }}: {{ number_format($segment['count']) }}"></span>
 @endif
 @endforeach
 </div>
 <div class="status-legend">
 @foreach ($segments as $segment)
 <span class="status-legend__item {{ $segment['count'] === 0 ? 'is-zero' : '' }}">
 <i class="status-legend__key" style="background: {{ $segment['color'] }};"></i>{{ $segment['label'] }}<strong>{{ number_format($segment['count']) }}</strong>
 <span class="text-xs">({{ round($segment['count'] / $total * 100) }}%)</span>
 </span>
 @endforeach
 </div>
 @endif
</div>
