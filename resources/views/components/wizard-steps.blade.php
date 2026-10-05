@php
 $steps = [
 1 => ['label' => 'เพิ่มครัวเรือน', 'icon' => 'groups'],
 2 => ['label' => 'เพิ่มสวน', 'icon' => 'forest'],
 3 => ['label' => 'เพิ่มแปลง', 'icon' => 'grid_view'],
 4 => ['label' => 'เครื่องมือวิจัย', 'icon' => 'science'],
 ];
@endphp
<div class="d-flex flex-wrap align-items-center mb-4">
 @foreach ($steps as $num => $step)
 <div class="d-flex align-items-center">
 <div class="d-flex align-items-center justify-content-center rounded-circle text-white flex-shrink-0
 {{ $num < $current ? 'bg-gradient-success' : ($num == $current ? 'bg-gradient-dark' : 'bg-secondary opacity-6') }}"
 style="width: 2rem; height: 2rem;">
 @if ($num < $current)
 <i class="material-symbols-rounded" style="font-size: 1rem;">check</i>
 @else
 <i class="material-symbols-rounded" style="font-size: 1rem;">{{ $step['icon'] }}</i>
 @endif
 </div>
 <span class="ms-2 me-3 text-xs {{ $num == $current ? 'text-dark font-weight-bold' : 'text-secondary' }}">
 {{ $num }}. {{ $step['label'] }}
 </span>
 </div>
 @if (!$loop->last)
 <div class="flex-grow-1 me-3 mb-0" style="height: 2px; min-width: 1.5rem; max-width: 3rem; background-color: {{ $num < $current ? '#66BB6A' : '#dee2e6' }};"></div>
 @endif
 @endforeach
</div>
