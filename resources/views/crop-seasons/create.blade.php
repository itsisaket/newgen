<x-app-layout :title="'เพิ่มฤดูผลิต'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">เพิ่มฤดูผลิต</h6>
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
 <p class="text-xs text-secondary">
 ตั้งสถานะเป็น "กำลังดำเนินการ" ได้ครั้งละ 1 ฤดูผลิตเท่านั้น — ถ้าเลือกสถานะนี้
 ระบบจะปิด (เปลี่ยนเป็น "ปิดแล้ว") ฤดูผลิตอื่นที่กำลังดำเนินการอยู่โดยอัตโนมัติ
 เพราะหลายหน้าจอ (/ เป็นต้น) อ้างอิงฤดูผลิตที่ "active" อยู่เพียงฤดูเดียวเสมอ
 </p>
 <form method="POST" action="{{ route('crop-seasons.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ชื่อฤดูผลิต (เช่น 2569/70)</label>
 <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
 </div>
 </div>
 <div class="col-md-3">
 <label class="field-label">สถานะ</label>
 <select name="status" class="field-control" required>
 <option value="upcoming" @selected(old('status', 'upcoming') === 'upcoming')>ยังไม่เริ่ม</option>
 <option value="active" @selected(old('status') === 'active')>กำลังดำเนินการ</option>
 <option value="closed" @selected(old('status') === 'closed')>ปิดแล้ว</option>
 </select>
 </div>
 <div class="col-md-3"></div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่เริ่มต้น</label>
 <input type="date" name="start_date" class="form-control" required value="{{ old('start_date') }}">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่สิ้นสุด</label>
 <input type="date" name="end_date" class="form-control" required value="{{ old('end_date') }}">
 </div>
 </div>
 </div>
 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('crop-seasons.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
