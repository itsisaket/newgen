<x-app-layout :title="'ระบบต้นทุนการผลิต'">
 @include('dashboards.partials.header', [
 'heading' => 'ระบบต้นทุนการผลิต',
 'subtitle' => 'ต้นทุน/ไร่ · ต้นทุน/ต้น · ต้นทุน/กก. · ปัจจัยต้นทุนหลัก · เทียบก่อน-หลัง (Baseline)',
 ])

 @php
 $driverChart = [
 'horizontal' => true,
 'unit' => 'บาท',
 'labels' => collect($byCategoryTotal)->keys()->map(fn ($key) => $categoryLabels[$key] ?? $key)->all(),
 'values' => collect($byCategoryTotal)->values()->all(),
 'empty' => 'ยังไม่มีข้อมูลต้นทุนในฤดูนี้',
 'categoryLabel' => 'หมวด',
 ];
 $maxHouseholdCost = max(1, (float) collect($rows)->max('summary.total_cost'));
 @endphp

 <div class="row g-4 mb-4">
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ต้นทุนเฉลี่ยต่อไร่" :value="$avgCostPerRai !== null ? number_format($avgCostPerRai, 0) : '-'" unit="บาท" icon="payments" tone="slate"
 :sub="'เฉลี่ยจาก '.number_format($householdCount).' ครัวเรือนที่มีแปลง'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ต้นทุนเฉลี่ยต่อต้น" :value="$avgCostPerTree !== null ? number_format($avgCostPerTree, 0) : '-'" unit="บาท" icon="park" tone="green"
 :sub="'ต้นทุนรวมทั้งขอบเขต '.number_format($totalCost, 0).' บาท'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ต้นทุนเฉลี่ยต่อกิโลกรัม" :value="$avgCostPerKg !== null ? number_format($avgCostPerKg, 1) : '-'" unit="บาท" icon="scale" tone="gold"
 sub="เทียบผลผลิต ที่อนุมัติแล้วเท่านั้น" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ประหยัดต้นทุนเทียบ Baseline" :value="$avgCostSavingVsBaseline !== null ? number_format($avgCostSavingVsBaseline, 0) : '-'" unit="บาท/ครัวเรือน" icon="savings" tone="blue"
 :sub="number_format($householdsWithSaving).' ครัวเรือนประหยัดได้จริง'" />
 </div>
 </div>

 <div class="row g-4 mb-4">
 <div class="col-lg-5">
 <x-dash.chart title="ปัจจัยต้นทุนหลัก (Cost Driver)" :chart="$driverChart"
 :subtitle="$topCostDriver ? 'หมวดที่ต้นทุนรวมสูงสุด: '.($categoryLabels[$topCostDriver] ?? $topCostDriver) : 'ต้นทุนรวมแยกตามหมวด (บาท)'" />
 </div>
 <div class="col-lg-7">
 <div class="viz-card">
 <div class="viz-card__head">
 <div>
 <h6 class="viz-card__title">ต้นทุนรายครัวเรือน</h6>
 <p class="viz-card__sub">20 ครัวเรือนที่ต้นทุนรวมสูงสุด · แถบสีแสดงขนาดเทียบกับครัวเรือนที่สูงสุด</p>
 </div>
 </div>
 <div class="viz-card__body viz-card__body--flush">
 <div class="viz-table-scroll">
 <table class="viz-table">
 <thead>
 <tr>
 <th class="ps-4">ครัวเรือน</th>
 <th class="num">ต้นทุนรวม (บาท)</th>
 <th class="num">บาท/ไร่</th>
 <th class="num pe-4">บาท/กก.</th>
 </tr>
 </thead>
 <tbody>
 @forelse ($rows as $row)
 <tr>
 <td class="ps-4">
 <a href="{{ route('households.production-cost', ['household' => $row['household'], 'crop_season_id' => $season?->id]) }}">{{ $row['household']->head_name }}</a>
 </td>
 <td class="num">
 <div class="data-bar">
 <span style="width: {{ round($row['summary']['total_cost'] / $maxHouseholdCost * 100, 1) }}%"></span>
 <em>{{ number_format($row['summary']['total_cost'], 0) }}</em>
 </div>
 </td>
 <td class="num">{{ $row['summary']['cost_per_rai'] !== null ? number_format($row['summary']['cost_per_rai'], 0) : '-' }}</td>
 <td class="num pe-4">{{ $row['summary']['cost_per_kg'] !== null ? number_format($row['summary']['cost_per_kg'], 1) : '-' }}</td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 <p class="viz-card__foot">
 กดชื่อครัวเรือนเพื่อดูต้นทุนแยกหมวด/แปลงของครัวเรือนนั้น ·
 <a href="{{ route('production-costs.index', $season ? ['crop_season_id' => $season->id] : []) }}">ดูรายการทุกครัวเรือน &rarr;</a>
 </p>
 </div>
 </div>
 </div>
</x-app-layout>
