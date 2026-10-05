<x-app-layout :title="'ระยะพัฒนาการ'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">ระยะพัฒนาการทุเรียน</h5>
 <p class="text-sm text-secondary mb-0">
 {{ $record->plot->plot_code }} · {{ $record->plot->farm->household->head_name }} · {{ $record->observed_date->format('d/m/Y') }}
 </p>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ระยะพัฒนาการ</p>
 <p class="text-sm mb-0">
 @php
 $stages = ['flowering' => 'แตกใบ/ออกดอก', 'full_bloom' => 'ดอกบาน', 'fruit_set' => 'ติดผล', 'fruit_development' => 'แต่งผล/พัฒนาผล'];
 @endphp
 {{ $stages[$record->stage] ?? $record->stage }}
 </p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ฤดูผลิต</p>
 <p class="text-sm mb-0">{{ $record->cropSeason->name }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">จำนวนผลคงเหลือ</p>
 <p class="text-sm mb-0">{{ $record->fruit_count ?? '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">น้ำหนักเฉลี่ยคาดการณ์</p>
 <p class="text-sm mb-0">{{ $record->expected_avg_weight_kg ?? '-' }} กก./ผล</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">ผลผลิตคาดการณ์ (Forecast Yield)</p>
 <p class="text-sm font-weight-bold mb-0">
 @if ($record->fruit_count && $record->expected_avg_weight_kg)
 {{ number_format($record->fruit_count * $record->expected_avg_weight_kg, 2) }} กก.
 @else
 -
 @endif
 </p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">วันที่คาดว่าจะเก็บเกี่ยว</p>
 <p class="text-sm mb-0">{{ $record->expected_harvest_date?->format('d/m/Y') ?? '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">ผู้บันทึก</p>
 <p class="text-sm mb-0">{{ $record->recordedBy->name }}</p>
 </div>
 </div>
 </div>
 </div>

 <p class="text-xs text-secondary">
 หมายเหตุ: ตัวเลข "ผลผลิตคาดการณ์" ด้านบนคำนวณจากสูตรเริ่มต้น (Blueprint หัวข้อ 10.1)
 ของแปลงนี้เพียงรายการเดียว ยังไม่ใช่ Dashboard พยากรณ์รวมระดับครัวเรือน/ตำบล/อำเภอ/จังหวัด
 พร้อม Calibration Factor ตามหัวข้อ 10.2 (อยู่ในแผน Sprint 5)
 </p>

 <p><a href="{{ route('durian-phenology-records.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
