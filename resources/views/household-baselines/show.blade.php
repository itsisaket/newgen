<x-app-layout :title="'ข้อมูลพื้นฐานครัวเรือน'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">ข้อมูลพื้นฐานครัวเรือน</h5>
 <p class="text-sm text-secondary mb-0">
 {{ $baseline->household->head_name }} ({{ $baseline->household->household_code }}) · {{ $baseline->cropSeason->name }}
 </p>
 </div>
 <x-status-badge :status="$baseline->status" />
 <x-revision-button :record="$baseline" type="household-baseline" />
 </div>

 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ต้นทุนรวม</p>
 <p class="text-sm font-weight-bold mb-0">{{ number_format($baseline->total_cost ?? 0, 2) }} บาท</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">รายได้รวม</p>
 <p class="text-sm font-weight-bold mb-0">{{ number_format($baseline->total_income ?? 0, 2) }} บาท</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">พื้นที่ปลูกช่วง Baseline</p>
 <p class="text-sm mb-0">{{ $baseline->baseline_area_rai !== null ? number_format($baseline->baseline_area_rai, 2).' ไร่' : '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ผลผลิตช่วง Baseline</p>
 <p class="text-sm mb-0">{{ $baseline->baseline_harvest_kg !== null ? number_format($baseline->baseline_harvest_kg, 2).' กก.' : '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ผู้บันทึก</p>
 <p class="text-sm mb-0">{{ $baseline->recordedBy->name }}</p>
 </div>
 <div class="col-12 mb-0">
 <p class="text-xs text-secondary mb-0">บันทึกการจัดการเดิม</p>
 <p class="text-sm mb-0">{{ $baseline->management_practice_notes ?: '-' }}</p>
 </div>
 @if ($baseline->rejection_reason)
 <div class="col-12 mt-3">
 <p class="text-xs text-danger mb-0">เหตุผลตีกลับล่าสุด</p>
 <p class="text-sm text-danger mb-0">{{ $baseline->rejection_reason }}</p>
 </div>
 @endif
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-body">
 <h6 class="text-sm mb-1">ปริมาณปัจจัยการผลิตช่วง Baseline (ต่อปี, ระดับครัวเรือน) — สำหรับ CFP</h6>
 <p class="text-xs text-secondary mb-3">
 ใช้เทียบกับปริมาณจริงในรอบปัจจุบันว่าปุ๋ย สารเคมี พลังงาน และการเผากิ่งลดลงเท่าใด (ต่อไร่ / ต่อ กก. ผลผลิต)
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ประเภท</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">รายการ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">ปริมาณ/ปี</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">หน่วย</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">ความชื้น (%)</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($baseline->inputs as $input)
 <tr>
 <td><span class="text-secondary text-xs ps-2">{{ $input->kindLabel() }}</span></td>
 <td class="ps-2"><span class="text-xs font-weight-bold">{{ $input->itemLabel() }}</span></td>
 <td class="text-end"><span class="text-secondary text-xs font-weight-bold">{{ number_format($input->quantity, 2) }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $input->unit }}</span></td>
 <td class="text-end"><span class="text-secondary text-xs">{{ $input->moisture_pct !== null ? number_format($input->moisture_pct, 2) : '-' }}</span></td>
 <td class="align-middle text-end">
 @if ($baseline->status === 'draft')
 @can('update', $baseline)
 <form method="POST" action="{{ route('household-baselines.inputs.destroy', [$baseline, $input]) }}" class="d-inline">
 @csrf
 @method('DELETE')
 <button class="btn btn-link text-danger text-xs p-0 mb-0">ลบ</button>
 </form>
 @endcan
 @endif
 </td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูลปริมาณปัจจัย</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if ($baseline->status === 'draft')
 @can('update', $baseline)
 @if ($errors->any())
 <div class="alert alert-danger text-white mt-3">
 <ul class="mb-0 ps-3">
 @foreach ($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif
 <form method="POST" action="{{ route('household-baselines.inputs.store', $baseline) }}" class="row g-2 align-items-end mt-2" id="baselineInputForm" novalidate>
 @csrf
 <div class="col-md-3 col-12">
 <label class="field-label">ประเภท</label>
 <select name="input_kind" id="inputKind" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach (\App\Models\HouseholdBaselineInput::KIND_LABELS as $value => $label)
 <option value="{{ $value }}" @selected(old('input_kind') === $value)>{{ $label }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-3 col-12" data-kind-group="fertilizer">
 <label class="field-label">ชนิดปุ๋ย</label>
 <select name="material_id" class="field-control" data-material-select disabled>
 <option value="">-- เลือก --</option>
 @foreach ($fertilizerMaterials as $m)
 <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->unit }})</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-3 col-12" data-kind-group="pesticide">
 <label class="field-label">ชนิดสารเคมี</label>
 <select name="material_id" class="field-control" data-material-select disabled>
 <option value="">-- เลือก --</option>
 @foreach ($chemicalMaterials as $m)
 <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->unit }})</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-3 col-12" data-kind-group="branch_route">
 <label class="field-label">เส้นทางจัดการกิ่ง</label>
 <select name="disposal_route" class="field-control" disabled>
 <option value="">-- เลือก --</option>
 @foreach (\App\Models\BranchDisposalRecord::ROUTE_LABELS as $value => $label)
 <option value="{{ $value }}">{{ $label }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-2 col-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ปริมาณ/ปี</label>
 <input type="number" step="0.0001" min="0" name="quantity" class="form-control" required value="{{ old('quantity') }}">
 </div>
 </div>
 <div class="col-md-2 col-6" data-kind-group="branch_route">
 <div class="input-group input-group-outline">
 <label class="form-label">ความชื้น (%)</label>
 <input type="number" step="0.01" min="0" max="100" name="moisture_pct" class="form-control" disabled>
 </div>
 </div>
 <div class="col-md-2 col-12">
 <button class="btn btn-outline-secondary mb-0 w-100">เพิ่มรายการ</button>
 </div>
 </form>
 <script>
 (function () {
 var kind = document.getElementById('inputKind');
 var form = document.getElementById('baselineInputForm');
 function sync() {
 form.querySelectorAll('[data-kind-group]').forEach(function (group) {
 var active = group.getAttribute('data-kind-group') === kind.value;
 group.style.display = active ? '' : 'none';
 group.querySelectorAll('select, input').forEach(function (el) { el.disabled = !active; });
 });
 }
 kind.addEventListener('change', sync);
 sync();
 })();
 </script>
 @endcan
 @endif
 </div>
 </div>

 <div class="d-flex gap-2 flex-wrap mb-3">
 @if ($baseline->status === 'draft')
 @can('update', $baseline)
 <a href="{{ route('household-baselines.edit', $baseline) }}" class="btn btn-outline-secondary btn-sm mb-0">แก้ไข</a>
 @endcan
 <form method="POST" action="{{ route('household-baselines.submit', $baseline) }}">
 @csrf
 <button class="btn bg-gradient-info btn-sm mb-0">ส่งข้อมูล (Submit)</button>
 </form>
 @endif

 @if ($baseline->status === 'submitted')
 <form method="POST" action="{{ route('household-baselines.verify', $baseline) }}">
 @csrf
 <button class="btn bg-gradient-info btn-sm mb-0">ตรวจสอบ (Verify)</button>
 </form>
 @endif

 @if ($baseline->status === 'verified')
 <form method="POST" action="{{ route('household-baselines.approve', $baseline) }}">
 @csrf
 <button class="btn bg-gradient-success btn-sm mb-0">อนุมัติ (Approve)</button>
 </form>
 @endif

 @if (in_array($baseline->status, ['submitted', 'verified']))
 <button type="button" class="btn btn-outline-danger btn-sm mb-0" data-bs-toggle="collapse" data-bs-target="#rejectForm">ตีกลับ (Reject)</button>
 @endif
 </div>

 @if (in_array($baseline->status, ['submitted', 'verified']))
 <div class="collapse mb-3" id="rejectForm">
 <div class="card col-lg-6 col-12">
 <div class="card-body">
 <form method="POST" action="{{ route('household-baselines.reject', $baseline) }}">
 @csrf
 <label class="field-label">เหตุผลที่ตีกลับ</label>
 <textarea name="rejection_reason" class="field-control mb-2" required></textarea>
 <button class="btn bg-gradient-danger btn-sm mb-0">ยืนยันตีกลับ</button>
 </form>
 </div>
 </div>
 </div>
 @endif

 <p><a href="{{ route('household-baselines.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
