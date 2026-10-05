<x-app-layout :title="'ค่าสัมประสิทธิ์การปล่อยก๊าซ'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ค่าสัมประสิทธิ์การปล่อยก๊าซเรือนกระจก (Emission Factors)</h6>
 @can('create', App\Models\EmissionFactor::class)
 <a href="{{ route('emission-factors.create') }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มค่าสัมประสิทธิ์</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">
 ค่าที่ "ปัจจุบัน" (ยังไม่มีวันสิ้นสุด) คือค่าที่ระบบใช้คำนวณกิจกรรมคาร์บอนใหม่ ๆ
 ค่าที่มีวันสิ้นสุดแล้วเป็นค่าประวัติศาสตร์ที่ถูกแทนที่ (ไม่สามารถแก้ไขค่าเดิมได้ เพื่อไม่ให้กระทบผลคำนวณเก่า)
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">หมวดหมู่กิจกรรม</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ค่าสัมประสิทธิ์</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">หน่วย</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">แหล่งอ้างอิง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ใช้ตั้งแต่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ถึง</th>
 </tr>
 </thead>
 <tbody>
 @forelse ($factors as $factor)
 <tr>
 <td><span class="text-xs font-weight-bold px-2">{{ \App\Models\CarbonActivity::CATEGORY_LABELS[$factor->category] ?? $factor->category }}</span></td>
 <td class="text-end"><span class="text-sm">{{ number_format($factor->factor_value, 6) }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $factor->unit }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $factor->source ?? '-' }}</span></td>
 <td><span class="text-secondary text-xs font-weight-bold">{{ $factor->effective_from->format('d/m/Y') }}</span></td>
 <td>
 @if ($factor->effective_to)
 <span class="text-secondary text-xs">{{ $factor->effective_to->format('d/m/Y') }}</span>
 @else
 <span class="badge badge-sm bg-gradient-success">ปัจจุบัน</span>
 @endif
 </td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary text-sm py-4">ยังไม่มีค่าสัมประสิทธิ์ กรุณาเพิ่มก่อนบันทึกกิจกรรมคาร์บอน</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $factors->links() }}</div>
</x-app-layout>
