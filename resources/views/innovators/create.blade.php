<x-app-layout :title="'ลงทะเบียนนวัตกร/แกนนำ'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">ลงทะเบียนนวัตกร/แกนนำ (ไม่มีครัวเรือนในระบบ)</h6>
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

 <form method="POST" action="{{ route('innovators.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ชื่อ</label>
 <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">เบอร์โทร</label>
 <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
 </div>
 </div>
 <div class="col-md-6">
 <label class="field-label">ครัวเรือน (ถ้ามี — ไม่บังคับ)</label>
 <select name="household_id" class="field-control">
 <option value="">-- ไม่มีครัวเรือน / แกนนำภายนอก --</option>
 @foreach ($households as $household)
 <option value="{{ $household->id }}" @selected(old('household_id') == $household->id)>{{ $household->head_name }} ({{ $household->household_code }})</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ระดับ/บทบาท (เช่น แกนนำระดับตำบล)</label>
 <input type="text" name="level" class="form-control" value="{{ old('level') }}">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่ลงทะเบียน</label>
 <input type="date" name="registered_at" class="form-control" required value="{{ old('registered_at', now()->toDateString()) }}">
 </div>
 </div>
 </div>
 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('innovators.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
