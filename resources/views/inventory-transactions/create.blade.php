<x-app-layout :title="'บันทึกการเคลื่อนไหวสต็อก'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึกการเคลื่อนไหวสต็อก</h6>
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

 <p class="text-xs text-secondary mb-3">
 สำหรับ <strong>การขาย</strong> ให้ใช้ปุ่ม "+ บันทึกการขาย" ที่หน้าครัวเรือนแทน
 (บันทึกราคา/ผู้ซื้อ/รายได้ครบและตัดสต็อกให้อัตโนมัติ - Blueprint )
 </p>
 <form method="POST" action="{{ route('inventory-transactions.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ครัวเรือน (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $household->head_name }} ({{ $household->household_code }})" readonly disabled>
 <input type="hidden" name="household_id" value="{{ $household->id }}">
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ผลิตภัณฑ์ (คงเหลือปัจจุบัน)</label>
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
 <label class="field-label">ประเภทรายการ</label>
 <select name="transaction_type" class="field-control" required>
 <option value="">-- เลือก --</option>
 <option value="transfer" @selected(old('transaction_type') === 'transfer')>โอนย้าย</option>
 <option value="loss" @selected(old('transaction_type') === 'loss')>สูญเสีย</option>
 @if ($canAdjust)
 <option value="adjustment" @selected(old('transaction_type') === 'adjustment')>ปรับยอด (Adjustment)</option>
 @endif
 </select>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่</label>
 <input type="date" name="transaction_date" class="form-control" required value="{{ old('transaction_date', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ปริมาณ (ปรับยอดใส่ค่าติดลบได้ถ้าต้องการลดยอด)</label>
 <input type="number" step="0.01" name="quantity" class="form-control" required value="{{ old('quantity') }}">
 </div>
 </div>
 <div class="col-12">
 <label class="field-label">หมายเหตุ (บังคับกรอกถ้าเป็นการปรับยอด)</label>
 <textarea name="notes" class="field-control">{{ old('notes') }}</textarea>
 </div>
 </div>
 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('households.show', $household) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
