<x-app-layout :title="'เพิ่มผู้ใช้'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">เพิ่มผู้ใช้ใหม่</h6>
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

 <form method="POST" action="{{ route('users.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ชื่อ-นามสกุล</label>
 <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">อีเมล</label>
 <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">เบอร์โทร</label>
 <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
 </div>
 </div>
 <div class="col-md-3">
 <label class="field-label">บทบาท (Role)</label>
 <select name="role_id" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach ($roles as $role)
 <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-3">
 <label class="field-label">สถานะ</label>
 <select name="status" class="field-control" required>
 <option value="active" @selected(old('status', 'active') === 'active')>ใช้งานอยู่</option>
 <option value="inactive" @selected(old('status') === 'inactive')>ระงับการใช้งาน</option>
 </select>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">รหัสผ่าน</label>
 <input type="password" name="password" class="form-control" required minlength="8">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ยืนยันรหัสผ่าน</label>
 <input type="password" name="password_confirmation" class="form-control" required minlength="8">
 </div>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('users.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
