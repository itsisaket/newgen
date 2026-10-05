<x-app-layout :title="'ระบบพยากรณ์ผลผลิต'">
 @include('dashboards.partials.header', [
 'heading' => 'ระบบพยากรณ์ผลผลิต',
 'subtitle' => 'แปลงที่คาดว่าจะเก็บเกี่ยวรายสัปดาห์ · พันธุ์ · ความแม่นยำของการพยากรณ์ ( เทียบ )',
 ])

 @php
 // byWeek keys are ISO weeks like "2026-W14" (InternalDashboardController::forecast())
 // - label each column by the Monday that starts that week, which reads
 // far more naturally than a week number.
 $weekStart = fn (string $isoWeek) => \Carbon\Carbon::now()
 ->setISODate((int) substr($isoWeek, 0, 4), (int) substr($isoWeek, strpos($isoWeek, 'W') + 1))
 ->startOfWeek();
 $weeks = collect($byWeek);
 $peakWeek = $weeks->isNotEmpty() ? $weeks->sortDesc()->keys()->first() : null;
 $weekChart = [
 'unit' => 'แปลง',
 'labels' => $weeks->keys()->map(fn ($w) => $weekStart($w)->format('d/m'))->all(),
 'values' => $weeks->values()->all(),
 'empty' => 'ยังไม่มีแปลงที่บันทึกวันคาดว่าจะเก็บเกี่ยว ในฤดูนี้',
 'categoryLabel' => 'สัปดาห์ที่เริ่ม',
 ];
 $varieties = collect($byVariety)->sortByDesc('area_rai');
 $varietyChart = [
 'horizontal' => true,
 'unit' => 'ไร่',
 'decimals' => 1,
 'labels' => $varieties->keys()->all(),
 'values' => $varieties->pluck('area_rai')->values()->all(),
 'empty' => 'ยังไม่มีข้อมูล',
 'table' => [
 'head' => ['พันธุ์', 'แปลง', 'พื้นที่ (ไร่)'],
 'rows' => $varieties->map(fn ($row, $name) => [$name, $row['plots'], $row['area_rai']])->values()->all(),
 ],
 ];
 @endphp

 <div class="row g-4 mb-4">
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="แปลงที่มีการพยากรณ์ " :value="number_format($forecastPlotCount)" unit="แปลง" icon="insights" tone="green"
 sub="มีวันคาดว่าจะเก็บเกี่ยวแล้ว" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="สัปดาห์ที่คาดเก็บเกี่ยวมากที่สุด" :value="$peakWeek ? $weekStart($peakWeek)->format('d/m/Y') : '-'" icon="event_available" tone="gold"
 :sub="$peakWeek ? number_format($weeks[$peakWeek]).' แปลงในสัปดาห์นั้น' : null" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="น้ำหนักคาดการณ์เฉลี่ยต่อผล" :value="$expectedAvgWeight !== null ? number_format($expectedAvgWeight, 2) : '-'" unit="กก./ผล" icon="scale" tone="slate" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ความคลาดเคลื่อนเฉลี่ย" :value="$avgAbsVariancePct !== null ? '±'.number_format($avgAbsVariancePct, 1).'%' : '-'" icon="target" tone="blue"
 sub="พยากรณ์เทียบผลผลิตจริงที่อนุมัติแล้ว" />
 </div>
 </div>

 <div class="row g-4 mb-4">
 <div class="col-lg-7">
 <x-dash.chart title="จำนวนแปลงที่คาดว่าจะเก็บเกี่ยว รายสัปดาห์" subtitle="แต่ละแท่ง = 1 สัปดาห์ (ระบุวันจันทร์ที่เริ่มสัปดาห์)" :chart="$weekChart" />
 </div>
 <div class="col-lg-5">
 <x-dash.chart title="พื้นที่ที่มีการพยากรณ์ แยกตามพันธุ์" subtitle="ไร่ · กดปุ่มตารางเพื่อดูจำนวนแปลง" :chart="$varietyChart" />
 </div>
 </div>

 <div class="row g-4 mb-4">
 <div class="col-12">
 <div class="viz-card">
 <div class="viz-card__head">
 <div>
 <h6 class="viz-card__title">แปลงที่ผลจริงต่างจากพยากรณ์มากที่สุด</h6>
 <p class="viz-card__sub">สูงสุด 15 แปลง · ▲ ได้มากกว่าที่คาด · ▼ ได้น้อยกว่าที่คาด</p>
 </div>
 </div>
 <div class="viz-card__body viz-card__body--flush">
 <div class="viz-table-scroll">
 <table class="viz-table">
 <thead>
 <tr>
 <th class="ps-4">แปลง</th>
 <th>ครัวเรือน</th>
 <th class="num">คาดการณ์ (กก.)</th>
 <th class="num">จริง (กก.)</th>
 <th class="num pe-4">ส่วนต่าง</th>
 </tr>
 </thead>
 <tbody>
 @forelse ($accuracyRows as $row)
 <tr>
 <td class="ps-4"><a href="{{ route('plots.show', $row['plot']) }}">{{ $row['plot']->plot_code }}</a></td>
 <td class="text-secondary">{{ $row['plot']->farm->household->head_name ?? '-' }}</td>
 <td class="num">{{ number_format($row['expected_kg'], 1) }}</td>
 <td class="num">{{ number_format($row['actual_kg'], 1) }}</td>
 <td class="num pe-4">
 @if ($row['variance_pct'] === null)
 -
 @elseif ($row['variance_pct'] >= 0)
 <span class="delta delta--up">▲ +{{ number_format($row['variance_pct'], 1) }}%</span>
 @else
 <span class="delta delta--down">▼ {{ number_format($row['variance_pct'], 1) }}%</span>
 @endif
 </td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary py-4">ยังไม่มีแปลงที่เทียบพยากรณ์กับผลจริงได้ (ต้องมีทั้ง ที่ระบุจำนวนผล/น้ำหนักคาด และ ที่อนุมัติแล้ว)</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
