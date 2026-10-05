<x-app-layout :title="'ต้นทุนการผลิต'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
 <div>
 <h5 class="mb-1">ต้นทุนการผลิต — {{ $household->head_name }}</h5>
 <p class="text-sm text-secondary mb-0">
 รหัส {{ $household->household_code }}
 @if ($season)
 · ฤดูผลิต: {{ $season->name }}
 @else
 · รวมทุกฤดูผลิต
 @endif
 </p>
 </div>
 <form method="GET" action="{{ route('households.production-cost', $household) }}" class="d-flex align-items-center gap-2">
 <select name="crop_season_id" class="form-control form-control-sm" onchange="this.form.submit()" style="width:auto;">
 <option value="">รวมทุกฤดูผลิต</option>
 @foreach ($seasons as $option)
 <option value="{{ $option->id }}" @selected($season?->id === $option->id)>{{ $option->name }}</option>
 @endforeach
 </select>
 </form>
 </div>

 <p class="text-xs text-secondary">
 คำนวณสดจากข้อมูล (กิจกรรมสวน) ทุกครั้งที่เปิดหน้านี้ ไม่ใช่ตัวเลขที่บันทึกไว้ล่วงหน้า
 — ต้นทุน/กิโลกรัม นับเฉพาะผลผลิต ที่ "อนุมัติแล้ว" เท่านั้น ถ้ายังไม่มีผลผลิตที่อนุมัติ
 ในช่วงนี้จะแสดง "-"
 </p>

 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-3 col-6 mb-3 mb-md-0">
 <p class="text-xs text-secondary mb-0">ต้นทุนรวม</p>
 <p class="text-lg font-weight-bold mb-0">{{ number_format($summary['total_cost'], 2) }} บาท</p>
 </div>
 <div class="col-md-3 col-6 mb-3 mb-md-0">
 <p class="text-xs text-secondary mb-0">ต้นทุน/ไร่</p>
 <p class="text-lg font-weight-bold mb-0">{{ $summary['cost_per_rai'] !== null ? number_format($summary['cost_per_rai'], 2) : '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">ต้นทุน/ต้น</p>
 <p class="text-lg font-weight-bold mb-0">{{ $summary['cost_per_tree'] !== null ? number_format($summary['cost_per_tree'], 2) : '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">จำนวนแปลงที่คำนวณ</p>
 <p class="text-lg font-weight-bold mb-0">{{ $summary['plot_count'] }} แปลง ({{ number_format($summary['total_area_rai'], 2) }} ไร่)</p>
 </div>
 </div>
 <hr class="horizontal dark my-3">
 <div class="row">
 <div class="col-md-4 col-6 mb-3 mb-md-0">
 <p class="text-xs text-secondary mb-0">ผลผลิตที่อนุมัติแล้ว </p>
 <p class="text-lg font-weight-bold mb-0">{{ number_format($summary['harvest_weight_kg'], 2) }} กก.</p>
 </div>
 <div class="col-md-4 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">ต้นทุน/กิโลกรัม</p>
 <p class="text-lg font-weight-bold mb-0">{{ $summary['cost_per_kg'] !== null ? number_format($summary['cost_per_kg'], 2) : '-' }}</p>
 </div>
 </div>
 </div>
 </div>

 <div class="row">
 <div class="col-lg-6 mb-3">
 <div class="card h-100">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 px-3">
 <h6 class="text-white text-capitalize mb-0">ต้นทุนแยกตามหมวด</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">หมวด</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ต้นทุน (บาท)</th>
 </tr>
 </thead>
 <tbody>
 @forelse ($summary['by_category'] as $category => $amount)
 <tr>
 <td class="text-sm">{{ \App\Services\ProductionCostService::CATEGORY_LABELS[$category] ?? $category }}</td>
 <td class="text-end text-sm font-weight-bold">{{ number_format($amount, 2) }}</td>
 </tr>
 @empty
 <tr><td colspan="2" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูลกิจกรรมสวน ในช่วงนี้</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>

 <div class="col-lg-6 mb-3">
 <div class="card h-100">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 px-3">
 <h6 class="text-white text-capitalize mb-0">เทียบก่อน-หลัง (Baseline)</h6>
 </div>
 </div>
 <div class="card-body">
 @if ($summary['baseline'])
 <div class="row">
 <div class="col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ต้นทุน Baseline</p>
 <p class="text-sm font-weight-bold mb-0">
 {{ $summary['baseline']->total_cost !== null ? number_format($summary['baseline']->total_cost, 2).' บาท' : 'ไม่ได้บันทึกไว้' }}
 </p>
 </div>
 <div class="col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ต้นทุนปัจจุบัน</p>
 <p class="text-sm font-weight-bold mb-0">{{ number_format($summary['total_cost'], 2) }} บาท</p>
 </div>
 <div class="col-12">
 <p class="text-xs text-secondary mb-0">ผลต่าง (Baseline − ปัจจุบัน)</p>
 @if ($summary['cost_saving_vs_baseline'] !== null)
 <p class="text-sm font-weight-bold mb-0 {{ $summary['cost_saving_vs_baseline'] >= 0 ? 'text-success' : 'text-danger' }}">
 {{ $summary['cost_saving_vs_baseline'] >= 0 ? 'ลดลง' : 'เพิ่มขึ้น' }}
 {{ number_format(abs($summary['cost_saving_vs_baseline']), 2) }} บาท
 </p>
 @else
 <p class="text-sm text-secondary mb-0">Baseline ยังไม่ได้บันทึกต้นทุนไว้ เปรียบเทียบไม่ได้</p>
 @endif
 </div>
 </div>
 <a href="{{ route('household-baselines.show', $summary['baseline']) }}" class="text-secondary text-xs">ดู Baseline นี้ &rarr;</a>
 @else
 <p class="text-sm text-secondary mb-0">
 ครัวเรือนนี้ยังไม่มี Baseline {{ $season ? 'สำหรับฤดูผลิตที่เลือก' : '' }}
 ให้เปรียบเทียบ — <a href="{{ route('household-baselines.create', ['household_id' => $household->id]) }}">เพิ่ม Baseline</a>
 </p>
 @endif
 </div>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 px-3">
 <h6 class="text-white text-capitalize mb-0">แยกตามแปลง</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">แปลง</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">พื้นที่ (ไร่)</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ต้นทุนรวม</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ต้นทุน/ไร่</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ต้นทุน/ต้น</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ต้นทุน/กก.</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($summary['plots'] as $row)
 <tr>
 <td class="text-sm">{{ $row['plot']->plot_code }}</td>
 <td class="text-end text-sm">{{ $row['plot']->area_rai ?? '-' }}</td>
 <td class="text-end text-sm font-weight-bold">{{ number_format($row['summary']['total_cost'], 2) }}</td>
 <td class="text-end text-sm">{{ $row['summary']['cost_per_rai'] !== null ? number_format($row['summary']['cost_per_rai'], 2) : '-' }}</td>
 <td class="text-end text-sm">{{ $row['summary']['cost_per_tree'] !== null ? number_format($row['summary']['cost_per_tree'], 2) : '-' }}</td>
 <td class="text-end text-sm">{{ $row['summary']['cost_per_kg'] !== null ? number_format($row['summary']['cost_per_kg'], 2) : '-' }}</td>
 <td class="text-end">
 <a href="{{ route('plots.show', $row['plot']) }}" class="text-secondary font-weight-bold text-xs">ดูแปลง</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="7" class="text-center text-secondary text-sm py-4">ครัวเรือนนี้ยังไม่มีแปลง</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <p><a href="{{ route('households.show', $household) }}" class="text-secondary text-sm">&larr; กลับไปหน้าครัวเรือน</a></p>
 </div>
 </div>
</x-app-layout>
