<x-app-layout :title="'CFP คาร์บอนฟุตพริ้นท์'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 px-3">
 <h6 class="text-white text-capitalize mb-0">CFP — คาร์บอนฟุตพริ้นท์ทุเรียน (ถึงประตูสวน, ต่อ 1 กก. ผลสด)</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">
 เลือกแปลงเพื่อจัดการรอบการผลิตและคำนวณ CFP ผลเป็นการประมาณการเบื้องต้น (ยังไม่ผ่านการทวนสอบ)
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">แปลง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">รอบที่เปิดอยู่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">CFP ล่าสุด (kgCO2e/กก.)</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($plots as $plot)
 @php($calc = $latest[$plot->id] ?? null)
 @php($open = $openCycles[$plot->id] ?? null)
 <tr>
 <td class="ps-3"><span class="text-xs font-weight-bold">{{ $plot->plot_code }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $plot->farm->household->head_name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $open ? 'เริ่ม '.$open->start_date->format('d/m/Y') : '—' }}</span></td>
 <td class="text-end"><span class="text-secondary text-xs font-weight-bold">{{ $calc && $calc->cfp_per_kg !== null ? number_format($calc->cfp_per_kg, 4) : '—' }}</span></td>
 <td class="text-end pe-3"><a href="{{ route('cfp.plot', $plot) }}" class="text-secondary font-weight-bold text-xs">เปิด</a></td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มีแปลง</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $plots->links() }}</div>
</x-app-layout>
