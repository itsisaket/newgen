<x-app-layout :title="'บันทึกผลการประเมิน ALP'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึก การประเมินการยอมรับเทคโนโลยี (ALP)</h6>
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

 <form method="POST" action="{{ route('alp-assessments.store') }}" novalidate>
 @csrf
 <input type="hidden" name="household_id" value="{{ $household->id }}">
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">ครัวเรือน (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $household->head_name }} ({{ $household->household_code }})" readonly disabled>
 </div>
 <div class="col-md-6">
 <label class="field-label">เทคโนโลยี</label>
 <select name="technology_id" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach ($technologies as $technology)
 <option value="{{ $technology->id }}" @selected(old('technology_id') == $technology->id)>{{ $technology->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่ประเมิน</label>
 <input type="date" name="assessment_date" class="form-control" required value="{{ old('assessment_date', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-6">
 <label class="field-label">ระดับ ALP</label>
 <select name="alp_level" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach (\App\Models\AlpAssessment::LEVEL_LABELS as $level => $label)
 <option value="{{ $level }}" @selected(old('alp_level') == $level)>{{ $level }} — {{ $label }}</option>
 @endforeach
 </select>
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
 <a href="{{ route('households.show', $household) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
