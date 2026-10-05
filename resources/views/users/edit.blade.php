<x-app-layout :title="'แก้ไขผู้ใช้'">
 <div class="mb-3">
 <h5 class="mb-1">แก้ไขผู้ใช้ — {{ $targetUser->name }}</h5>
 <p class="text-sm text-secondary mb-0">{{ $targetUser->email }}</p>
 </div>

 @if ($errors->any())
 <div class="alert alert-danger text-white">
 <ul class="mb-0 ps-3">
 @foreach ($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <div class="row">
 <div class="col-lg-6">
 <div class="card mb-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">ข้อมูลบัญชีและบทบาท</h6>
 </div>
 </div>
 <div class="card-body">
 <form method="POST" action="{{ route('users.update', $targetUser) }}" novalidate>
 @csrf
 @method('PUT')
 <div class="input-group input-group-outline mb-3">
 <label class="form-label">ชื่อ-นามสกุล</label>
 <input type="text" name="name" class="form-control" value="{{ old('name', $targetUser->name) }}" required>
 </div>
 <div class="input-group input-group-outline mb-3">
 <label class="form-label">อีเมล</label>
 <input type="email" name="email" class="form-control" value="{{ old('email', $targetUser->email) }}" required>
 </div>
 <div class="input-group input-group-outline mb-3">
 <label class="form-label">เบอร์โทร</label>
 <input type="text" name="phone" class="form-control" value="{{ old('phone', $targetUser->phone) }}">
 </div>
 <div class="row">
 <div class="col-6">
 <div class="input-group input-group-outline mb-3">
 <label class="form-label">รหัสผ่านใหม่</label>
 <input type="password" name="password" class="form-control" minlength="8">
 </div>
 </div>
 <div class="col-6">
 <div class="input-group input-group-outline mb-3">
 <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
 <input type="password" name="password_confirmation" class="form-control" minlength="8">
 </div>
 </div>
 </div>
 <p class="text-xs text-secondary mt-n2 mb-3">เว้นว่างช่องรหัสผ่านไว้ถ้าไม่ต้องการเปลี่ยน</p>

 <div class="row g-3 mb-3">
 <div class="col-6">
 <label class="field-label">บทบาท (Role)</label>
 <select name="role_id" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach ($roles as $role)
 <option value="{{ $role->id }}" @selected(old('role_id', $targetUser->role_id) == $role->id)>{{ $role->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-6">
 <label class="field-label">สถานะ</label>
 <select name="status" class="field-control" required>
 <option value="active" @selected(old('status', $targetUser->status) === 'active')>ใช้งานอยู่</option>
 <option value="inactive" @selected(old('status', $targetUser->status) === 'inactive')>ระงับการใช้งาน</option>
 </select>
 </div>
 </div>

 <button class="btn bg-gradient-success">บันทึก</button>
 <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">กลับไปหน้ารายการ</a>
 </form>
 </div>
 </div>
 </div>

 <div class="col-lg-6">
 <div class="card mb-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">ขอบเขตพื้นที่รับผิดชอบ</h6>
 </div>
 </div>
 <div class="card-body">
 <p class="text-xs text-secondary">
 Area Scope — Blueprint 4.1: ผู้ใช้หนึ่งคนมีได้หลายขอบเขต เช่น เจ้าหน้าที่อำเภอที่ดูแลหลายตำบล
 </p>

 @forelse ($targetUser->areaAssignments as $assignment)
 <div class="d-flex justify-content-between align-items-center border-bottom py-2">
 <span class="text-sm">{{ $assignment->scopeLabel() }}</span>
 <form method="POST" action="{{ route('users.area-assignments.destroy', [$targetUser, $assignment]) }}"
 onsubmit="return confirm('ยืนยันนำขอบเขตนี้ออก?');">
 @csrf
 @method('DELETE')
 <button class="btn btn-outline-danger btn-sm mb-0">นำออก</button>
 </form>
 </div>
 @empty
 <p class="text-secondary text-sm">ยังไม่มีขอบเขตพื้นที่ที่มอบหมาย</p>
 @endforelse

 <form method="POST" action="{{ route('users.area-assignments.store', $targetUser) }}" class="mt-3" id="area-assignment-form" novalidate>
 @csrf
 <label class="field-label">ระดับขอบเขต</label>
 <select name="scope_type" id="scope_type" class="field-control mb-2" required>
 <option value="all">ทั้งหมด (Super Admin / Project Admin)</option>
 <option value="province">จังหวัด</option>
 <option value="district">อำเภอ</option>
 <option value="tambon">ตำบล</option>
 <option value="farmer_group">กลุ่มเกษตรกร</option>
 </select>

 <div class="mb-2 scope-target" data-scope="province" hidden>
 <label class="field-label">จังหวัด</label>
 <select name="province_id" class="field-control">
 @foreach ($provinces as $p)
 <option value="{{ $p->id }}">{{ $p->name_th }}</option>
 @endforeach
 </select>
 </div>
 <div class="mb-2 scope-target" data-scope="district" hidden>
 <label class="field-label">อำเภอ</label>
 <select name="district_id" class="field-control">
 @foreach ($districts as $d)
 <option value="{{ $d->id }}">{{ $d->name_th }}</option>
 @endforeach
 </select>
 </div>
 <div class="mb-2 scope-target" data-scope="tambon" hidden>
 <label class="field-label">ตำบล</label>
 <select name="tambon_id" class="field-control">
 @foreach ($tambons as $t)
 <option value="{{ $t->id }}">{{ $t->name_th }}</option>
 @endforeach
 </select>
 </div>
 <div class="mb-2 scope-target" data-scope="farmer_group" hidden>
 <label class="field-label">กลุ่มเกษตรกร</label>
 <select name="farmer_group_id" class="field-control">
 @foreach ($farmerGroups as $g)
 <option value="{{ $g->id }}">{{ $g->name }}</option>
 @endforeach
 </select>
 </div>

 <button class="btn bg-gradient-info btn-sm mb-0 mt-2">+ เพิ่มขอบเขต</button>
 </form>
 </div>
 </div>
 </div>
 </div>

 @push('scripts')
 <script>
 (function () {
 var select = document.getElementById('scope_type');
 var targets = document.querySelectorAll('.scope-target');

 function sync() {
 targets.forEach(function (el) {
 el.hidden = el.dataset.scope !== select.value;
 });
 }

 select.addEventListener('change', sync);
 sync();
 })();
 </script>
 @endpush
</x-app-layout>
