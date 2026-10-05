<x-app-layout :title="'ทะเบียนสวน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ทะเบียนสวน</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">
 เพิ่มสวนใหม่ได้จากหน้ารายละเอียดของครัวเรือนนั้น ๆ เท่านั้น (เปิดครัวเรือน →
 กด "+ เพิ่มสวน") เพื่อป้องกันความสับสนของลำดับ ครัวเรือน → สวน → แปลง
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ชื่อสวน / รหัส</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">พื้นที่ (ไร่)</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">จำนวนแปลง</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($farms as $farm)
 <tr>
 <td>
 <div class="d-flex px-2 py-1">
 <div class="d-flex flex-column justify-content-center">
 <h6 class="mb-0 text-sm">{{ $farm->farm_name ?? $farm->farm_code }}</h6>
 <p class="text-xs text-secondary mb-0">{{ $farm->farm_code }}</p>
 </div>
 </div>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs">{{ $farm->household->head_name }}</span>
 </td>
 <td class="text-end">
 <span class="text-secondary text-xs font-weight-bold">{{ $farm->total_area_rai ?? '-' }}</span>
 </td>
 <td class="align-middle text-center">
 <span class="text-secondary text-xs font-weight-bold">{{ $farm->plots_count }}</span>
 </td>
 <td class="align-middle text-center text-sm">
 <x-status-badge :status="$farm->status" />
 </td>
 <td class="align-middle text-end">
 <a href="{{ route('farms.show', $farm) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>

 <div class="px-2">
 {{ $farms->links() }}
 </div>
</x-app-layout>
