<x-app-layout :title="'ทะเบียนแปลง'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ทะเบียนแปลง</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">
 เพิ่มแปลงใหม่ได้จากหน้ารายละเอียดของสวนนั้น ๆ เท่านั้น (เปิดสวน → กด
 "+ เพิ่มแปลง") เพื่อป้องกันความสับสนของลำดับ สวน → แปลง
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">รหัสแปลง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">สวน / ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">พันธุ์</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">พื้นที่ (ไร่)</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">จำนวนต้น</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($plots as $plot)
 <tr>
 <td>
 <h6 class="mb-0 text-sm px-2 py-1">{{ $plot->plot_code }}</h6>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs">{{ $plot->farm->farm_name ?? $plot->farm->farm_code }} · {{ $plot->farm->household->head_name }}</span>
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
 <tr><td colspan="7" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>

 <div class="px-2">
 {{ $plots->links() }}
 </div>
</x-app-layout>
