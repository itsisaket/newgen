<x-app-layout :title="'บันทึกกิจกรรมสวน'">
 <div class="row">
 <div class="col-12">
 <p class="text-danger small mb-2" id="offline-indicator"></p>

 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึกกิจกรรมในสวน</h6>
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

 <form method="POST" action="{{ route('farm-activities.store') }}" id="farm-activity-form" novalidate>
 @csrf
 <input type="hidden" name="client_uuid" id="client_uuid" value="{{ old('client_uuid') }}">

 <div class="row g-3">
 <div class="col-md-6 col-lg-4">
 {{-- 15 ก.ย. round 2: ไม่มี dropdown เลือกแปลงอิสระอีกต่อไป - กิจกรรม
 ต้องบันทึกจากหน้าแปลงนั้นเท่านั้น (plots/show -> "+ บันทึกกิจกรรม"
 หรือขั้นตอนต่อเนื่องหลังลงทะเบียนแปลงใหม่) แปลงที่นี่จึงเป็นค่าที่
 ล็อกไว้แล้ว แสดงเป็น read-only ดู FarmActivityController::create() --}}
 <label class="field-label">แปลง (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $plot->plot_code }} ({{ $plot->farm->household->head_name }})" readonly disabled>
 <input type="hidden" name="plot_id" value="{{ $plot->id }}">
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ฤดูผลิต</label>
 <select name="crop_season_id" class="field-control" required data-draft>
 <option value="">-- เลือก --</option>
 @foreach ($cropSeasons as $season)
 <option value="{{ $season->id }}" @selected(old('crop_season_id') == $season->id)>{{ $season->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">วันที่</label>
 <input type="date" name="activity_date" class="field-control" required data-draft value="{{ old('activity_date', now()->toDateString()) }}">
 </div>

 <div class="col-md-6 col-lg-4">
 <label class="field-label">ประเภทกิจกรรม</label>
 <select name="activity_type_id" class="field-control" required data-draft>
 <option value="">-- เลือก --</option>
 @foreach ($activityTypes as $type)
 <option value="{{ $type->id }}" @selected(old('activity_type_id') == $type->id)>[{{ $type->category }}] {{ $type->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ปัจจัยการผลิต (ถ้ามี)</label>
 <select name="material_id" class="field-control" data-draft>
 <option value="">-- ไม่ระบุ --</option>
 @foreach ($materials as $material)
 <option value="{{ $material->id }}" @selected(old('material_id') == $material->id)>{{ $material->name }} ({{ $material->unit }})</option>
 @endforeach
 </select>
 </div>
 </div>

 <hr class="horizontal dark my-4">

 <div class="row g-3">
 <div class="col-6 col-lg-3">
 <div class="input-group input-group-outline">
 <label class="form-label">ปริมาณ</label>
 <input type="number" step="0.01" min="0" name="quantity" class="form-control" data-draft value="{{ old('quantity') }}">
 </div>
 </div>
 <div class="col-6 col-lg-3">
 <div class="input-group input-group-outline">
 <label class="form-label">ราคา/หน่วย</label>
 <input type="number" step="0.01" min="0" name="unit_cost" class="form-control" data-draft value="{{ old('unit_cost') }}">
 </div>
 </div>
 <div class="col-6 col-lg-3">
 <div class="input-group input-group-outline">
 <label class="form-label">ชั่วโมงแรงงาน</label>
 <input type="number" step="0.01" min="0" name="labor_hours" class="form-control" data-draft value="{{ old('labor_hours') }}">
 </div>
 </div>
 <div class="col-6 col-lg-3">
 <div class="input-group input-group-outline">
 <label class="form-label">ค่าแรง (บาท)</label>
 <input type="number" step="0.01" min="0" name="labor_cost" class="form-control" data-draft value="{{ old('labor_cost') }}">
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

 @push('scripts')
 <script src="{{ asset('js/app.js') }}" defer></script>
 @endpush
</x-app-layout>
