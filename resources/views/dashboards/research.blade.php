<x-app-layout :title="'ระบบวิจัยและประเมินผล'">
 @include('dashboards.partials.header', [
 'heading' => 'ระบบวิจัยและประเมินผล',
 'subtitle' => 'Baseline · การนำเทคโนโลยีไปใช้ · ระดับ ALP · ความครบถ้วนของข้อมูล · KPI',
 ])

 @php
 $alpTotal = (int) collect($alpByLevel)->sum();
 $alpShort = collect(\App\Models\AlpAssessment::LEVEL_LABELS)
 ->map(fn ($label, $level) => $level.' · '.trim(\Illuminate\Support\Str::before($label, '(')));
 $completenessChart = [
 'horizontal' => true,
 'unit' => '%',
 'decimals' => 1,
 'max' => 100,
 'label' => 'all',
 'labels' => array_values(\App\Services\DataCompletenessService::EXPECTED_FORMS),
 'values' => collect(\App\Services\DataCompletenessService::EXPECTED_FORMS)->keys()->map(fn ($key) => $dataCompleteness['pct'][$key] ?? 0)->all(),
 'table' => [
 'head' => ['แบบฟอร์ม', 'ครัวเรือนที่มีข้อมูลแล้ว', '%'],
 'rows' => collect(\App\Services\DataCompletenessService::EXPECTED_FORMS)
 ->map(fn ($label, $key) => [$label, $dataCompleteness['counts'][$key] ?? 0, $dataCompleteness['pct'][$key] ?? 0])
 ->values()->all(),
 ],
 ];
 $alpChart = [
 'horizontal' => true,
 'unit' => 'ครั้ง',
 'label' => 'all',
 'labels' => $alpShort->values()->all(),
 'values' => $alpShort->keys()->map(fn ($level) => (int) ($alpByLevel[$level] ?? 0))->all(),
 'empty' => 'ยังไม่มีการประเมิน ALP ในขอบเขตนี้',
 'table' => [
 'head' => ['ระดับ ALP', 'จำนวนการประเมิน'],
 'rows' => collect(\App\Models\AlpAssessment::LEVEL_LABELS)
 ->map(fn ($label, $level) => [$level.' '.$label, (int) ($alpByLevel[$level] ?? 0)])
 ->values()->all(),
 ],
 ];
 @endphp

 <div class="row g-4 mb-4">
 <div class="col-6 col-xl-3">
 <x-dash.kpi label=" Baseline ในฤดูนี้" :value="number_format($baselineCount)" unit="รายการ" icon="fact_check" tone="slate"
 :sub="'ต้นทุนเฉลี่ย '.($avgBaselineCost !== null ? number_format($avgBaselineCost, 0) : '-').' · รายได้เฉลี่ย '.($avgBaselineIncome !== null ? number_format($avgBaselineIncome, 0) : '-').' บาท'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="การนำเทคโนโลยีไปใช้ (Adoption)" :value="number_format($adoptionPct, 1).'%'" icon="precision_manufacturing" tone="green"
 :meter="$adoptionPct" :sub="number_format($adoptedCount).' จาก '.number_format($householdTotal).' ครัวเรือน'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ระดับ ALP เฉลี่ย " :value="$alpAvgLevel !== null ? number_format($alpAvgLevel, 1) : '-'" unit="/ 5" icon="trending_up" tone="gold"
 :meter="$alpAvgLevel !== null ? $alpAvgLevel / 5 * 100 : null" :sub="'จากการประเมิน '.number_format($alpTotal).' ครั้ง'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="บรรลุเป้า Net Benefit " :value="$kpiAchievedPct !== null ? number_format($kpiAchievedPct, 1).'%' : '-'" icon="flag" tone="blue"
 :meter="$kpiAchievedPct" :sub="number_format($impactAchieved).' จาก '.number_format($impactTotal).' ครัวเรือนที่คำนวณแล้ว'" />
 </div>
 </div>

 <div class="row g-4 mb-4">
 <div class="col-lg-6">
 <x-dash.chart title="ความครบถ้วนของข้อมูล" :chart="$completenessChart"
 :subtitle="'เฉลี่ยรวม '.number_format($dataCompleteness['avg_completeness_pct'], 1).'% · '.number_format($dataCompleteness['total_households']).' ครัวเรือนในขอบเขต'">
 <p class="text-xs text-secondary mt-2 mb-0">% ของครัวเรือนที่มีข้อมูลแบบฟอร์มนั้นแล้วอย่างน้อย 1 รายการ</p>
 </x-dash.chart>
 </div>
 <div class="col-lg-6">
 <x-dash.chart title="การกระจายระดับ ALP " subtitle="จำนวนการประเมินในแต่ละระดับ · 1 รับรู้ → 5 ถ่ายทอด/ขยายผล" :chart="$alpChart" />
 </div>
 </div>
</x-app-layout>
