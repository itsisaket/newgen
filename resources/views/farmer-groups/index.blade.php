<x-app-layout :title="'กลุ่มเกษตรกร'">
 <div class="card">
 <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
 <div><h5 class="mb-1">ทะเบียนกลุ่มเกษตรกร</h5><p class="text-sm text-secondary mb-0">จัดการกลุ่มเกษตรกรและพื้นที่รับผิดชอบ</p></div>
 @can('create', App\Models\FarmerGroup::class)<a href="{{ route('farmer-groups.create') }}" class="btn bg-gradient-success mb-0">+ เพิ่มกลุ่มเกษตรกร</a>@endcan
 </div>
 <div class="card-body pt-0">
 <form method="GET" class="row g-3 mb-4">
 <div class="col-lg-3 col-md-6"><label class="field-label">ค้นหาชื่อกลุ่ม/ประธาน</label><input name="q" class="field-control" value="{{ request('q') }}" placeholder="พิมพ์คำค้นหา"></div>
 <div class="col-lg-3 col-md-6"><label class="field-label">จังหวัด</label><select name="province_id" id="province_select" class="field-control"><option value="">ทุกจังหวัด</option>@foreach($provinces as $province)<option value="{{ $province->id }}" @selected(request('province_id') == $province->id)>{{ $province->name_th }}</option>@endforeach</select></div>
 <div class="col-lg-3 col-md-6"><label class="field-label">อำเภอ</label><select name="district_id" id="district_select" class="field-control" data-selected="{{ request('district_id') }}"><option value="">ทุกอำเภอ</option></select></div>
 <div class="col-lg-3 col-md-6"><label class="field-label">ตำบล</label><select name="tambon_id" id="tambon_select" class="field-control" data-selected="{{ request('tambon_id') }}"><option value="">ทุกตำบล</option></select></div>
 <div class="col-12 d-flex gap-2"><button class="btn bg-gradient-dark mb-0">ค้นหา</button><a href="{{ route('farmer-groups.index') }}" class="btn btn-outline-secondary mb-0">ล้างตัวกรอง</a></div>
 </form>
 <div class="table-responsive"><table class="table align-items-center mb-0">
 <thead><tr><th>ชื่อกลุ่ม</th><th>พื้นที่</th><th>ประธาน/ผู้ประสานงาน</th><th>สมาชิกในระบบ</th><th>สถานะ</th><th></th></tr></thead>
 <tbody>@forelse($groups as $group)<tr>
 <td class="fw-semibold">{{ $group->name }}</td>
 <td>{{ $group->tambon->name_th }} / {{ $group->tambon->district->name_th }} / {{ $group->tambon->district->province->name_th }}</td>
 <td>{{ $group->leader_name ?: '—' }} @if($group->contact_phone)<div class="text-xs text-secondary">{{ $group->contact_phone }}</div>@endif</td>
 <td>{{ number_format($group->households_count) }} ครัวเรือน</td>
 <td><span class="badge {{ $group->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $group->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}</span></td>
 <td class="text-end">@can('update', $group)<a href="{{ route('farmer-groups.edit', $group) }}" class="btn btn-sm btn-outline-success mb-0">แก้ไข</a>@endcan</td>
 </tr>@empty<tr><td colspan="6" class="text-center text-secondary py-4">ยังไม่มีกลุ่มเกษตรกรตามเงื่อนไข</td></tr>@endforelse</tbody>
 </table></div>
 <div class="mt-3">{{ $groups->links() }}</div>
 </div></div>
 @push('scripts')
 <script src="{{ asset('js/cascading-location.js') }}?v={{ filemtime(public_path('js/cascading-location.js')) }}" defer></script>
 <script>document.addEventListener('DOMContentLoaded',function(){drfisInitLocationCascade({province:'province_select',district:'district_select',tambon:'tambon_select',districtsUrl:'{{ route('locations.districts') }}',tambonsUrl:'{{ route('locations.tambons') }}'});});</script>
 @endpush
</x-app-layout>
