<x-app-layout :title="'เพิ่มสวน'">
 <div class="row">
 <div class="col-12">
 <x-wizard-steps :current="2" />

 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">เพิ่มสวนใหม่</h6>
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

 <form method="POST" action="{{ route('farms.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6">
 {{-- 15 ก.ย. round 2: ไม่มี dropdown เลือกครัวเรือนอิสระอีกต่อไป -
 สวนต้องเพิ่มจากหน้าครัวเรือนนั้นเท่านั้น (households/show ->
 "+ เพิ่มสวน") ครัวเรือนที่นี่จึงเป็นค่าที่ล็อกไว้แล้ว แสดงเป็น
 read-only ไม่ใช่ตัวเลือก ดู FarmController::create() --}}
 <label class="field-label">ครัวเรือน (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $household->head_name }} ({{ $household->household_code }})" readonly disabled>
 <input type="hidden" name="household_id" value="{{ $household->id }}">
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ชื่อสวน</label>
 <input type="text" name="farm_name" class="form-control" value="{{ old('farm_name') }}" required>
 </div>
 <p class="text-xs text-secondary mb-0 mt-1">รหัสสวนจะถูกตั้งให้อัตโนมัติเมื่อบันทึก (เช่น FARM-0013)</p>
 </div>
 <div class="col-md-4">
 <label class="field-label">จังหวัด</label>
 {{-- เลือกศรีสะเกษไว้ล่วงหน้าเสมอ (config('drfis.default_province_id'))
 เพราะระบบนี้ใช้งานเฉพาะจังหวัดเดียว - ยังเปลี่ยนเป็นจังหวัดอื่นเองได้ปกติ --}}
 <select name="province_id" id="province_select" class="field-control">
 <option value="">-- ไม่ระบุ --</option>
 @foreach ($provinces as $province)
 <option value="{{ $province->id }}" @selected(old('province_id', $defaultProvinceId) == $province->id)>{{ $province->name_th }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-4">
 <label class="field-label">อำเภอ</label>
 <select name="district_id" id="district_select" class="field-control" data-selected="{{ old('district_id') }}">
 <option value="">-- ไม่ระบุ --</option>
 </select>
 </div>
 <div class="col-md-4">
 <label class="field-label">ตำบล</label>
 <select name="tambon_id" id="tambon_select" class="field-control" data-selected="{{ old('tambon_id') }}">
 <option value="">-- ไม่ระบุ --</option>
 </select>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ที่อยู่ / รายละเอียดที่ตั้งเพิ่มเติม</label>
 <input type="text" name="address" class="form-control" value="{{ old('address') }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">พื้นที่รวม (ไร่)</label>
 <input type="number" step="0.01" min="0" name="total_area_rai" class="form-control" value="{{ old('total_area_rai') }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">พิกัด GPS (ละติจูด)</label>
 <input type="number" step="0.0000001" name="gps_lat" id="gps_lat" class="form-control" value="{{ old('gps_lat') }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">พิกัด GPS (ลองจิจูด)</label>
 <input type="number" step="0.0000001" name="gps_lng" id="gps_lng" class="form-control" value="{{ old('gps_lng') }}">
 </div>
 </div>
 <x-gps-picker />
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">แหล่งน้ำ</label>
 <input type="text" name="water_source" class="form-control" value="{{ old('water_source') }}">
 </div>
 </div>
 <div class="col-md-6">
 <label class="field-label">สถานะ</label>
 <select name="status" class="field-control" required>
 <option value="active" @selected(old('status', 'active') === 'active')>ใช้งานอยู่</option>
 <option value="inactive" @selected(old('status') === 'inactive')>ระงับการใช้งาน</option>
 </select>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('households.show', $household) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>

 @push('scripts')
 <script src="{{ asset('js/cascading-location.js') }}" defer></script>
 <script>
 document.addEventListener('DOMContentLoaded', function () {
 drfisInitLocationCascade({
 province: 'province_select',
 district: 'district_select',
 tambon: 'tambon_select',
 districtsUrl: '{{ route('locations.districts') }}',
 tambonsUrl: '{{ route('locations.tambons') }}',
 });
 });
 </script>
 @endpush
</x-app-layout>
