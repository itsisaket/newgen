<x-app-layout :title="'แก้ไข ข้อมูลพื้นฐานครัวเรือน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">แก้ไข ข้อมูลพื้นฐานครัวเรือน</h6>
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

 <form method="POST" action="{{ route('household-baselines.update', $baseline) }}" novalidate>
 @csrf
 @method('PUT')
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">ครัวเรือน</label>
 <select name="household_id" class="field-control" required>
 @foreach ($households as $household)
 <option value="{{ $household->id }}" @selected(old('household_id', $baseline->household_id) == $household->id)>
 {{ $household->head_name }} ({{ $household->household_code }})
 </option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6">
 <label class="field-label">ฤดูผลิต (Baseline)</label>
 <select name="crop_season_id" class="field-control" required>
 @foreach ($cropSeasons as $season)
 <option value="{{ $season->id }}" @selected(old('crop_season_id', $baseline->crop_season_id) == $season->id)>{{ $season->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6 col-lg-3">
 <div class="input-group input-group-outline">
 <label class="form-label">ต้นทุนรวม (บาท)</label>
 <input type="number" step="0.01" min="0" name="total_cost" class="form-control" value="{{ old('total_cost', $baseline->total_cost) }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-3">
 <div class="input-group input-group-outline">
 <label class="form-label">รายได้รวม (บาท)</label>
 <input type="number" step="0.01" min="0" name="total_income" class="form-control" value="{{ old('total_income', $baseline->total_income) }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-3">
 <div class="input-group input-group-outline">
 <label class="form-label">พื้นที่ปลูกช่วง Baseline (ไร่)</label>
 <input type="number" step="0.01" min="0" name="baseline_area_rai" class="form-control" value="{{ old('baseline_area_rai', $baseline->baseline_area_rai) }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-3">
 <div class="input-group input-group-outline">
 <label class="form-label">ผลผลิตช่วง Baseline (กก.)</label>
 <input type="number" step="0.01" min="0" name="baseline_harvest_kg" class="form-control" value="{{ old('baseline_harvest_kg', $baseline->baseline_harvest_kg) }}">
 </div>
 </div>
 <div class="col-12">
 <label class="field-label">บันทึกการจัดการเดิม</label>
 <textarea name="management_practice_notes" class="field-control" rows="4">{{ old('management_practice_notes', $baseline->management_practice_notes) }}</textarea>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึกการแก้ไข</button>
 <a href="{{ route('household-baselines.show', $baseline) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
