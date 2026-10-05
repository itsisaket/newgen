<x-app-layout :title="'ผลกระทบเศรษฐกิจ'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
 <div>
 <h5 class="mb-1">ผลกระทบเศรษฐกิจ — {{ $household->head_name }}</h5>
 <p class="text-sm text-secondary mb-0">
 รหัส {{ $household->household_code }}
 @if ($season)
 · ฤดูผลิต: {{ $season->name }}
 @endif
 </p>
 </div>
 <form method="GET" action="{{ route('households.economic-impact', $household) }}" class="d-flex align-items-center gap-2">
 <select name="crop_season_id" class="form-control form-control-sm" onchange="this.form.submit()" style="width:auto;">
 <option value="">-- เลือกฤดูผลิต --</option>
 @foreach ($seasons as $option)
 <option value="{{ $option->id }}" @selected($season?->id === $option->id)>{{ $option->name }}</option>
 @endforeach
 </select>
 </form>
 </div>

 @if (! $season)
 <div class="card">
 <div class="card-body">
 <p class="text-sm text-secondary mb-0">กรุณาเลือกฤดูผลิตด้านบนเพื่อดูผลการคำนวณ (ไม่มีฤดูผลิตที่กำลัง "active" อยู่ในขณะนี้)</p>
 </div>
 </div>
 @else
 <p class="text-xs text-secondary">
 คำนวณสดโดย EconomicImpactService ทุกครั้งที่เปิดหน้านี้ จาก Baseline , ยอดขายจริง ( Sales)
 และต้นทุนการเดินเตาที่อนุมัติแล้ว — ไม่ใช่ตัวเลขที่ผู้ใช้กรอกเอง (Blueprint หัวข้อ 11)
 · คำนวณล่าสุด: {{ $impact->calculated_at->format('d/m/Y H:i') }} (สูตรรุ่น {{ $impact->calculation_version }})
 </p>

 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">Cost Saving</p>
 <p class="text-lg font-weight-bold mb-0">{{ number_format($impact->cost_saving, 2) }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">Durian Income Increase</p>
 <p class="text-lg font-weight-bold mb-0">{{ number_format($impact->durian_income_increase, 2) }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">Bioproduct Income</p>
 <p class="text-lg font-weight-bold mb-0">{{ number_format($impact->bioproduct_income, 2) }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">Additional Technology Cost</p>
 <p class="text-lg font-weight-bold mb-0 text-danger">-{{ number_format($impact->additional_technology_cost, 2) }}</p>
 </div>
 </div>
 <hr class="horizontal dark my-3">
 <div class="row align-items-center">
 <div class="col-md-6 col-8">
 <p class="text-xs text-secondary mb-0">Net Benefit (เป้าหมาย {{ number_format($targetBaht, 0) }} บาท/ครัวเรือน/ปี)</p>
 <p class="text-xl font-weight-bold mb-0">{{ number_format($impact->net_benefit, 2) }} บาท</p>
 </div>
 <div class="col-md-6 col-4 text-end">
 @if ($impact->target_status === 'achieved')
 <span class="badge badge-lg bg-gradient-success">บรรลุเป้าหมาย (Achieved)</span>
 @else
 <span class="badge badge-lg bg-gradient-secondary">ยังไม่ถึงเป้าหมาย (Not Achieved)</span>
 @endif
 </div>
 </div>
 </div>
 </div>
 @endif

 <p><a href="{{ route('households.show', $household) }}" class="text-secondary text-sm">&larr; กลับไปหน้าครัวเรือน</a></p>
 </div>
 </div>
</x-app-layout>
