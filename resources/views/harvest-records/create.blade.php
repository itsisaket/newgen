<x-app-layout :title="'บันทึกผลผลิต'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึกผลผลิตที่เก็บเกี่ยวจริง</h6>
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

 <form method="POST" action="{{ route('harvest-records.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6 col-lg-4">
 <label class="field-label">แปลง (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $plot->plot_code }} ({{ $plot->farm->household->head_name }})" readonly disabled>
 <input type="hidden" name="plot_id" value="{{ $plot->id }}">
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
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่เก็บเกี่ยว</label>
 <input type="date" name="harvest_date" class="form-control" required value="{{ old('harvest_date', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">น้ำหนักจริง (กก.)</label>
 <input type="number" step="0.01" min="0" name="actual_weight_kg" class="form-control" required value="{{ old('actual_weight_kg') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">เกรด (เช่น AA, A, B)</label>
 <input type="text" name="grade" class="form-control" value="{{ old('grade') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ราคา/กก. (บาท)</label>
 <input type="number" step="0.01" min="0" name="price_per_kg" class="form-control" value="{{ old('price_per_kg') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ผู้ซื้อ (ถ้ามี)</label>
 <select name="buyer_id" class="field-control">
 <option value="">-- ไม่ระบุ --</option>
 @foreach ($buyers as $buyer)
 <option value="{{ $buyer->id }}" @selected(old('buyer_id') == $buyer->id)>{{ $buyer->name }}</option>
 @endforeach
 </select>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึกร่าง</button>
 <a href="{{ route('plots.show', $plot) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
