<x-app-layout :title="'บันทึกระยะพัฒนาการ'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึกระยะพัฒนาการทุเรียน</h6>
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

 <form method="POST" action="{{ route('durian-phenology-records.store') }}" novalidate>
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
 <label class="field-label">ระยะพัฒนาการ</label>
 <select name="stage" class="field-control" required>
 <option value="">-- เลือก --</option>
 <option value="flowering" @selected(old('stage') === 'flowering')>แตกใบ/ออกดอก (Flowering)</option>
 <option value="full_bloom" @selected(old('stage') === 'full_bloom')>ดอกบาน (Full Bloom)</option>
 <option value="fruit_set" @selected(old('stage') === 'fruit_set')>ติดผล (Fruit Set)</option>
 <option value="fruit_development" @selected(old('stage') === 'fruit_development')>แต่งผล/พัฒนาผล (Fruit Development)</option>
 </select>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่สำรวจ</label>
 <input type="date" name="observed_date" class="form-control" required value="{{ old('observed_date', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">จำนวนผลคงเหลือ (ผล)</label>
 <input type="number" min="0" name="fruit_count" class="form-control" value="{{ old('fruit_count') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">น้ำหนักเฉลี่ยคาดการณ์ (กก./ผล)</label>
 <input type="number" step="0.01" min="0" name="expected_avg_weight_kg" class="form-control" value="{{ old('expected_avg_weight_kg') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่คาดว่าจะเก็บเกี่ยว</label>
 <input type="date" name="expected_harvest_date" class="form-control" value="{{ old('expected_harvest_date') }}">
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
