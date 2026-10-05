<x-app-layout :title="'ระบบคาร์บอนและสิ่งแวดล้อม'">
 @include('dashboards.partials.header', [
 'heading' => 'ระบบคาร์บอนและสิ่งแวดล้อม',
 'subtitle' => 'การลดปุ๋ย/สารเคมี · ชีวมวลที่นำกลับมาใช้ · พลังงานทดแทน · CO2e โดยประมาณ ',
 ])

 @php
 $co2eSorted = collect($categoryLabels)
 ->map(fn ($label, $key) => ['label' => $label, 'co2e' => (float) ($co2eByCategory[$key] ?? 0), 'qty' => (float) ($quantityByCategory[$key] ?? 0)])
 ->sortByDesc('co2e');
 $co2eChart = [
 'horizontal' => true,
 'unit' => 'กก. CO2e',
 'decimals' => 1,
 'labels' => $co2eSorted->pluck('label')->values()->all(),
 'values' => $co2eSorted->pluck('co2e')->values()->all(),
 'empty' => 'ยังไม่มีกิจกรรมคาร์บอน ที่อนุมัติแล้วในฤดูนี้',
 'table' => [
 'head' => ['หมวดกิจกรรม', 'ปริมาณกิจกรรมรวม', 'CO2e (กก.)'],
 'rows' => $co2eSorted->map(fn ($row) => [$row['label'], $row['qty'], $row['co2e']])->values()->all(),
 ],
 ];
 @endphp

 <div class="row g-4 mb-4">
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="CO2e โดยประมาณ (อนุมัติแล้ว)" :value="number_format($totalCo2eKg, 1)" unit="กก. CO2e" icon="co2" tone="green"
 :sub="'จาก '.number_format($activityCount).' กิจกรรม'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ลดปุ๋ยเคมี/ใช้ปุ๋ยอินทรีย์" :value="number_format($fertilizerReductionQty, 1)" icon="eco" tone="green"
 sub="ปริมาณตามหน่วยของกิจกรรม" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ชีวมวลที่นำกลับมาใช้ (Biochar)" :value="number_format($biomassDivertedQty, 1)" icon="compost" tone="slate"
 sub="ปริมาณตามหน่วยของกิจกรรม" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="พลังงานทดแทน" :value="number_format($energyQty, 1)" icon="bolt" tone="gold"
 sub="ปริมาณตามหน่วยของกิจกรรม" />
 </div>
 </div>

 <div class="row g-4 mb-4">
 <div class="col-12">
 <x-dash.chart title="CO2e แยกตามหมวดกิจกรรม" subtitle="กก. CO2e · กดปุ่มตารางเพื่อดูปริมาณกิจกรรมของแต่ละหมวด" :chart="$co2eChart">
 <p class="text-xs text-secondary mt-3 mb-0">
 นับเฉพาะกิจกรรม ที่ผ่านการอนุมัติแล้ว (ตัดกิจกรรมที่ยังไม่ตรวจสอบออก เพื่อไม่ให้ตัวเลขที่ยังไม่ยืนยันมาปนกับตัวเลขที่เผยแพร่ได้)
 — ปริมาณกิจกรรมแต่ละหมวดใช้หน่วยต่างกัน จึงเทียบกันด้วย CO2e แทน
 </p>
 </x-dash.chart>
 </div>
 </div>
</x-app-layout>
