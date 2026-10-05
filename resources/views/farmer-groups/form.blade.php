@php
 $editing = $group->exists;
 $tambon = $group->tambon;
 $selectedProvinceId = old('province_id', $tambon?->district?->province_id ?? config('drfis.default_province_id'));
 $selectedDistrictId = old('district_id', $tambon?->district_id);
 $selectedTambonId = old('tambon_id', $group->tambon_id);
@endphp
<x-app-layout :title="$editing ? 'แก้ไขกลุ่มเกษตรกร' : 'เพิ่มกลุ่มเกษตรกร'">
 <div class="row justify-content-center"><div class="col-xl-9"><div class="card">
 <div class="card-header"><h5 class="mb-1">{{ $editing ? 'แก้ไขกลุ่มเกษตรกร' : 'เพิ่มกลุ่มเกษตรกรใหม่' }}</h5><p class="text-sm text-secondary mb-0">ระบุพื้นที่ตั้งของกลุ่มและข้อมูลผู้ประสานงาน</p></div>
 <div class="card-body pt-0">
 @if($errors->any())<div class="alert alert-danger text-white"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
 <form method="POST" action="{{ $editing ? route('farmer-groups.update', $group) : route('farmer-groups.store') }}">
 @csrf @if($editing) @method('PUT') @endif
 <div class="row g-3">
 <div class="col-md-4"><label class="field-label">จังหวัด</label><select name="province_id" id="province_select" class="field-control" required><option value="">-- เลือกจังหวัด --</option>@foreach($provinces as $province)<option value="{{ $province->id }}" @selected($selectedProvinceId == $province->id)>{{ $province->name_th }}</option>@endforeach</select></div>
 <div class="col-md-4"><label class="field-label">อำเภอ</label><select name="district_id" id="district_select" class="field-control" data-selected="{{ $selectedDistrictId }}" required><option value="">-- เลือกอำเภอ --</option></select></div>
 <div class="col-md-4"><label class="field-label">ตำบล</label><select name="tambon_id" id="tambon_select" class="field-control" data-selected="{{ $selectedTambonId }}" required><option value="">-- เลือกตำบล --</option></select></div>
 <div class="col-12"><label class="field-label">ชื่อกลุ่มเกษตรกร</label><input name="name" class="field-control" value="{{ old('name', $group->name) }}" required placeholder="เช่น กลุ่มเกษตรกรผู้ปลูกทุเรียนบ้านบักดอง"></div>
 <div class="col-md-6"><label class="field-label">ประธาน/ผู้ประสานงาน</label><input name="leader_name" class="field-control" value="{{ old('leader_name', $group->leader_name) }}"></div>
 <div class="col-md-6"><label class="field-label">เบอร์โทรผู้ประสานงาน</label><input name="contact_phone" class="field-control" value="{{ old('contact_phone', $group->contact_phone) }}"></div>
 <div class="col-12"><label class="field-label">รายละเอียดเพิ่มเติม</label><textarea name="description" class="field-control" rows="3">{{ old('description', $group->description) }}</textarea></div>
 <div class="col-12"><input type="hidden" name="is_active" value="0"><label class="d-flex align-items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $group->exists ? $group->is_active : true))> เปิดให้เลือกในหน้าครัวเรือน</label></div>
 </div>
 <div class="mt-4"><button class="btn bg-gradient-success mb-0">บันทึกกลุ่มเกษตรกร</button> <a href="{{ route('farmer-groups.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a></div>
 </form></div></div></div></div>
 @push('scripts')
 <script src="{{ asset('js/cascading-location.js') }}?v={{ filemtime(public_path('js/cascading-location.js')) }}" defer></script>
 <script>document.addEventListener('DOMContentLoaded',function(){drfisInitLocationCascade({province:'province_select',district:'district_select',tambon:'tambon_select',districtsUrl:'{{ route('locations.districts') }}',tambonsUrl:'{{ route('locations.tambons') }}'});});</script>
 @endpush
</x-app-layout>
