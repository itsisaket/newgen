<x-app-layout :title="'บันทึกการใช้ผลิตภัณฑ์'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึกการใช้ผลิตภัณฑ์ชีวมวลในแปลง</h6>
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

 <form method="POST" action="{{ route('product-usages.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6 col-lg-4">
 <label class="field-label">แปลง (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $plot->plot_code }} ({{ $plot->farm->household->head_name }})" readonly disabled>
 <input type="hidden" name="plot_id" value="{{ $plot->id }}">
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ผลิตภัณฑ์ (คงเหลือของครัวเรือนนี้)</label>
 <select name="product_id" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach ($products as $row)
 <option value="{{ $row['product']->id }}" @selected(old('product_id') == $row['product']->id)>
 {{ $row['product']->name }} (คงเหลือ {{ number_format($row['balance'], 2) }} {{ $row['product']->unit }})
 </option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่ใช้</label>
 <input type="date" name="application_date" class="form-control" required value="{{ old('application_date', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ปริมาณที่ใช้</label>
 <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" required value="{{ old('quantity') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">อัตราการใช้ (เช่น ลิตร/ไร่)</label>
 <input type="number" step="0.01" min="0" name="usage_rate" class="form-control" value="{{ old('usage_rate') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">วิธีการใช้ (เช่น ราดโคนต้น, ฉีดพ่น)</label>
 <input type="text" name="application_method" class="form-control" value="{{ old('application_method') }}">
 </div>
 </div>
 </div>
 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('plots.show', $plot) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
