<x-app-layout :title="'ระบบภาพรวมโครงการ'">
 {{--
 Public overview (DashboardController - no login required, aggregate
 counts only, never a household name - see that controller's
 doc-comment). 23 ก.ย. round: this is now the "ภาพรวม" tab of the
 merged Dashboard (dashboards/partials/header.blade.php) and uses the
 shared KPI/chart/status-bar kit instead of plain tables.
 --}}
 @include('dashboards.partials.header', [
 'heading' => 'ภาพรวมโครงการ',
 'subtitle' => 'ข้อมูลสรุปทั้งระบบ DRFIS · เปิดดูได้โดยไม่ต้องเข้าสู่ระบบ',
 ])

 <section class="orchard-welcome" aria-labelledby="orchard-welcome-title">
 <img src="{{ asset('images/durian-smart-orchard-hero.webp') }}"
 alt="เกษตรกรและเจ้าหน้าที่กำลังตรวจทุเรียน ระบบน้ำ และข้อมูลสวนผ่านแท็บเล็ต"
 width="1600" height="900" fetchpriority="high">
 <div class="orchard-welcome__shade"></div>
 <div class="orchard-welcome__content">
 <span class="orchard-welcome__eyebrow"><i class="material-symbols-rounded" aria-hidden="true">location_on</i> จังหวัดศรีสะเกษ</span>
 <h2 id="orchard-welcome-title">จัดการสวนทุเรียน<br>ด้วยข้อมูลที่เข้าใจง่าย</h2>
 <p>เชื่อมข้อมูลครัวเรือน สวน ผลผลิต ต้นทุน และคาร์บอน เพื่อช่วยตัดสินใจได้ดีขึ้นในทุกฤดูผลิต</p>
 <div class="orchard-welcome__areas" aria-label="พื้นที่โครงการ 3 อำเภอ">
 <span>ขุนหาญ</span><span>กันทรลักษ์</span><span>ศรีรัตนะ</span>
 </div>
 </div>
 </section>

 @auth
 @if (auth()->user()->hasAnyRole(App\Models\Role::OWN_HOUSEHOLD) && auth()->user()->household)
 {{-- 24 ก.ย.: ปุ่มใหญ่ทางลัดสำหรับเกษตรกร - งานที่ทำบ่อยที่สุด --}}
 <div class="card mb-4">
 <div class="card-body">
 <h6 class="mb-1">วันนี้จะทำอะไร?</h6>
 <p class="text-xs text-secondary mb-3">เลือกแปลงจากหน้า "บ้านและสวนของฉัน" แล้วกดปุ่ม + เพื่อบันทึก รายการที่ส่งแล้วจะรอเจ้าหน้าที่ตรวจและอนุมัติ</p>
 <div class="row g-3">
 <div class="col-6 col-md-3"><a class="btn bg-gradient-success w-100 py-3 mb-0" href="{{ route('households.show', auth()->user()->household) }}"><i class="material-symbols-rounded d-block">home</i>บ้านและสวนของฉัน</a></div>
 <div class="col-6 col-md-3"><a class="btn bg-gradient-dark w-100 py-3 mb-0" href="{{ route('farm-activities.index') }}"><i class="material-symbols-rounded d-block">agriculture</i>บันทึกงานในสวน</a></div>
 <div class="col-6 col-md-3"><a class="btn bg-gradient-dark w-100 py-3 mb-0" href="{{ route('harvest-records.index') }}"><i class="material-symbols-rounded d-block">scale</i>ผลผลิตที่เก็บ</a></div>
 <div class="col-6 col-md-3"><a class="btn bg-gradient-dark w-100 py-3 mb-0" href="{{ route('cfp.index') }}"><i class="material-symbols-rounded d-block">eco</i>ผลคาร์บอนของทุเรียน</a></div>
 </div>
 </div>
 </div>
 @endif
 @endauth

 @guest
 <p class="text-sm text-secondary mt-n2 mb-4">
 <a href="{{ route('login') }}">เข้าสู่ระบบ</a> เพื่อจัดการข้อมูลรายครัวเรือนและใช้งานระบบวิเคราะห์ด้านอื่น
 </p>
 @endguest

 @php
 $byDistrictByArea = $byDistrict->sortByDesc('area_rai');
 $districtChart = [
 'horizontal' => true,
 'unit' => 'ไร่',
 'decimals' => 1,
 'labels' => $byDistrictByArea->keys()->all(),
 'values' => $byDistrictByArea->pluck('area_rai')->values()->all(),
 'table' => [
 'head' => ['อำเภอ', 'ครัวเรือน', 'สวน', 'พื้นที่ (ไร่)'],
 'rows' => $byDistrictByArea->map(fn ($row, $name) => [$name, $row['households'], $row['farms'], $row['area_rai']])->values()->all(),
 ],
 ];
 $varietyChart = [
 'horizontal' => true,
 'unit' => 'แปลง',
 'labels' => $byVariety->keys()->all(),
 'values' => $byVariety->pluck('plots')->values()->all(),
 'table' => [
 'head' => ['พันธุ์', 'แปลง', 'พื้นที่ (ไร่)', 'จำนวนต้น'],
 'rows' => $byVariety->map(fn ($row, $name) => [$name, $row['plots'], $row['area_rai'], $row['tree_count']])->values()->all(),
 ],
 ];
 $tambonByArea = $byTambon->sortByDesc('area_rai');
 $maxTambonArea = max(1, (float) $byTambon->max('area_rai'));
 $innovatorPct = $farmerMemberCount > 0 ? round($innovatorCount / $farmerMemberCount * 100) : 0;
 @endphp

 <div class="row g-4 mb-4">
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="เกษตรกรสมาชิก" :value="number_format($farmerMemberCount)" unit="ครัวเรือน"
 icon="home_work" tone="green" :sub="'เป็นนวัตกรชุมชนแล้ว '.number_format($innovatorCount).' คน'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="สวนทั้งหมด" :value="number_format($farmCount)" unit="สวน"
 icon="forest" tone="green" :sub="'พื้นที่รวม '.number_format($totalFarmAreaRai, 1).' ไร่'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="แปลงทั้งหมด" :value="number_format($plotCount)" unit="แปลง"
 icon="grid_view" tone="gold" :sub="number_format($totalPlotAreaRai, 1).' ไร่ · '.number_format($totalTreeCount).' ต้น'" />
 </div>
 <div class="col-6 col-xl-3">
 <x-dash.kpi label="ฤดูผลิตปัจจุบัน" :value="optional($activeCropSeason)->name ?? '-'"
 icon="event" tone="blue" :sub="number_format($farmerGroupCount).' กลุ่มเกษตรกร'" />
 </div>
 </div>

 <div class="row g-4 mb-4">
 <div class="col-lg-5">
 <div class="viz-card">
 <div class="viz-card__head">
 <div>
 <h6 class="viz-card__title">สถานะครัวเรือน</h6>
 <p class="viz-card__sub">สัดส่วนครัวเรือนที่ยังร่วมโครงการ</p>
 </div>
 </div>
 <div class="viz-card__body">
 <x-dash.status-bar type="household" :counts="$householdStatusCounts" :title="'ครัวเรือนทั้งหมด'" />
 <div class="status-block">
 <div class="status-block__head">
 <span>เป็นนวัตกรชุมชนแล้ว</span>
 <small>{{ number_format($innovatorCount) }} จาก {{ number_format($farmerMemberCount) }} ครัวเรือน</small>
 </div>
 <div class="kpi-meter" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $innovatorPct }}" aria-label="สัดส่วนนวัตกรชุมชน">
 <span style="width: {{ $innovatorPct }}%"></span>
 </div>
 <div class="status-legend">
 <span class="status-legend__item">ผ่านการประเมินนวัตกรชุมชน <strong>{{ $innovatorPct }}%</strong></span>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="col-lg-7">
 <div class="viz-card">
 <div class="viz-card__head">
 <div>
 <h6 class="viz-card__title">ความคืบหน้าการส่งข้อมูล</h6>
 <p class="viz-card__sub">ร่าง → ส่งแล้ว → ตรวจสอบแล้ว → อนุมัติแล้ว (สีเข้มขึ้น = ไปไกลขึ้นในขั้นตอน)</p>
 </div>
 </div>
 <div class="viz-card__body">
 <x-dash.status-bar type="workflow" :counts="$baselineStatusCounts" title="ข้อมูลพื้นฐานครัวเรือน" />
 <x-dash.status-bar type="workflow" :counts="$activityStatusCounts" title="กิจกรรมในสวน" />
 </div>
 </div>
 </div>
 </div>

 <div class="row g-4 mb-4">
 <div class="col-lg-6">
 <x-dash.chart title="พื้นที่สวนตามอำเภอ" subtitle="ไร่ · เรียงจากมากไปน้อย (จ.ศรีสะเกษ)" :chart="$districtChart" />
 </div>
 <div class="col-lg-6">
 <x-dash.chart title="จำนวนแปลงตามพันธุ์ทุเรียน" subtitle="แปลง · กดปุ่มตารางเพื่อดูพื้นที่และจำนวนต้น" :chart="$varietyChart" />
 </div>
 </div>

 <div class="row g-4 mb-4">
 <div class="col-12">
 <div class="viz-card">
 <div class="viz-card__head">
 <div>
 <h6 class="viz-card__title">ข้อมูลตามตำบล</h6>
 <p class="viz-card__sub">เรียงตามพื้นที่ · แถบสีหลังตัวเลขแสดงขนาดเทียบกับตำบลที่มากที่สุด</p>
 </div>
 </div>
 <div class="viz-card__body viz-card__body--flush">
 <div class="viz-table-scroll">
 <table class="viz-table">
 <thead>
 <tr>
 <th class="ps-4">ตำบล</th>
 <th class="num">ครัวเรือน</th>
 <th class="num">สวน</th>
 <th class="num pe-4">พื้นที่ (ไร่)</th>
 </tr>
 </thead>
 <tbody>
 @forelse ($tambonByArea as $tambon => $row)
 <tr>
 <td class="ps-4">{{ $tambon }}</td>
 <td class="num">{{ number_format($row['households']) }}</td>
 <td class="num">{{ number_format($row['farms']) }}</td>
 <td class="num pe-4">
 <div class="data-bar">
 <span style="width: {{ round($row['area_rai'] / $maxTambonArea * 100, 1) }}%"></span>
 <em>{{ number_format($row['area_rai'], 1) }}</em>
 </div>
 </td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>

 <p class="text-secondary text-xs mb-4">
 ตัวเลขทั้งหมดในหน้านี้เป็นข้อมูลสรุป (จำนวน/ผลรวม) เท่านั้น ไม่มีการแสดงชื่อหรือข้อมูลส่วนบุคคลของครัวเรือนรายบุคคล
 — ต้องเข้าสู่ระบบเพื่อดูข้อมูลรายละเอียด
 </p>
</x-app-layout>
