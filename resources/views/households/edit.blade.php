<x-app-layout :title="'แก้ไขครัวเรือน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">แก้ไขครัวเรือน — {{ $household->head_name }}</h6>
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

 <form method="POST" action="{{ route('households.update', $household) }}" novalidate>
 @csrf
 @method('PUT')
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">รหัสครัวเรือน (ตั้งอัตโนมัติ แก้ไขไม่ได้)</label>
 <input type="text" class="field-control" value="{{ $household->household_code }}" readonly disabled>
 </div>
 <div class="col-md-6">
 <label class="field-label" for="head_name">ชื่อหัวหน้าครัวเรือน</label>
 <input type="text" id="head_name" name="head_name" class="field-control" value="{{ old('head_name', $household->head_name) }}" required>
 </div>
 <div class="col-md-6">
 <label class="field-label" for="phone">เบอร์โทร</label>
 <input type="text" id="phone" name="phone" class="field-control" value="{{ old('phone', $household->phone) }}">
 </div>
 <div class="col-md-6">
 <label class="field-label" for="id_card_number">เลขบัตรประชาชน (13 หลัก)</label>
 <input type="text" id="id_card_number" name="id_card_number" inputmode="numeric" maxlength="13" class="field-control" value="{{ old('id_card_number', $household->id_card_number_encrypted) }}">
 </div>
 @can('create', App\Models\Household::class)
 {{-- Sprint 5 Role/Permission Matrix audit
 (DRFIS-Sprint5-Design-16Sep.md ส่วน A) -
 farmer_group/village/status are staff-only
 fields ("ไม่ใช่ farmer_group/village") - a
 household's own Farmer/Innovator login
 (HouseholdPolicy::update() lets them reach
 this page for contact-info edits) never
 sees these; UpdateHouseholdRequest also
 strips them server-side so this isn't just
 a UI hide. Reusing the 'create' Household
 ability as the "is staff" check here since
 HouseholdPolicy::create() is already
 exactly "hasAnyRole(Role::STAFF)". --}}
 <div class="col-md-4">
 <label class="field-label">จังหวัด</label>
 <select id="province_select" class="field-control">
 <option value="">-- ทั้งหมด --</option>
 @foreach ($provinces as $province)
 <option value="{{ $province->id }}" @selected($selectedProvinceId == $province->id)>{{ $province->name_th }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-4">
 <label class="field-label">อำเภอ</label>
 <select id="district_select" class="field-control" data-selected="{{ $selectedDistrictId }}">
 <option value="">-- ทั้งหมด --</option>
 </select>
 </div>
 <div class="col-md-4">
 <label class="field-label">ตำบล</label>
 <select id="tambon_select" class="field-control" data-selected="{{ $selectedTambonId }}">
 <option value="">-- ทั้งหมด --</option>
 </select>
 </div>
 <p class="text-xs text-secondary mb-0 col-12">
 เลือกจังหวัด/อำเภอ/ตำบลเพื่อกรองรายการกลุ่มเกษตรกรและหมู่บ้านด้านล่างให้แคบลง (ไม่บังคับ)
 </p>
 <div class="col-md-6">
 <div class="d-flex justify-content-between align-items-center">
 <label class="field-label">กลุ่มเกษตรกร</label>
 <a href="{{ route('farmer-groups.create', ['tambon_id' => $selectedTambonId]) }}" class="text-success text-xs fw-bold">+ เพิ่มกลุ่มใหม่</a>
 </div>
 <select name="farmer_group_id" id="farmer_group_id" class="field-control">
 <option value="">-- ไม่ระบุ --</option>
 @foreach ($farmerGroups as $group)
 <option value="{{ $group->id }}" data-tambon-id="{{ $group->tambon_id }}" @selected(old('farmer_group_id', $household->farmer_group_id) == $group->id)>{{ $group->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6">
 <div class="d-flex justify-content-between align-items-center">
 <label class="field-label">หมู่บ้าน</label>
 <a href="{{ route('villages.create', ['tambon_id' => $selectedTambonId]) }}" class="text-success text-xs fw-bold">+ เพิ่มหมู่บ้านใหม่</a>
 </div>
 <select name="village_id" id="village_id" class="field-control" data-selected="{{ old('village_id', $household->village_id) }}">
 <option value="">-- ไม่ระบุ --</option>
 </select>
 </div>
 <div class="col-md-6">
 <label class="field-label">สถานะ</label>
 <select name="status" class="field-control" required>
 <option value="active" @selected(old('status', $household->status) === 'active')>ใช้งานอยู่</option>
 <option value="inactive" @selected(old('status', $household->status) === 'inactive')>ระงับการใช้งาน</option>
 <option value="withdrawn" @selected(old('status', $household->status) === 'withdrawn')>ถอนตัว</option>
 </select>
 </div>
 @endcan
 <div class="col-md-6">
 <label class="field-label">วันที่ลงทะเบียน</label>
 <input type="date" name="registered_at" class="field-control" value="{{ old('registered_at', $household->registered_at?->format('Y-m-d')) }}">
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึกการแก้ไข</button>
 <a href="{{ route('households.show', $household) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
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
