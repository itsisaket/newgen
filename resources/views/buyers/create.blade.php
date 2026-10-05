<x-app-layout :title="'เพิ่มผู้ซื้อ'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">เพิ่มผู้ซื้อ </h6>
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

 <form method="POST" action="{{ route('buyers.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ชื่อผู้ซื้อ</label>
 <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ประเภท (เช่น ล้ง/พ่อค้าคนกลาง/สหกรณ์/ผู้บริโภคตรง)</label>
 <input type="text" name="buyer_type" class="form-control" value="{{ old('buyer_type') }}">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">เบอร์โทร</label>
 <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ช่องทางติดต่ออื่น (Line/อีเมล ฯลฯ)</label>
 <input type="text" name="contact" class="form-control" value="{{ old('contact') }}">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">มาตรฐานที่ต้องการ (เช่น GAP/Organic)</label>
 <input type="text" name="standard_required" class="form-control" value="{{ old('standard_required') }}">
 </div>
 </div>
 <div class="col-12">
 <div class="input-group input-group-outline">
 <label class="form-label">หมายเหตุ</label>
 <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
 </div>
 </div>
 </div>
 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('buyers.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
