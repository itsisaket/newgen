<x-app-layout :title="'เพิ่มครัวเรือน'">
 <div class="row">
 <div class="col-12">
 <x-wizard-steps :current="1" />

 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">ลงทะเบียนครัวเรือนใหม่ (สมัครสมาชิก)</h6>
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

 <form method="POST" action="{{ route('households.store') }}" novalidate>
 @csrf
 <p class="text-xs text-secondary">
 รหัสครัวเรือนจะถูกตั้งให้อัตโนมัติเมื่อบันทึก (เช่น HH-0013)
 </p>
 <div class="row g-3">
 <div class="col-md-4">
 <label class="field-label" for="head_name">ชื่อหัวหน้าครัวเรือน</label>
 <input type="text" id="head_name" name="head_name" class="field-control" value="{{ old('head_name') }}" required>
 </div>
 <div class="col-md-4">
 <label class="field-label" for="phone">เบอร์โทร</label>
 <input type="text" id="phone" name="phone" class="field-control" value="{{ old('phone') }}">
 </div>
 <div class="col-md-4">
 <label class="field-label" for="id_card_number">เลขบัตรประชาชน (13 หลัก)</label>
 <input type="text" id="id_card_number" name="id_card_number" inputmode="numeric" maxlength="13" class="field-control" value="{{ old('id_card_number') }}">
 </div>
 <div class="col-md-4">
 <label class="field-label">จังหวัด</label>
 {{-- เลือกศรีสะเกษไว้ล่วงหน้าเสมอ (config('drfis.default_province_id'))
 เพราะระบบนี้ใช้งานเฉพาะจังหวัดเดียว - ช่องนี้เป็นแค่ตัวกรอง
 ฝั่ง client (ไม่มี name ไม่ถูกส่งไปกับฟอร์ม) ยังเปลี่ยนเป็น
 จังหวัดอื่นเองได้ปกติเพื่อกรองกลุ่มเกษตรกร/หมู่บ้านจากที่อื่น --}}
 <select id="province_select" class="field-control">
 <option value="">-- ทั้งหมด --</option>
 @foreach ($provinces as $province)
 <option value="{{ $province->id }}" @selected($defaultProvinceId == $province->id)>{{ $province->name_th }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-4">
 <label class="field-label">อำเภอ</label>
 <select id="district_select" class="field-control">
 <option value="">-- ทั้งหมด --</option>
 </select>
 </div>
 <div class="col-md-4">
 <label class="field-label">ตำบล</label>
 <select id="tambon_select" class="field-control">
 <option value="">-- ทั้งหมด --</option>
 </select>
 </div>
 <p class="text-xs text-secondary mb-0 col-12">
 เลือกจังหวัด/อำเภอ/ตำบลเพื่อกรองรายการกลุ่มเกษตรกรและหมู่บ้านด้านล่างให้แคบลง (ไม่บังคับ)
 </p>
 <div class="col-md-6">
 <div class="d-flex justify-content-between align-items-center">
 <label class="field-label">กลุ่มเกษตรกร</label>
 <a href="{{ route('farmer-groups.create') }}" class="text-success text-xs fw-bold">+ เพิ่มกลุ่มใหม่</a>
 </div>
 <select name="farmer_group_id" id="farmer_group_id" class="field-control">
 <option value="">-- ไม่ระบุ --</option>
 @foreach ($farmerGroups as $group)
 <option value="{{ $group->id }}" data-tambon-id="{{ $group->tambon_id }}" @selected(old('farmer_group_id') == $group->id)>{{ $group->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6">
 <div class="d-flex justify-content-between align-items-center">
 <label class="field-label">หมู่บ้าน</label>
 <a href="{{ route('villages.create') }}" class="text-success text-xs fw-bold">+ เพิ่มหมู่บ้านใหม่</a>
 </div>
 <select name="village_id" id="village_id" class="field-control" data-selected="{{ old('village_id') }}">
 <option value="">-- ไม่ระบุ --</option>
 </select>
 </div>
 <div class="col-md-6">
 <label class="field-label">วันที่ลงทะเบียน</label>
 <input type="date" name="registered_at" class="field-control" value="{{ old('registered_at') }}">
 </div>
 <div class="col-md-6">
 <label class="field-label">สถานะ</label>
 <select name="status" class="field-control" required>
 <option value="active" @selected(old('status', 'active') === 'active')>ใช้งานอยู่</option>
 <option value="inactive" @selected(old('status') === 'inactive')>ระงับการใช้งาน</option>
 <option value="withdrawn" @selected(old('status') === 'withdrawn')>ถอนตัว</option>
 </select>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('households.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>

 @push('scripts')
 <script src="{{ asset('js/cascading-location.js') }}?v={{ filemtime(public_path('js/cascading-location.js')) }}" defer></script>
 <script>
 document.addEventListener('DOMContentLoaded', function () {
 drfisInitLocationCascade({
 province: 'province_select',
 district: 'district_select',
 tambon: 'tambon_select',
 districtsUrl: '{{ route('locations.districts') }}',
 tambonsUrl: '{{ route('locations.tambons') }}',
 village: 'village_id',
 villagesUrl: '{{ route('locations.villages') }}',
 tambonDependents: ['farmer_group_id'],
 });
 });
 </script>
 @endpush
</x-app-layout>
