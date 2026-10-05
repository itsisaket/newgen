<x-app-layout :title="'บันทึกการจัดการกิ่ง'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึกการจัดการกิ่ง/เศษไม้ (CFP)</h6>
 </div>
 </div>
 <div class="card-body">
 <p class="text-xs text-secondary">
 บันทึกว่ากิ่งที่ตัดแต่งไปทางไหน (เข้าเตา เผา กองทิ้ง ทำปุ๋ยหมัก) — ปริมาณกิ่งที่ตัดทั้งหมดยังบันทึกที่กิจกรรม "ตัดแต่งกิ่ง" ระบบจะตรวจสมดุลให้
 </p>

 @if ($errors->any())
 <div class="alert alert-danger text-white">
 <ul class="mb-0 ps-3">
 @foreach ($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <form method="POST" action="{{ route('branch-disposal-records.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6 col-lg-4">
 <label class="field-label">แปลง (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $plot->plot_code }} ({{ $plot->farm->household->head_name }})" readonly disabled>
 <input type="hidden" name="plot_id" value="{{ $plot->id }}">
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่จัดการกิ่ง</label>
 <input type="date" name="disposal_date" class="form-control" required value="{{ old('disposal_date', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">เส้นทางจัดการกิ่ง</label>
 <select name="disposal_route" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach (\App\Models\BranchDisposalRecord::ROUTE_LABELS as $value => $label)
 <option value="{{ $value }}" @selected(old('disposal_route') === $value)>{{ $label }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">น้ำหนักสด (กก.)</label>
 <input type="number" step="0.01" min="0" name="quantity_kg" class="form-control" required value="{{ old('quantity_kg') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ความชื้น (%) — ถ้าวัดได้</label>
 <input type="number" step="0.01" min="0" max="100" name="moisture_pct" class="form-control" value="{{ old('moisture_pct') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">รอบเดินเตา (เฉพาะเส้นทาง "เข้าเตา")</label>
 <select name="kiln_batch_id" class="field-control">
 <option value="">-- ไม่ระบุ --</option>
 @foreach ($kilnBatches as $batch)
 <option value="{{ $batch->id }}" @selected(old('kiln_batch_id') == $batch->id)>
 {{ $batch->batch_code }} — {{ $batch->batch_date->format('d/m/Y') }} ({{ number_format($batch->biomass_input_kg, 2) }} กก.)
 </option>
 @endforeach
 </select>
 </div>
 <div class="col-12">
 <div class="input-group input-group-outline">
 <label class="form-label">หมายเหตุ</label>
 <input type="text" name="note" class="form-control" maxlength="1000" value="{{ old('note') }}">
 </div>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึกร่าง</button>
 <a href="{{ route('plots.show', $plot) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
