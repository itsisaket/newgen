<x-app-layout :title="'ระยะพัฒนาการทุเรียน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ระยะพัฒนาการทุเรียน (Phenology)</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">
 บันทึกใหม่ได้จากหน้ารายละเอียดของแปลงนั้น ๆ เท่านั้น (เปิดแปลง → กด "+ บันทึกระยะพัฒนาการ")
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่สำรวจ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">แปลง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ระยะ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">จำนวนผล</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @php
 $stages = ['flowering' => 'แตกใบ/ออกดอก', 'full_bloom' => 'ดอกบาน', 'fruit_set' => 'ติดผล', 'fruit_development' => 'แต่งผล/พัฒนาผล'];
 @endphp
 @forelse ($records as $record)
 <tr>
 <td><span class="text-secondary text-xs font-weight-bold ps-2">{{ $record->observed_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-xs font-weight-bold">{{ $record->plot->plot_code }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $record->plot->farm->household->head_name }}</span></td>
 <td class="ps-2"><span class="text-xs">{{ $stages[$record->stage] ?? $record->stage }}</span></td>
 <td class="text-end"><span class="text-secondary text-xs font-weight-bold">{{ $record->fruit_count ?? '-' }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('durian-phenology-records.show', $record) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
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
 {{ $records->links() }}
 </div>
</x-app-layout>
