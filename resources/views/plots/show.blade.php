<x-app-layout :title="'แปลง'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $plot->plot_code }}</h5>
 <p class="text-sm text-secondary mb-0">
 สวน: <a href="{{ route('farms.show', $plot->farm) }}">{{ $plot->farm->farm_name ?? $plot->farm->farm_code }}</a>
 · ครัวเรือน: <a href="{{ route('households.show', $plot->farm->household) }}">{{ $plot->farm->household->head_name }}</a>
 </p>
 </div>
 <div class="d-flex align-items-center gap-2">
 <x-status-badge :status="$plot->status" />
 @can('update', $plot)
 <a href="{{ route('plots.edit', $plot) }}" class="btn btn-outline-secondary btn-sm mb-0">แก้ไข</a>
 @endcan
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">พันธุ์ทุเรียน</p>
 <p class="text-sm mb-0">{{ $plot->durianVariety->name ?? '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">พื้นที่ / จำนวนต้น</p>
 <p class="text-sm mb-0">{{ $plot->area_rai ?? '-' }} ไร่ / {{ $plot->tree_count ?? '-' }} ต้น</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">ปีที่ปลูก</p>
 <p class="text-sm mb-0">{{ $plot->planting_year ?? '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">ระบบน้ำ</p>
 <p class="text-sm mb-0">{{ $plot->irrigation_type ?? '-' }}</p>
 </div>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">กิจกรรมสวนล่าสุด </h6>
 <a href="{{ route('farm-activities.create', ['plot_id' => $plot->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ บันทึกกิจกรรม</a>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ประเภทกิจกรรม</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">ต้นทุนรวม</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($recentActivities as $activity)
 <tr>
 <td>
 <span class="text-secondary text-xs font-weight-bold px-2">{{ $activity->activity_date->format('d/m/Y') }}</span>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs">{{ $activity->activityType->name }}</span>
 </td>
 <td class="text-end">
 <span class="text-secondary text-xs font-weight-bold">{{ number_format($activity->total_cost ?? 0, 2) }}</span>
 </td>
 <td class="align-middle text-center text-sm">
 <x-status-badge :status="$activity->status" />
 </td>
 <td class="align-middle text-end">
 <a href="{{ route('farm-activities.show', $activity) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มีกิจกรรมในแปลงนี้</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ระยะพัฒนาการทุเรียน </h6>
 <a href="{{ route('durian-phenology-records.create', ['plot_id' => $plot->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ บันทึกระยะพัฒนาการ</a>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่สำรวจ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ระยะ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">จำนวนผล</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @php
 $stages = ['flowering' => 'แตกใบ/ออกดอก', 'full_bloom' => 'ดอกบาน', 'fruit_set' => 'ติดผล', 'fruit_development' => 'แต่งผล/พัฒนาผล'];
 @endphp
 @forelse ($recentPhenology as $observation)
 <tr>
 <td><span class="text-secondary text-xs font-weight-bold px-2">{{ $observation->observed_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $stages[$observation->stage] ?? $observation->stage }}</span></td>
 <td class="text-end"><span class="text-secondary text-xs font-weight-bold">{{ $observation->fruit_count ?? '-' }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('durian-phenology-records.show', $observation) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูลระยะพัฒนาการ</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ผลผลิตที่เก็บเกี่ยวจริง </h6>
 <a href="{{ route('harvest-records.create', ['plot_id' => $plot->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ บันทึกผลผลิต</a>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่เก็บเกี่ยว</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">น้ำหนัก (กก.)</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($recentHarvests as $harvest)
 <tr>
 <td><span class="text-secondary text-xs font-weight-bold px-2">{{ $harvest->harvest_date->format('d/m/Y') }}</span></td>
 <td class="text-end"><span class="text-secondary text-xs font-weight-bold">{{ number_format($harvest->actual_weight_kg, 2) }}</span></td>
 <td class="align-middle text-center text-sm"><x-status-badge :status="$harvest->status" /></td>
 <td class="align-middle text-end">
 <a href="{{ route('harvest-records.show', $harvest) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูลผลผลิต</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">การจัดการกิ่ง/เศษไม้ (CFP)</h6>
 <a href="{{ route('branch-disposal-records.create', ['plot_id' => $plot->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ บันทึกการจัดการกิ่ง</a>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">เส้นทาง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">น้ำหนักสด (กก.)</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($recentBranchDisposals as $disposal)
 <tr>
 <td><span class="text-secondary text-xs font-weight-bold px-2">{{ $disposal->disposal_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $disposal->routeLabel() }}</span></td>
 <td class="text-end"><span class="text-secondary text-xs font-weight-bold">{{ number_format($disposal->quantity_kg, 2) }}</span></td>
 <td class="align-middle text-center text-sm"><x-status-badge :status="$disposal->status" /></td>
 <td class="align-middle text-end">
 <a href="{{ route('branch-disposal-records.show', $disposal) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูลการจัดการกิ่ง</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">การใช้ผลิตภัณฑ์ชีวมวล </h6>
 <a href="{{ route('product-usages.create', ['plot_id' => $plot->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ บันทึกการใช้ผลิตภัณฑ์</a>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่ใช้</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ผลิตภัณฑ์</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">ปริมาณ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($recentProductUsages as $usage)
 <tr>
 <td><span class="text-secondary text-xs font-weight-bold px-2">{{ $usage->application_date->format('d/m/Y') }}</span></td>
 <td><span class="text-secondary text-xs font-weight-bold">{{ $usage->inventoryTransaction->product->name }}</span></td>
 <td class="text-end"><span class="text-secondary text-xs font-weight-bold">{{ number_format($usage->inventoryTransaction->quantity, 2) }} {{ $usage->inventoryTransaction->product->unit }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('product-usages.show', $usage) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูลการใช้ผลิตภัณฑ์</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 @can('viewAny', App\Models\DemonstrationComparisonGroup::class)
 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">แปลงสาธิต </h6>
 @can('create', [App\Models\DemonstrationPlot::class, $plot])
 <a href="{{ route('demonstration-plots.create', ['plot_id' => $plot->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มเข้าแปลงสาธิต</a>
 @endcan
 </div>
 </div>
 <div class="card-body">
 @forelse ($plot->demonstrationPlots as $enrollment)
 <p class="text-sm mb-1 d-flex justify-content-between">
 <a href="{{ route('demonstration-comparison-groups.show', $enrollment->comparisonGroup) }}">
 {{ $enrollment->comparisonGroup->name }}
 <span class="text-secondary text-xs">({{ $enrollment->group_type === 'treatment' ? 'Treatment' : 'Control' }})</span>
 </a>
 @if ($enrollment->ended_at)
 <span class="text-secondary text-xs">สิ้นสุด {{ $enrollment->ended_at->format('d/m/Y') }}</span>
 @else
 <span class="badge badge-sm bg-gradient-success">กำลังร่วม</span>
 @endif
 </p>
 @empty
 <p class="text-sm text-secondary mb-0">แปลงนี้ยังไม่เข้าร่วมชุดเปรียบเทียบใด</p>
 @endforelse
 </div>
 </div>
 @endcan

 <p><a href="{{ route('plots.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
