<x-app-layout :title="'ลงทะเบียนเครื่อง'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">ลงทะเบียนเครื่องใหม่ </h6>
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
 <form method="POST" action="{{ route('technology-assets.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">ประเภทเทคโนโลยี (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $technology->name }}" readonly disabled>
 <input type="hidden" name="technology_id" value="{{ $technology->id }}">
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">รหัสเครื่อง (Asset Code)</label>
 <input type="text" name="asset_code" class="form-control" value="{{ old('asset_code') }}" required>
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่ได้รับ/จัดซื้อ</label>
 <input type="date" name="acquired_date" class="form-control" value="{{ old('acquired_date') }}">
 </div>
 </div>
 <div class="col-md-6">
 <label class="field-label">สภาพ</label>
 <select name="condition_status" class="field-control" required>
 <option value="good" @selected(old('condition_status', 'good') === 'good')>ใช้งานได้ดี</option>
 <option value="needs_repair" @selected(old('condition_status') === 'needs_repair')>ต้องซ่อมบำรุง</option>
 <option value="retired" @selected(old('condition_status') === 'retired')>ปลดระวาง</option>
 </select>
 </div>
 </div>
 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('technologies.show', $technology) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
