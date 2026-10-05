<x-app-layout :title="'สวน'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $farm->farm_name ?? $farm->farm_code }}</h5>
 <p class="text-sm text-secondary mb-0">
 รหัส {{ $farm->farm_code }}
 · ครัวเรือน:
 <a href="{{ route('households.show', $farm->household) }}">{{ $farm->household->head_name }}</a>
 </p>
 </div>
 <div class="d-flex align-items-center gap-2">
 <x-status-badge :status="$farm->status" />
 @can('update', $farm)
 <a href="{{ route('farms.edit', $farm) }}" class="btn btn-outline-secondary btn-sm mb-0">แก้ไข</a>
 @endcan
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ที่ตั้ง (ตำบล/อำเภอ/จังหวัด)</p>
 <p class="text-sm mb-0">
 @if ($farm->tambon || $farm->district || $farm->province)
 {{ $farm->tambon->name_th ?? '-' }} / {{ $farm->district->name_th ?? '-' }} / {{ $farm->province->name_th ?? '-' }}
 @else
 -
 @endif
 </p>
 @if ($farm->address)
 <p class="text-xs text-secondary mb-0 mt-1">{{ $farm->address }}</p>
 @endif
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">พื้นที่รวม</p>
 <p class="text-sm mb-0">{{ $farm->total_area_rai ?? '-' }} ไร่</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">แหล่งน้ำ</p>
 <p class="text-sm mb-0">{{ $farm->water_source ?? '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">พิกัด GPS</p>
 <p class="text-sm mb-0">
 @if ($farm->gps_lat && $farm->gps_lng)
 {{ $farm->gps_lat }}, {{ $farm->gps_lng }}
 @else
 -
 @endif
 </p>
 </div>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">แปลงในสวนนี้</h6>
 <a href="{{ route('plots.create', ['farm_id' => $farm->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มแปลง</a>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">รหัสแปลง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">พันธุ์</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">พื้นที่ (ไร่)</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">จำนวนต้น</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($farm->plots as $plot)
 <tr>
 <td>
 <h6 class="mb-0 text-sm px-2 py-1">{{ $plot->plot_code }}</h6>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs">{{ $plot->durianVariety->name ?? '-' }}</span>
 </td>
 <td class="text-end">
 <span class="text-secondary text-xs font-weight-bold">{{ $plot->area_rai ?? '-' }}</span>
 </td>
 <td class="text-end">
 <span class="text-secondary text-xs font-weight-bold">{{ $plot->tree_count ?? '-' }}</span>
 </td>
 <td class="align-middle text-center text-sm">
 <x-status-badge :status="$plot->status" />
 </td>
 <td class="align-middle text-end">
 <a href="{{ route('plots.show', $plot) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary text-sm py-4">ยังไม่มีแปลง</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <p><a href="{{ route('farms.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
