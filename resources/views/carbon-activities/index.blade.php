<x-app-layout :title="'กิจกรรมคาร์บอน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">กิจกรรมลดคาร์บอน (Carbon Activity Monitoring)</h6>
 @can('viewAny', App\Models\EmissionFactor::class)
 <a href="{{ route('emission-factors.index') }}" class="btn btn-sm bg-white text-dark mb-0">ค่าสัมประสิทธิ์การปล่อยก๊าซ</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">บันทึกใหม่ได้จากหน้ารายละเอียดของครัวเรือนนั้น ๆ เท่านั้น</p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">หมวดหมู่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ปริมาณ</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">CO2e (กก.)</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($activities as $activity)
 <tr>
 <td><span class="text-xs font-weight-bold px-2">{{ $activity->household->head_name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $activity->categoryLabel() }}</span></td>
 <td><span class="text-secondary text-xs font-weight-bold">{{ $activity->activity_date->format('d/m/Y') }}</span></td>
 <td class="text-end"><span class="text-sm">{{ number_format($activity->quantity, 2) }} {{ $activity->unit }}</span></td>
 <td class="text-end"><span class="text-sm">{{ $activity->calculation ? number_format($activity->calculation->co2e_kg, 2) : '-' }}</span></td>
 <td class="align-middle text-center text-sm"><x-status-badge :status="$activity->status" /></td>
 <td class="align-middle text-end">
 <a href="{{ route('carbon-activities.show', $activity) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="7" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $activities->links() }}</div>
</x-app-layout>
