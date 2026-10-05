<x-app-layout :title="'บันทึกการขาย'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึก การขาย</h6>
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

 <form method="POST" action="{{ route('sales.store') }}" id="sale-form" novalidate>
 @csrf
 <input type="hidden" name="seller_household_id" value="{{ $household->id }}">
 <div class="row g-3">
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ครัวเรือน (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $household->head_name }} ({{ $household->household_code }})" readonly disabled>
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ผู้ซื้อ</label>
 <select name="buyer_id" class="field-control">
 <option value="">-- ไม่ระบุ --</option>
 @foreach ($buyers as $buyer)
 <option value="{{ $buyer->id }}" @selected(old('buyer_id') == $buyer->id)>{{ $buyer->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ฤดูผลิต</label>
 <select name="crop_season_id" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach ($cropSeasons as $season)
 <option value="{{ $season->id }}" @selected(old('crop_season_id') == $season->id)>{{ $season->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ประเภทสินค้า</label>
 <select name="product_type" id="product_type" class="field-control" required>
 <option value="durian" @selected(old('product_type') == 'durian')>ทุเรียน</option>
 <option value="bioproduct" @selected(old('product_type') == 'bioproduct')>ผลิตภัณฑ์ชีวมวล</option>
 </select>
 </div>
 <div class="col-md-6 col-lg-4" id="product-field" style="display:none;">
 <label class="field-label">ผลิตภัณฑ์ (คงเหลือปัจจุบัน)</label>
 <select name="product_id" class="field-control">
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
 <label class="form-label">วันที่ขาย</label>
 <input type="date" name="sale_date" class="form-control" required value="{{ old('sale_date', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ปริมาณ</label>
 <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" required value="{{ old('quantity') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ราคาต่อหน่วย (บาท)</label>
 <input type="number" step="0.01" min="0" name="unit_price" class="form-control" required value="{{ old('unit_price') }}">
 </div>
 </div>
 <div class="col-12">
 <div class="input-group input-group-outline">
 <label class="form-label">หมายเหตุ</label>
 <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
 </div>
 </div>
 </div>
 <p class="text-xs text-secondary mt-2 mb-0">ระบบจะคำนวณมูลค่ารวม (ปริมาณ × ราคาต่อหน่วย) ให้อัตโนมัติ และตัดสต็อกให้เองถ้าเป็นการขายผลิตภัณฑ์ชีวมวล</p>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('households.show', $household) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>

 @push('scripts')
 <script>
 (function () {
 var typeSelect = document.getElementById('product_type');
 var productField = document.getElementById('product-field');
 if (!typeSelect || !productField) return;
 function sync() {
 productField.style.display = typeSelect.value === 'bioproduct' ? '' : 'none';
 }
 typeSelect.addEventListener('change', sync);
 sync();
 })();
 </script>
 @endpush
</x-app-layout>
