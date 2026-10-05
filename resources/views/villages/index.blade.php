<x-app-layout :title="'หมู่บ้านในประเทศไทย'">
 <div class="card">
 <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
 <div>
 <h5 class="mb-1">ทะเบียนหมู่บ้านในประเทศไทย</h5>
 <p class="text-sm text-secondary mb-0">ค้นหาตามพื้นที่หรือรหัสหมู่บ้านของกรมการปกครอง</p>
 </div>
 @can('create', App\Models\Village::class)
 <a href="{{ route('villages.create') }}" class="btn bg-gradient-success mb-0">+ เพิ่มหมู่บ้านใหม่</a>
 @endcan
 </div>
 <div class="card-body pt-0">
 <form method="GET" class="row g-3 mb-4" id="village-filter">
 <div class="col-lg-3 col-md-6">
 <label class="field-label">ค้นหาชื่อหรือรหัส</label>
 <input name="q" class="field-control" value="{{ request('q') }}" placeholder="เช่น บักดอง หรือ 3308">
 </div>
 <div class="col-lg-3 col-md-6">
 <label class="field-label">จังหวัด</label>
 <select name="province_id" id="province_select" class="field-control">
 <option value="">ทุกจังหวัด</option>
 @foreach ($provinces as $province)
 <option value="{{ $province->id }}" @selected(request('province_id') == $province->id)>{{ $province->name_th }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-lg-3 col-md-6">
 <label class="field-label">อำเภอ</label>
 <select name="district_id" id="district_select" class="field-control" data-selected="{{ request('district_id') }}">
 <option value="">ทุกอำเภอ</option>
 </select>
 </div>
 <div class="col-lg-3 col-md-6">
 <label class="field-label">ตำบล</label>
 <select name="tambon_id" id="tambon_select" class="field-control" data-selected="{{ request('tambon_id') }}">
 <option value="">ทุกตำบล</option>
 </select>
 </div>
 <div class="col-12 d-flex gap-2">
 <button class="btn bg-gradient-dark mb-0">ค้นหา</button>
 <a href="{{ route('villages.index') }}" class="btn btn-outline-secondary mb-0">ล้างตัวกรอง</a>
 </div>
 </form>

 <div class="table-responsive">
 <table class="table align-items-center mb-0">
 <thead><tr><th>หมู่บ้าน</th><th>ตำบล / อำเภอ / จังหวัด</th><th>รหัสราชการ</th><th>แหล่งข้อมูล</th><th>สถานะ</th><th></th></tr></thead>
 <tbody>
 @forelse ($villages as $village)
 <tr>
 <td class="fw-semibold">{{ $village->display_name }}</td>
 <td>{{ $village->tambon->name_th }} / {{ $village->tambon->district->name_th }} / {{ $village->tambon->district->province->name_th }}</td>
 <td>{{ $village->official_code ?: '—' }}</td>
 <td class="text-sm text-secondary">{{ $village->source ?: 'ข้อมูลเดิมของระบบ' }}</td>
 <td><span class="badge {{ $village->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $village->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}</span></td>
 <td class="text-end">@can('update', $village)<a class="btn btn-sm btn-outline-success mb-0" href="{{ route('villages.edit', $village) }}">แก้ไข</a>@endcan</td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary py-4">ไม่พบข้อมูลหมู่บ้านตามเงื่อนไข</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 <div class="mt-3">{{ $villages->links() }}</div>
 </div>
 </div>

 @push('scripts')
 <script src="{{ asset('js/cascading-location.js') }}?v={{ filemtime(public_path('js/cascading-location.js')) }}" defer></script>
 <script>
 document.addEventListener('DOMContentLoaded', function () {
 drfisInitLocationCascade({
 province: 'province_select', district: 'district_select', tambon: 'tambon_select',
 districtsUrl: '{{ route('locations.districts') }}', tambonsUrl: '{{ route('locations.tambons') }}'
 });
 });
 </script>
 @endpush
</x-app-layout>
