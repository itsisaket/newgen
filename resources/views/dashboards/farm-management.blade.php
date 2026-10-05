<x-app-layout :title="'ระบบการจัดการสวน'">
 @include('dashboards.partials.header', [
 'heading' => 'ระบบการจัดการสวน',
 'subtitle' => 'พื้นที่ · จำนวนต้น · ผลผลิต/ไร่ · กิจกรรมสวน · การใช้ปัจจัยการผลิต',
 ])

 @php
 $inputSorted = collect($inputByCategory)->sortDesc();
 $inputChart = [
 'horizontal' => true,
 'unit' => 'บาท',
 'labels' => $inputSorted->keys()->map(fn ($key) => $categoryLabels[$key] ?? $key)->all(),
 'values' => $inputSorted->values()->all(),
 'empty' => 'ยังไม่มีกิจกรรมสวน ในฤดูนี้',
 'categoryLabel' => 'หมวด',
 ];
 @endphp

 <div class="row g-4 mb-4">
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="สวนทั้งหมด" :value="number_format($farmCount)" unit="สวน" icon="forest" tone="green"
 :sub="number_format($totalAreaRai, 1).' ไร่ · '.number_format($plotCount).' แปลง'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="จำนวนต้นทุเรียนรวม" :value="number_format($totalTreeCount)" unit="ต้น" icon="park" tone="green"
 :sub="$plotCount > 0 ? 'เฉลี่ย '.number_format($totalTreeCount / $plotCount, 0).' ต้น/แปลง' : null" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ผลผลิตต่อไร่ (อนุมัติแล้ว)" :value="$yieldPerRai !== null ? number_format($yieldPerRai, 1) : '-'" unit="กก./ไร่" icon="scale" tone="gold"
 :sub="'ผลผลิตรวม '.number_format($harvestWeightKg, 0).' กก.'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="กิจกรรมสวน " :value="number_format($activityCount)" unit="รายการ" icon="agriculture" tone="blue"
 :sub="'อนุมัติแล้ว '.number_format($activityStatusCounts['approved'] ?? 0).' รายการ'" />
 </div>
 </div>

 <div class="row g-4 mb-4">
 <div class="col-lg-7">
 <div class="viz-card">
 <div class="viz-card__head">
 <div>
 <h6 class="viz-card__title">แผนที่ตำแหน่งสวน</h6>
 <p class="viz-card__sub">
 {{ number_format($mapFarms->count()) }} จาก {{ number_format($farmCount) }} สวนที่บันทึกพิกัด GPS แล้ว · กดหมุดเพื่อดูชื่อสวนและพื้นที่
 </p>
 </div>
 </div>
 <div class="viz-card__body">
 @if ($mapFarms->isEmpty())
 <div class="viz-empty dash-map">ยังไม่มีสวนที่บันทึกพิกัด GPS ในขอบเขตนี้</div>
 @else
 <div id="farm_map" class="dash-map" role="region" aria-label="แผนที่ตำแหน่งสวน"></div>
 @endif
 </div>
 </div>
 </div>
 <div class="col-lg-5 d-flex flex-column gap-4">
 <div class="viz-card h-auto">
 <div class="viz-card__head">
 <div>
 <h6 class="viz-card__title">สถานะกิจกรรมสวน </h6>
 <p class="viz-card__sub">ร่าง → ส่งแล้ว → ตรวจสอบแล้ว → อนุมัติแล้ว</p>
 </div>
 </div>
 <div class="viz-card__body">
 <x-dash.status-bar type="workflow" :counts="$activityStatusCounts" />
 </div>
 </div>
 <x-dash.chart class="h-auto flex-grow-1" title="ต้นทุนปัจจัยการผลิตแยกตามหมวด" subtitle="บาท · จากกิจกรรมสวน ในฤดูนี้" :chart="$inputChart" />
 </div>
 </div>

 @if ($mapFarms->isNotEmpty())
 @push('scripts')
 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
 <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
 <script>
 document.addEventListener('DOMContentLoaded', function () {
 var farms = @json($mapFarms);
 var map = L.map('farm_map', { scrollWheelZoom: false });
 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
 attribution: '&copy; OpenStreetMap contributors',
 maxZoom: 19,
 }).addTo(map);
 L.control.scale({ imperial: false }).addTo(map);

 var bounds = [];
 farms.forEach(function (farm) {
 // Brand-green circle markers instead of Leaflet's
 // default blue pin; popup text built with
 // textContent (farm names are user-entered data).
 var marker = L.circleMarker([farm.lat, farm.lng], {
 radius: 7,
 weight: 2,
 color: '#ffffff',
 fillColor: '#2e7d32',
 fillOpacity: 1,
 }).addTo(map);
 var popup = document.createElement('div');
 var name = document.createElement('strong');
 name.textContent = farm.label;
 popup.appendChild(name);
 popup.appendChild(document.createElement('br'));
 popup.appendChild(document.createTextNode(farm.area_rai.toFixed(1) + ' ไร่'));
 marker.bindPopup(popup);
 bounds.push([farm.lat, farm.lng]);
 });

 // มุมกว้างเสมอ: จำกัด maxZoom ตอน fitBounds กันซูมเข้าใกล้
 // เกินไปเวลาสวนกระจุกอยู่ใกล้กัน และซูมเริ่มต้น 12 (ระดับ
 // ตำบล/อำเภอ) เมื่อมีสวนเดียว - พฤติกรรมเดิมก่อนรอบนี้
 if (bounds.length === 1) {
 map.setView(bounds[0], 12);
 } else {
 map.fitBounds(bounds, { padding: [40, 40], maxZoom: 13 });
 }
 });
 </script>
 @endpush
 @endif
</x-app-layout>
