<x-app-layout :title="'ครัวเรือน'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $household->head_name }}</h5>
 <p class="text-sm text-secondary mb-0">
 รหัส {{ $household->household_code }}
 · {{ $household->farmerGroup->name ?? 'ไม่ระบุกลุ่มเกษตรกร' }}
 @if ($household->village)
 · {{ $household->village->name_th }}
 @endif
 @if ($household->farmerGroup?->tambon?->district?->province)
 · {{ $household->farmerGroup->tambon->district->province->name_th }}
 @endif
 </p>
 </div>
 <div class="d-flex align-items-center gap-2">
 <x-status-badge :status="$household->status" />
 <a href="{{ route('households.production-cost', $household) }}" class="btn btn-outline-dark btn-sm mb-0">ต้นทุนการผลิต </a>
 <a href="{{ route('households.economic-impact', $household) }}" class="btn btn-outline-dark btn-sm mb-0">ผลกระทบเศรษฐกิจ </a>
 @can('update', $household)
 <a href="{{ route('households.edit', $household) }}" class="btn btn-outline-secondary btn-sm mb-0">แก้ไข</a>
 @endcan
 </div>
 </div>

 {{-- 23 ก.ย. round: หน้าครัวเรือนจัดเป็น 5 ส่วนตามลำดับที่ผู้ใช้กำหนด
 (1 ครัวเรือน/สวน/แปลง -> 2 ยอดขาย -> 3 เทคโนโลยี ชีวมวล สต็อก ->
 4 การประเมิน -> 5 บัญชีผู้ใช้เกษตรกร) - แถบลิงก์นี้ใช้กระโดดไปแต่ละ
 ส่วนได้ทันทีเพราะหน้านี้ยาว. คาร์บอน (ไม่ได้อยู่ในรายการที่ผู้ใช้
 กำหนด) วางไว้ท้ายส่วนที่ 3 เพราะเป็นงานสิ่งแวดล้อมที่ต่อเนื่องกับ
 ชีวมวล/ไบโอชาร์. ปุ่ม "+ เพิ่ม..." ทุกปุ่มแสดงเฉพาะผู้ที่ Policy อนุญาต
 ให้สร้างจริงเท่านั้น (เดิมแสดงทุกคน กดแล้วเจอ 403). --}}
 <div class="d-flex flex-wrap gap-2 mb-3">
 <a href="#section-1" class="btn btn-sm btn-outline-secondary mb-0">1 ครัวเรือน สวน แปลง</a>
 <a href="#section-2" class="btn btn-sm btn-outline-secondary mb-0">2 ยอดขาย</a>
 <a href="#section-3" class="btn btn-sm btn-outline-secondary mb-0">3 เทคโนโลยี ชีวมวล สต็อก</a>
 <a href="#section-4" class="btn btn-sm btn-outline-secondary mb-0">4 การประเมิน</a>
 <a href="#section-5" class="btn btn-sm btn-outline-secondary mb-0">5 บัญชีผู้ใช้เกษตรกร</a>
 </div>

 <h6 id="section-1" class="text-uppercase text-xs text-dark font-weight-bolder opacity-7 mt-2 mb-3 ps-1" style="scroll-margin-top: 90px;">1 · ข้อมูลครัวเรือน สวน และแปลง</h6>
 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">เบอร์โทร</p>
 <p class="text-sm mb-0">{{ $household->phone ?? '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">วันที่ลงทะเบียน</p>
 <p class="text-sm mb-0">{{ $household->registered_at?->format('d/m/Y') ?? '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">จำนวนสวน</p>
 <p class="text-sm font-weight-bold mb-0">{{ $household->farms->count() }}</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">จำนวนแปลงรวม</p>
 <p class="text-sm font-weight-bold mb-0">{{ $household->farms->sum(fn ($farm) => $farm->plots->count()) }}</p>
 </div>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">สวนของครัวเรือนนี้</h6>
 @can('create', [App\Models\Farm::class, $household])
 <a href="{{ route('farms.create', ['household_id' => $household->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มสวน</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">รหัสสวน / ชื่อสวน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">พื้นที่ (ไร่)</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">จำนวนแปลง</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($household->farms as $farm)
 <tr>
 <td>
 <div class="d-flex px-2 py-1">
 <div class="d-flex flex-column justify-content-center">
 <h6 class="mb-0 text-sm">{{ $farm->farm_name ?? $farm->farm_code }}</h6>
 <p class="text-xs text-secondary mb-0">{{ $farm->farm_code }}</p>
 </div>
 </div>
 </td>
 <td class="text-end">
 <span class="text-secondary text-xs font-weight-bold">{{ $farm->total_area_rai ?? '-' }}</span>
 </td>
 <td class="align-middle text-center">
 <span class="text-secondary text-xs font-weight-bold">{{ $farm->plots->count() }}</span>
 </td>
 <td class="align-middle text-center text-sm">
 <x-status-badge :status="$farm->status" />
 </td>
 <td class="align-middle text-end">
 <a href="{{ route('farms.show', $farm) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มีสวน</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 {{--
 15 ก.ย. round 3: เมนู "สวน"/"แปลง" ถูกเอาออกจาก sidenav แล้ว (ดู
 layouts/app.blade.php) - การจัดการทุกอย่างตอนนี้เข้าผ่านหน้า
 ครัวเรือนเป็นจุดเริ่มต้นเดียว จึงเพิ่มตารางสรุป "แปลงทั้งหมด" ไว้ที่นี่
 ด้วย (แบน รวมทุกสวน ไม่ต้องไล่เปิดทีละสวนเพื่อดูแปลง) ต่อจากตารางสวน
 ด้านบน - ใช้ $household->farms ที่ eager-load 'plots' มาแล้วจาก
 HouseholdController::show() อยู่แล้ว ไม่ต้อง query เพิ่ม
 --}}
 @php($plots = $household->farms->flatMap->plots)
 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">แปลงทั้งหมดของครัวเรือนนี้</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">รหัสแปลง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">สวน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">พันธุ์</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">พื้นที่ (ไร่)</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">จำนวนต้น</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($plots as $plot)
 <tr>
 <td>
 <h6 class="mb-0 text-sm px-2 py-1">{{ $plot->plot_code }}</h6>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs">{{ $plot->farm->farm_name ?? $plot->farm->farm_code }}</span>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs">{{ $plot->durianVariety->name ?? '-' }}</span>
 </td>
 <td class="text-end">
 <span class="text-secondary text-xs font-weight-bold">{{ $plot->area_rai ?? '-' }}</span>
 </td>
 <td class="text-end">
 <span class="text-secondary text-xs font-weight-bold">{{ $plot->tree_count ?? '-' }}</span>
 </td>
 <td class="align-middle text-center text-sm">
 <x-status-badge :status="$plot->status" />
 </td>
 <td class="align-middle text-end">
 <a href="{{ route('plots.show', $plot) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="7" class="text-center text-secondary text-sm py-4">ยังไม่มีแปลง</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 @if ($household->farms->isNotEmpty())
 <p class="text-xs text-secondary px-3 pt-2 mb-0">
 เพิ่มแปลงใหม่ได้จากหน้ารายละเอียดของสวนนั้น ๆ (เปิดสวนด้านบน → กด
 "+ เพิ่มแปลง")
 </p>
 @endif
 </div>
 </div>

 <h6 id="section-2" class="text-uppercase text-xs text-dark font-weight-bolder opacity-7 mt-5 mb-5 ps-1" style="scroll-margin-top: 90px;">2 · ยอดขาย</h6>
 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ยอดขาย (Sales)</h6>
 @can('create', [App\Models\Sale::class, $household])
 <a href="{{ route('sales.create', ['household_id' => $household->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ บันทึกการขาย</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ผู้ซื้อ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ประเภทสินค้า</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">มูลค่า (บาท)</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($household->sales as $sale)
 <tr>
 <td><span class="text-xs px-2">{{ $sale->sale_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $sale->buyer->name ?? '-' }}</span></td>
 <td><span class="text-secondary text-xs">{{ $sale->product_type === 'durian' ? 'ทุเรียน' : ($sale->product->name ?? '-') }}</span></td>
 <td class="text-end"><span class="text-sm font-weight-bold">{{ number_format($sale->total_amount, 2) }}</span></td>
 <td class="align-middle text-end"><a href="{{ route('sales.show', $sale) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a></td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มียอดขาย</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <h6 id="section-3" class="text-uppercase text-xs text-dark font-weight-bolder opacity-7 mt-5 mb-5 ps-1" style="scroll-margin-top: 90px;">3 · เทคโนโลยี ชีวมวล และสต็อก</h6>
 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">เทคโนโลยี ชีวมวล และสต็อก </h6>
 <div class="d-flex gap-2">
 @can('create', [App\Models\KilnBatch::class, $household])
 <a href="{{ route('kiln-batches.create', ['household_id' => $household->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ บันทึกการเดินเตา</a>
 @endcan
 @can('create', [App\Models\InventoryTransaction::class, $household])
 <a href="{{ route('inventory-transactions.create', ['household_id' => $household->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ บันทึกการเคลื่อนไหวสต็อก</a>
 @endcan
 </div>
 </div>
 </div>
 <div class="card-body">
 <p class="text-xs text-secondary text-uppercase font-weight-bolder mb-2">เครื่องเทคโนโลยีที่ได้รับจัดสรรอยู่ </p>
 @forelse ($household->technologyAssignments as $assignment)
 <p class="text-sm mb-1">
 <a href="{{ route('technology-assets.show', $assignment->technologyAsset) }}">{{ $assignment->technologyAsset->asset_code }}</a>
 — {{ $assignment->technologyAsset->technology->name }}
 <span class="text-xs text-secondary">(จัดสรรตั้งแต่ {{ $assignment->assigned_date->format('d/m/Y') }})</span>
 </p>
 @empty
 <p class="text-sm text-secondary mb-0">ยังไม่มีเครื่องเทคโนโลยีที่ได้รับจัดสรร</p>
 @endforelse

 <hr class="horizontal dark my-3">

 <p class="text-xs text-secondary text-uppercase font-weight-bolder mb-2">การเดินเตาล่าสุด </p>
 @forelse ($household->kilnBatches as $batch)
 <p class="text-sm mb-1 d-flex justify-content-between">
 <a href="{{ route('kiln-batches.show', $batch) }}">{{ $batch->batch_code }} — {{ $batch->batch_date->format('d/m/Y') }}</a>
 <x-status-badge :status="$batch->status" />
 </p>
 @empty
 <p class="text-sm text-secondary mb-0">ยังไม่มีการเดินเตา</p>
 @endforelse

 <hr class="horizontal dark my-3">

 <p class="text-xs text-secondary text-uppercase font-weight-bolder mb-2">สต็อกผลิตภัณฑ์ชีวมวลคงเหลือ (Inventory Ledger)</p>
 @forelse ($productBalances as $balance)
 <p class="text-sm mb-1">
 {{ $balance->product->name }}: <strong>{{ number_format($balance->balance_after, 2) }} {{ $balance->product->unit }}</strong>
 </p>
 @empty
 <p class="text-sm text-secondary mb-0">ยังไม่มีสต็อกผลิตภัณฑ์ชีวมวล</p>
 @endforelse
 <a href="{{ route('inventory-transactions.index') }}" class="text-secondary text-xs">ดูบัญชีเคลื่อนไหวสต็อกทั้งหมด &rarr;</a>
 </div>
 </div>

 @can('viewAny', App\Models\CarbonActivity::class)
 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">กิจกรรมลดคาร์บอน </h6>
 @can('create', [App\Models\CarbonActivity::class, $household])
 <a href="{{ route('carbon-activities.create', ['household_id' => $household->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ บันทึกกิจกรรมคาร์บอน</a>
 @endcan
 </div>
 </div>
 <div class="card-body">
 @forelse ($household->carbonActivities as $activity)
 <p class="text-sm mb-1 d-flex justify-content-between">
 <a href="{{ route('carbon-activities.show', $activity) }}">
 {{ $activity->categoryLabel() }} — {{ $activity->activity_date->format('d/m/Y') }}
 @if ($activity->calculation)
 <span class="text-secondary text-xs">({{ number_format($activity->calculation->co2e_kg, 2) }} kgCO2e)</span>
 @endif
 </a>
 <x-status-badge :status="$activity->status" />
 </p>
 @empty
 <p class="text-sm text-secondary mb-0">ยังไม่มีกิจกรรมลดคาร์บอน</p>
 @endforelse
 </div>
 </div>
 @endcan

 <h6 id="section-4" class="text-uppercase text-xs text-dark font-weight-bolder opacity-7 mt-5 mb-5 ps-1" style="scroll-margin-top: 90px;">4 · การประเมิน (ระดับการยอมรับเทคโนโลยี และนวัตกรชุมชน)</h6>
 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">การประเมินการยอมรับเทคโนโลยี (ALP) (ระดับการยอมรับเทคโนโลยี)</h6>
 @can('create', [App\Models\AlpAssessment::class, $household])
 <a href="{{ route('alp-assessments.create', ['household_id' => $household->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มการประเมิน ALP</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">เทคโนโลยี</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ระดับ ALP</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($household->alpAssessments as $alp)
 <tr>
 <td><span class="text-xs px-2">{{ $alp->assessment_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $alp->technology->name }}</span></td>
 <td class="text-center"><span class="badge badge-sm bg-gradient-info">{{ $alp->alp_level }}</span></td>
 <td class="align-middle text-end"><a href="{{ route('alp-assessments.show', $alp) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a></td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีประวัติการประเมิน ALP</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ประวัติการประเมินนวัตกรชุมชน</h6>
 @can('create', [App\Models\InnovatorEvaluation::class, $household])
 <a href="{{ route('innovator-evaluations.create', ['household_id' => $household->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มการประเมินนวัตกรชุมชน</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่ประเมิน</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">คะแนน</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ผลการประเมิน</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($household->innovatorEvaluations as $evaluation)
 <tr>
 <td><span class="text-xs px-2">{{ $evaluation->evaluation_date->format('d/m/Y') }}</span></td>
 <td class="text-center"><span class="text-sm font-weight-bold">{{ $evaluation->score ?? '-' }}</span></td>
 <td class="text-center"><x-status-badge :status="$evaluation->result" /></td>
 <td class="align-middle text-end"><a href="{{ route('innovator-evaluations.show', $evaluation) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a></td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีประวัติการประเมินนวัตกรชุมชน</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <h6 id="section-5" class="text-uppercase text-xs text-dark font-weight-bolder opacity-7 mt-5 mb-5 ps-1" style="scroll-margin-top: 90px;">5 · บัญชีผู้ใช้เกษตรกร</h6>
 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">บัญชีผู้ใช้เกษตรกร (Farmer Login)</h6>
 @if ($household->user && auth()->user()->can('update', $household))
 <form method="POST" action="{{ route('households.reset-farmer-password', $household) }}">
 @csrf
 <button class="btn btn-sm bg-white text-dark mb-0">รีเซ็ตรหัสผ่าน</button>
 </form>
 @endif
 </div>
 </div>
 <div class="card-body">
 @if ($household->user)
 <div class="row">
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">อีเมล (login)</p>
 <p class="text-sm mb-0">{{ $household->user->email }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">สถานะบัญชี</p>
 <p class="text-sm mb-0">{{ $household->user->status === 'active' ? 'ใช้งานได้' : 'ระงับการใช้งาน' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">บทบาท</p>
 <p class="text-sm font-weight-bold mb-0">{{ $household->user->isInnovator() ? 'นวัตกรชุมชน' : 'เกษตรกร' }}</p>
 </div>
 </div>
 @else
 <p class="text-sm text-secondary mb-0">
 ครัวเรือนนี้ยังไม่มีบัญชีเกษตรกร (อาจลงทะเบียนไว้ก่อนฟีเจอร์นี้ - รัน
 <code>php artisan db:seed</code> เพื่อสร้างให้อัตโนมัติ)
 </p>
 @endif
 </div>
 </div>

 <p><a href="{{ route('households.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
