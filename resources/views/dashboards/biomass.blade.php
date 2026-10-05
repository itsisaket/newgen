<x-app-layout :title="'ระบบชีวมวลและผลิตภัณฑ์'">
 @include('dashboards.partials.header', [
 'heading' => 'ระบบชีวมวลและผลิตภัณฑ์',
 'subtitle' => 'การเดินเตา · ผลผลิตชีวมวล · การใช้ในสวน · สต็อกคงเหลือ · ยอดขายผลิตภัณฑ์',
 ])

 @php
 $outputSorted = collect($outputByProduct)->sortDesc();
 $stockSorted = collect($stockByProduct)->sortDesc();
 $outputChart = [
 'horizontal' => true,
 'decimals' => 1,
 'labels' => $outputSorted->keys()->all(),
 'values' => $outputSorted->values()->all(),
 'empty' => 'ยังไม่มีการเดินเตาที่อนุมัติแล้วในฤดูนี้',
 'categoryLabel' => 'ผลิตภัณฑ์',
 ];
 $stockChart = [
 'horizontal' => true,
 'decimals' => 1,
 'labels' => $stockSorted->keys()->all(),
 'values' => $stockSorted->values()->all(),
 'empty' => 'ยังไม่มีสต็อกผลิตภัณฑ์ชีวมวล',
 'categoryLabel' => 'ผลิตภัณฑ์',
 ];
 @endphp

 <div class="row g-4 mb-4">
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="วัตถุดิบเข้าเตา (อนุมัติแล้ว)" :value="number_format($inputKg, 0)" unit="กก." icon="local_fire_department" tone="gold"
 :sub="'จากการเดินเตา '.number_format($batchCount).' ครั้ง'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ใช้ผลิตภัณฑ์ในสวน " :value="number_format($usageKg, 0)" icon="recycling" tone="green"
 sub="หน่วยตามผลิตภัณฑ์ (ลิตร/กก.)" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="สต็อกคงเหลือรวม" :value="number_format(collect($stockByProduct)->sum(), 0)" icon="inventory_2" tone="slate"
 :sub="number_format(collect($stockByProduct)->count()).' ผลิตภัณฑ์ · หน่วยตามผลิตภัณฑ์'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ยอดขายผลิตภัณฑ์ชีวมวล " :value="number_format($bioproductSales, 0)" unit="บาท" icon="sell" tone="blue"
 sub="รายได้จากการขายในฤดูนี้" />
 </div>
 </div>

 <div class="row g-4 mb-4">
 <div class="col-lg-6">
 <x-dash.chart title="ผลผลิตจากเตา แยกตามผลิตภัณฑ์ " subtitle="ปริมาณรวมจากการเดินเตาที่อนุมัติแล้ว · หน่วยตามผลิตภัณฑ์" :chart="$outputChart" />
 </div>
 <div class="col-lg-6">
 <x-dash.chart title="สต็อกคงเหลือ แยกตามผลิตภัณฑ์" subtitle="ยอดล่าสุดของทุกครัวเรือนในขอบเขตรวมกัน · หน่วยตามผลิตภัณฑ์" :chart="$stockChart">
 <p class="text-xs text-secondary mt-2 mb-0">
 <a href="{{ route('inventory-transactions.index') }}">ดูบัญชีเคลื่อนไหวสต็อกทั้งหมด &rarr;</a>
 </p>
 </x-dash.chart>
 </div>
 </div>
</x-app-layout>
