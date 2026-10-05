<x-app-layout :title="'บันทึกกิจกรรมคาร์บอน'">
 <div class="row">
 <div class="col-12 col-lg-8">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึก กิจกรรมลดคาร์บอน</h6>
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

 <form method="POST" action="{{ route('carbon-activities.store') }}" novalidate>
 @csrf
 <input type="hidden" name="household_id" value="{{ $household->id }}">
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">ครัวเรือน (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $household->head_name }} ({{ $household->household_code }})" readonly disabled>
 </div>
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
 <label class="form-label">วันที่ทำกิจกรรม</label>
 <input type="date" name="activity_date" class="form-control" required value="{{ old('activity_date', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-outline">
 <label class="form-label">ปริมาณ</label>
 <input type="number" step="0.0001" min="0" name="quantity" class="form-control" required value="{{ old('quantity') }}">
 </div>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-outline">
 <label class="form-label">หน่วย (เช่น กก., ต้น, ไร่)</label>
 <input type="text" name="unit" class="form-control" required value="{{ old('unit') }}">
 </div>
 </div>
 <div class="col-12">
 <div class="input-group input-group-outline">
 <label class="form-label">รายละเอียดเพิ่มเติม (ไม่บังคับ)</label>
 <input type="text" name="description" class="form-control" value="{{ old('description') }}">
 </div>
 </div>
 </div>

 <p class="text-xs text-secondary mt-3">
 บันทึกนี้จะเริ่มที่สถานะ "ร่าง" — ปริมาณคาร์บอนเทียบเท่า (CO2e) จะคำนวณให้อัตโนมัติหลังจากข้อมูลผ่านการอนุมัติแล้วเท่านั้น
 </p>

 <div class="mt-3">
 <button class="btn bg-gradient-success mb-0">บันทึกร่าง</button>
 <a href="{{ route('households.show', $household) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
