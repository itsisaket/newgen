<x-app-layout :title="'บันทึกการเดินเตา'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึกการเดินเตา</h6>
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

 @if ($assets->isEmpty())
 <p class="text-sm text-danger">
 ครัวเรือนนี้ยังไม่มีเครื่องเทคโนโลยีที่ได้รับจัดสรร กรุณาจัดสรรเครื่องให้ครัวเรือนนี้ก่อน
 จึงจะบันทึกการเดินเตาได้
 </p>
 @else
 <form method="POST" action="{{ route('kiln-batches.store') }}" id="kiln-batch-form" novalidate>
 @csrf
 <input type="hidden" name="household_id" value="{{ $household->id }}">
 <div class="row g-3">
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ครัวเรือน (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $household->head_name }} ({{ $household->household_code }})" readonly disabled>
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">เครื่อง (ที่ได้รับจัดสรร)</label>
 <select name="technology_asset_id" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach ($assets as $asset)
 <option value="{{ $asset->id }}" @selected(old('technology_asset_id') == $asset->id)>{{ $asset->asset_code }} ({{ $asset->technology->name }})</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">รหัส Batch (เช่น KB-0001)</label>
 <input type="text" name="batch_code" class="form-control" value="{{ old('batch_code') }}" required>
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่เดินเตา</label>
 <input type="date" name="batch_date" class="form-control" required value="{{ old('batch_date', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">วัตถุดิบเข้า (กก.)</label>
 <input type="number" step="0.01" min="0" name="biomass_input_kg" class="form-control" required value="{{ old('biomass_input_kg') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">เวลาที่ใช้ผลิต (ชม.)</label>
 <input type="number" step="0.01" min="0" name="production_time_hours" class="form-control" value="{{ old('production_time_hours') }}">
 </div>
 </div>
 </div>

 <hr class="horizontal dark my-4">

 <div class="row g-3">
 <div class="col-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ค่าแรง (บาท)</label>
 <input type="number" step="0.01" min="0" name="labor_cost" class="form-control" value="{{ old('labor_cost') }}">
 </div>
 </div>
 <div class="col-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ค่าพลังงาน (บาท)</label>
 <input type="number" step="0.01" min="0" name="energy_cost" class="form-control" value="{{ old('energy_cost') }}">
 </div>
 </div>
 <div class="col-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ค่าใช้จ่ายอื่น ๆ (บาท)</label>
 <input type="number" step="0.01" min="0" name="other_cost" class="form-control" value="{{ old('other_cost') }}">
 </div>
 </div>
 </div>

 <hr class="horizontal dark my-4">

 <h6 class="text-sm">ผลผลิตที่ได้จาก Batch นี้</h6>
 <div id="outputs-wrap">
 <div class="row g-3 output-row mb-2">
 <div class="col-md-4">
 <label class="field-label">ผลิตภัณฑ์</label>
 <select name="outputs[0][product_id]" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach ($products as $product)
 <option value="{{ $product->id }}" data-unit="{{ $product->unit }}">{{ $product->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-outline">
 <label class="form-label">ปริมาณ</label>
 <input type="number" step="0.01" min="0" name="outputs[0][output_quantity]" class="form-control" required>
 </div>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-outline">
 <label class="form-label">หน่วย</label>
 <input type="text" name="outputs[0][unit]" class="form-control" required>
 </div>
 </div>
 <div class="col-md-2">
 <div class="input-group input-group-outline">
 <label class="form-label">เกรด</label>
 <input type="text" name="outputs[0][quality_grade]" class="form-control">
 </div>
 </div>
 </div>
 </div>
 <button type="button" class="btn btn-outline-dark btn-sm mb-0" id="add-output">+ เพิ่มผลิตภัณฑ์อีกรายการ</button>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึกร่าง</button>
 <a href="{{ route('households.show', $household) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 @endif
 </div>
 </div>
 </div>
 </div>

 @push('scripts')
 <script>
 (function () {
 var wrap = document.getElementById('outputs-wrap');
 var addBtn = document.getElementById('add-output');
 if (!wrap || !addBtn) return;
 var template = wrap.querySelector('.output-row').cloneNode(true);
 var idx = 1;
 addBtn.addEventListener('click', function () {
 var row = template.cloneNode(true);
 row.querySelectorAll('input, select').forEach(function (el) {
 var name = el.getAttribute('name');
 if (name) {
 el.setAttribute('name', name.replace(/outputs\[\d+\]/, 'outputs[' + idx + ']'));
 }
 if (el.tagName === 'SELECT') { el.selectedIndex = 0; } else { el.value = ''; }
 });
 wrap.appendChild(row);
 row.dispatchEvent(new CustomEvent('drfis:enhance-selects', { bubbles: true }));
 idx++;
 });
 wrap.addEventListener('change', function (e) {
 if (e.target.tagName === 'SELECT') {
 var opt = e.target.selectedOptions[0];
 var unit = opt ? opt.getAttribute('data-unit') : null;
 if (unit) {
 var unitInput = e.target.closest('.output-row').querySelector('input[name*="[unit]"]');
 if (unitInput && !unitInput.value) { unitInput.value = unit; }
 }
 }
 });
 })();
 </script>
 @endpush
</x-app-layout>
