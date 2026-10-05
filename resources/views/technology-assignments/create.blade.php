<x-app-layout :title="'จัดสรรเครื่อง'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">จัดสรรเครื่องให้ครัวเรือน </h6>
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
 <form method="POST" action="{{ route('technology-assignments.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">เครื่อง (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $asset->asset_code }} ({{ $asset->technology->name }})" readonly disabled>
 <input type="hidden" name="technology_asset_id" value="{{ $asset->id }}">
 </div>
 <div class="col-md-6">
 <label class="field-label">ครัวเรือน</label>
 <select name="household_id" class="field-control" required>
 <option value="">-- เลือกครัวเรือน --</option>
 @foreach ($households as $household)
 <option value="{{ $household->id }}" @selected(old('household_id') == $household->id)>{{ $household->head_name }} ({{ $household->household_code }})</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่จัดสรร</label>
 <input type="date" name="assigned_date" class="form-control" required value="{{ old('assigned_date', now()->toDateString()) }}">
 </div>
 </div>
 </div>
 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('technology-assets.show', $asset) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
