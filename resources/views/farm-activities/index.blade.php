<x-app-layout :title="'บันทึกกิจกรรมในสวน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">บันทึกกิจกรรมในสวน</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">
 บันทึกกิจกรรมใหม่ได้จากหน้ารายละเอียดของแปลงนั้น ๆ เท่านั้น (เปิดแปลง → กด
 "+ บันทึกกิจกรรม") เพื่อป้องกันความสับสนของลำดับ แปลง → กิจกรรมสวน
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ปัจจัยการผลิต / ปริมาณ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">แปลง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">กิจกรรม</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">ต้นทุนรวม</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ผู้บันทึก</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($activities as $activity)
 <tr>
 <td>
 <span class="text-secondary text-xs font-weight-bold ps-2">{{ $activity->activity_date->format('d/m/Y') }}</span>
 </td>
 <td class="ps-2">
 <span class="text-xs font-weight-bold">{{ $activity->plot->plot_code }}</span>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs">{{ $activity->plot->farm->household->head_name }}</span>
 </td>
 <td class="ps-2">
 <span class="text-xs">{{ $activity->activityType->name }}</span>
 </td>
 <td class="ps-2">
 @if ($activity->material)
 <span class="text-xs">{{ $activity->material->name }}</span>
 @if ($activity->quantity)
 <span class="text-secondary text-xs">({{ number_format($activity->quantity, 2) }} {{ $activity->material->unit }})</span>
 @endif
 @else
 <span class="text-secondary text-xs">-</span>
 @endif
 </td>
 <td class="text-end">
 <span class="text-secondary text-xs font-weight-bold">{{ number_format($activity->total_cost ?? 0, 2) }}</span>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs font-weight-bold">{{ $activity->recordedBy->name }}</span>
 </td>
 <td class="align-middle text-center text-sm">
 <x-status-badge :status="$activity->status" />
 </td>
 <td class="align-middle text-end">
 <a href="{{ route('farm-activities.show', $activity) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="9" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>

 <div class="px-2">
 {{ $activities->links() }}
 </div>
</x-app-layout>
