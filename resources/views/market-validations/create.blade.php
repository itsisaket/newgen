<x-app-layout :title="'บันทึกความต้องการตลาด'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึกความต้องการตลาด</h6>
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

 <form method="POST" action="{{ route('market-validations.store') }}" novalidate>
 @csrf
 <input type="hidden" name="buyer_id" value="{{ $buyer->id }}">
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">ผู้ซื้อ (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $buyer->name }}" readonly disabled>
 </div>
 <div class="col-md-6">
 <label class="field-label">ประเภทสินค้า</label>
 <select name="product_type" class="field-control" required>
 <option value="durian" @selected(old('product_type') == 'durian')>ทุเรียน</option>
 <option value="bioproduct" @selected(old('product_type') == 'bioproduct')>ผลิตภัณฑ์ชีวมวล</option>
 </select>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ปริมาณที่ต้องการ</label>
 <input type="number" step="0.01" min="0" name="demand_quantity" class="form-control" value="{{ old('demand_quantity') }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ราคา (บาท)</label>
 <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price') }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ความถี่ (เช่น รายเดือน/ตามฤดูกาล)</label>
 <input type="text" name="frequency" class="form-control" value="{{ old('frequency') }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">มีผลตั้งแต่</label>
 <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from') }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">มีผลถึง</label>
 <input type="date" name="valid_to" class="form-control" value="{{ old('valid_to') }}">
 </div>
 </div>
 <div class="col-md-4 d-flex align-items-center">
 <div class="form-check form-switch ps-0 mt-3">
 <input class="form-check-input ms-0" type="checkbox" name="has_loi_mou" value="1" id="has_loi_mou" @checked(old('has_loi_mou'))>
 <label class="form-check-label ms-2" for="has_loi_mou">มี LOI/MOU แล้ว</label>
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
 <a href="{{ route('buyers.show', $buyer) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
