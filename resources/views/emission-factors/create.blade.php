<x-app-layout :title="'เพิ่มค่าสัมประสิทธิ์การปล่อยก๊าซ'">
 <div class="row">
 <div class="col-12 col-lg-8">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">เพิ่มค่าสัมประสิทธิ์การปล่อยก๊าซเรือนกระจก</h6>
 </div>
 </div>
 <div class="card-body">
 @if ($errors->any())
 <div class="alert alert-danger text-white">
 <ul class="mb-0 ps-3">
 @foreach ($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <form method="POST" action="{{ route('emission-factors.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">หมวดหมู่กิจกรรม</label>
 <select name="category" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach (\App\Models\CarbonActivity::CATEGORY_LABELS as $value => $label)
 <option value="{{ $value }}" @selected(old('category') == $value)>{{ $label }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่เริ่มใช้ค่านี้</label>
 <input type="date" name="effective_from" class="form-control" required value="{{ old('effective_from', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ค่าสัมประสิทธิ์ (kgCO2e ต่อหน่วย)</label>
 <input type="number" step="0.000001" min="0" name="factor_value" class="form-control" required value="{{ old('factor_value') }}">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">หน่วย (เช่น kgCO2e/กก., kgCO2e/ต้น)</label>
 <input type="text" name="unit" class="form-control" required value="{{ old('unit') }}">
 </div>
 </div>
 <div class="col-12">
 <div class="input-group input-group-outline">
 <label class="form-label">แหล่งอ้างอิง (ไม่บังคับ, เช่น IPCC 2019 Guidelines)</label>
 <input type="text" name="source" class="form-control" value="{{ old('source') }}">
 </div>
 </div>
 </div>

 <p class="text-xs text-secondary mt-3">
 ถ้าหมวดหมู่นี้มีค่าปัจจุบันอยู่แล้ว ระบบจะปิดค่าเดิม (ตั้งวันสิ้นสุด) ให้อัตโนมัติ
 แล้วใช้ค่าใหม่นี้แทนตั้งแต่วันที่ระบุเป็นต้นไป — ค่าเดิมจะยังถูกเก็บไว้เพื่ออ้างอิงการคำนวณเก่า
 </p>

 <div class="mt-3">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('emission-factors.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
