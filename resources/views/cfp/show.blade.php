@php
 $labels = [
 'fert_production_N' => 'การผลิตปุ๋ย N', 'fert_production_P2O5' => 'การผลิตปุ๋ย P2O5', 'fert_production_K2O' => 'การผลิตปุ๋ย K2O',
 'urea_co2' => 'CO2 จากยูเรีย', 'soil_n2o_direct' => 'N2O ตรงจากดิน', 'pesticide_production' => 'การผลิตสารเคมีเกษตร',
 'kiln_burden' => 'ภาระจากเตา (ไบโอชาร์/น้ำส้มควันไม้)', 'branch_open_burn' => 'เผากิ่งกลางแจ้ง',
 'prebearing_allocation' => 'ปันส่วนช่วงก่อนให้ผลผลิต',
 ];
 $breakdown = $calc->breakdown_json ?? [];
 $total = (float) $calc->e_total_kgco2e;
@endphp
<x-app-layout :title="'ผล CFP แปลง '.$plot->plot_code">
 <div class="row">
 <div class="col-12">
 @if (session('status'))
 <div class="alert alert-success text-white text-sm mt-3">{{ session('status') }}</div>
 @endif
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 px-3 d-flex justify-content-between">
 <h6 class="text-white mb-0">CFP แปลง {{ $plot->plot_code }} · {{ $calc->period_start->format('d/m/Y') }} → {{ $calc->period_end->format('d/m/Y') }}</h6>
 <a href="{{ route('cfp.plot', $plot) }}" class="text-white text-xs">← กลับ</a>
 </div>
 </div>
 <div class="card-body">
 <div class="row mb-3">
 <div class="col-md-3"><div class="text-xs text-secondary">CFP ต่อ 1 กก. ผลสด</div><div class="h4 mb-0">{{ $calc->cfp_per_kg !== null ? number_format($calc->cfp_per_kg, 4) : '—' }}</div><div class="text-xxs text-secondary">kgCO2e/กก.</div></div>
 <div class="col-md-3"><div class="text-xs text-secondary">รวมทั้งรอบ</div><div class="h4 mb-0">{{ number_format($total, 2) }}</div><div class="text-xxs text-secondary">kgCO2e</div></div>
 <div class="col-md-3"><div class="text-xs text-secondary">ผลผลิต (อนุมัติแล้ว)</div><div class="h4 mb-0">{{ number_format($calc->harvest_kg, 2) }}</div><div class="text-xxs text-secondary">กก. · {{ $calc->area_rai }} ไร่</div></div>
 <div class="col-md-3"><div class="text-xs text-secondary">คุณภาพข้อมูล (เบื้องต้น)</div><div class="h4 mb-0">{{ $calc->data_quality_score }}/100</div><div class="text-xxs text-secondary">{{ $calc->status }} · {{ $calc->calculation_version }}</div></div>
 </div>

 <h6 class="text-sm">แจกแจงตามหมวด</h6>
 <div class="table-responsive mb-3">
 <table class="table align-items-center mb-0">
 <thead><tr><th class="text-xxs text-secondary">หมวด</th><th class="text-xxs text-secondary text-end">kgCO2e</th><th class="text-xxs text-secondary text-end">สัดส่วน</th></tr></thead>
 <tbody>
 @foreach ($breakdown as $key => $val)
 <tr>
 <td class="text-xs">{{ $labels[$key] ?? (str_starts_with($key, 'energy_') ? 'พลังงาน/น้ำ: '.substr($key, 7) : $key) }}</td>
 <td class="text-xs text-end">{{ number_format($val, 4) }}</td>
 <td class="text-xs text-end">{{ $total > 0 ? number_format($val / $total * 100, 1) : '0.0' }}%</td>
 </tr>
 @endforeach
 <tr><td class="text-xs font-weight-bold">รวม</td><td class="text-xs text-end font-weight-bold">{{ number_format($total, 4) }}</td><td></td></tr>
 </tbody>
 </table>
 </div>

 @if ($calc->sensitivity_json)
 <h6 class="text-sm">Sensitivity Analysis</h6>
 <div class="table-responsive mb-3">
 <table class="table align-items-center mb-0">
 <thead><tr><th class="text-xxs text-secondary">กรณี</th><th class="text-xxs text-secondary text-end">kgCO2e รวม</th><th class="text-xxs text-secondary text-end">CFP/กก.</th><th class="text-xxs text-secondary text-end">เปลี่ยน %</th></tr></thead>
 <tbody>
 @foreach ($calc->sensitivity_json as $s)
 <tr>
 <td class="text-xs">{{ $s['label'] }}</td>
 <td class="text-xs text-end">{{ number_format($s['e_total_kgco2e'], 4) }}</td>
 <td class="text-xs text-end">{{ $s['cfp_per_kg'] ?? '—' }}</td>
 <td class="text-xs text-end">{{ $s['change_pct'] ?? '—' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @endif

 @if ($calc->warnings_json)
 <h6 class="text-sm">คำเตือน / ข้อจำกัด</h6>
 <ul class="text-xs text-secondary">
 @foreach ($calc->warnings_json as $w)<li>{{ $w }}</li>@endforeach
 </ul>
 @endif

 <p class="text-xxs text-secondary mb-0">GWP: {{ $calc->gwp_version }} · ขอบเขต: ถึงประตูสวน · ผลนี้เป็นการประมาณการ ไม่ใช่ค่าที่ผ่านการทวนสอบ/รับรอง</p>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
