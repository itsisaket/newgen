<x-app-layout :title="'แก้ไขสวน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">แก้ไขสวน — {{ $farm->farm_name ?? $farm->farm_code }}</h6>
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

 <form method="POST" action="{{ route('farms.update', $farm) }}" novalidate>
 @csrf
 @method('PUT')
 <div class="row g-3">
 <div class="col-md-6">
 {{-- 15 ก.ย. round 2: ย้ายสวนไปครัวเรือนอื่นไม่ได้อีกต่อไปแล้ว -
 UpdateFarmRequest ไม่รับ household_id เลย (ดู FarmController::
 update()) ช่องนี้จึงเป็นข้อมูลอ้างอิงอย่างเดียว ไม่ใช่ฟอร์ม input --}}
 <label class="field-label">ครัวเรือน (ย้ายไม่ได้)</label>
 <input type="text" class="field-control" value="{{ $farm->household->head_name }} ({{ $farm->household->household_code }})" readonly disabled>
 </div>
 <div class="col-md-6">
 <label class="field-label">รหัสสวน (ตั้งอัตโนมัติ แก้ไขไม่ได้)</label>
 <input type="text" class="field-control" value="{{ $farm->farm_code }}" readonly disabled>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ชื่อสวน</label>
 <input type="text" name="farm_name" class="form-control" value="{{ old('farm_name', $farm->farm_name) }}" required>
 </div>
 </div>
 <div class="col-md-4">
 <label class="field-label">จังหวัด</label>
 <select name="province_id" id="province_select" class="field-control">
 <option value="">-- ไม่ระบุ --</option>
 @foreach ($provinces as $province)
 <option value="{{ $province->id }}" @selected(old('province_id', $farm->province_id) == $province->id)>{{ $province->name_th }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-4">
 <label class="field-label">อำเภอ</label>
 <select name="district_id" id="district_select" class="field-control" data-selected="{{ old('district_id', $farm->district_id) }}">
 <option value="">-- ไม่ระบุ --</option>
 </select>
 </div>
 <div class="col-md-4">
 <label class="field-label">ตำบล</label>
 <select name="tambon_id" id="tambon_select" class="field-control" data-selected="{{ old('tambon_id', $farm->tambon_id) }}">
 <option value="">-- ไม่ระบุ --</option>
 </select>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ที่อยู่ / รายละเอียดที่ตั้งเพิ่มเติม</label>
 <input type="text" name="address" class="form-control" value="{{ old('address', $farm->address) }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">พื้นที่รวม (ไร่)</label>
 <input type="number" step="0.01" min="0" name="total_area_rai" class="form-control" value="{{ old('total_area_rai', $farm->total_area_rai) }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">พิกัด GPS (ละติจูด)</label>
 <input type="number" step="0.0000001" name="gps_lat" id="gps_lat" class="form-control" value="{{ old('gps_lat', $farm->gps_lat) }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">พิกัด GPS (ลองจิจูด)</label>
 <input type="number" step="0.0000001" name="gps_lng" id="gps_lng" class="form-control" value="{{ old('gps_lng', $farm->gps_lng) }}">
 </div>
 </div>
 <x-gps-picker />
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">แหล่งน้ำ</label>
 <input type="text" name="water_source" class="form-control" value="{{ old('water_source', $farm->water_source) }}">
 </div>
 </div>
 <div class="col-md-6">
 <label class="field-label">สถานะ</label>
 <select name="status" class="field-control" required>
 <option value="active" @selected(old('status', $farm->status) === 'active')>ใช้งานอยู่</option>
 <option value="inactive" @selected(old('status', $farm->status) === 'inactive')>ระงับการใช้งาน</option>
 </select>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึกการแก้ไข</button>
 <a href="{{ route('farms.show', $farm) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
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
