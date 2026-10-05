@php
 $editing = $village->exists;
 $tambon = $village->tambon;
 $selectedProvinceId = old('province_id', $tambon?->district?->province_id ?? config('drfis.default_province_id'));
 $selectedDistrictId = old('district_id', $tambon?->district_id);
 $selectedTambonId = old('tambon_id', $village->tambon_id);
@endphp
<x-app-layout :title="$editing ? 'แก้ไขหมู่บ้าน' : 'เพิ่มหมู่บ้านใหม่'">
 <div class="row justify-content-center"><div class="col-xl-9">
 <div class="card">
 <div class="card-header"><h5 class="mb-1">{{ $editing ? 'แก้ไขข้อมูลหมู่บ้าน' : 'เพิ่มหมู่บ้านใหม่' }}</h5><p class="text-sm text-secondary mb-0">เลือกพื้นที่ตามลำดับจังหวัด อำเภอ และตำบล</p></div>
 <div class="card-body pt-0">
 @if ($errors->any())<div class="alert alert-danger text-white"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
 <form method="POST" action="{{ $editing ? route('villages.update', $village) : route('villages.store') }}">
 @csrf @if($editing) @method('PUT') @endif
 <div class="row g-3">
 <div class="col-md-4"><label class="field-label">จังหวัด</label><select name="province_id" id="province_select" class="field-control" required><option value="">-- เลือกจังหวัด --</option>@foreach($provinces as $province)<option value="{{ $province->id }}" @selected($selectedProvinceId == $province->id)>{{ $province->name_th }}</option>@endforeach</select></div>
 <div class="col-md-4"><label class="field-label">อำเภอ</label><select name="district_id" id="district_select" class="field-control" data-selected="{{ $selectedDistrictId }}" required><option value="">-- เลือกอำเภอ --</option></select></div>
 <div class="col-md-4"><label class="field-label">ตำบล</label><select name="tambon_id" id="tambon_select" class="field-control" data-selected="{{ $selectedTambonId }}" required><option value="">-- เลือกตำบล --</option></select></div>
 <div class="col-md-3"><label class="field-label">หมู่ที่</label><input type="number" min="1" max="99" name="village_no" class="field-control" value="{{ old('village_no', $village->village_no) }}" placeholder="เช่น 5"></div>
 <div class="col-md-9"><label class="field-label">ชื่อหมู่บ้าน</label><input name="name_th" class="field-control" value="{{ old('name_th', $village->name_th) }}" required placeholder="กรอกเฉพาะชื่อ เช่น บักดอง"></div>
 <div class="col-md-6"><label class="field-label">ละติจูด (ถ้ามี)</label><input name="latitude" class="field-control" value="{{ old('latitude', $village->latitude) }}"></div>
 <div class="col-md-6"><label class="field-label">ลองจิจูด (ถ้ามี)</label><input name="longitude" class="field-control" value="{{ old('longitude', $village->longitude) }}"></div>
 <div class="col-12"><input type="hidden" name="is_active" value="0"><label class="d-flex align-items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $village->exists ? $village->is_active : true))> เปิดให้เลือกใช้งานในแบบฟอร์มครัวเรือน</label></div>
 </div>
 <div class="mt-4"><button class="btn bg-gradient-success mb-0">บันทึกข้อมูลหมู่บ้าน</button> <a href="{{ route('villages.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a></div>
 </form>
 </div></div>
 </div></div>
 @push('scripts')
 <script src="{{ asset('js/cascading-location.js') }}?v={{ filemtime(public_path('js/cascading-location.js')) }}" defer></script>
 <script>document.addEventListener('DOMContentLoaded', function(){ drfisInitLocationCascade({ province:'province_select', district:'district_select', tambon:'tambon_select', districtsUrl:'{{ route('locations.districts') }}', tambonsUrl:'{{ route('locations.tambons') }}' }); });</script>
 @endpush
</x-app-layout>
