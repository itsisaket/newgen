<x-app-layout :title="'บันทึกการประเมินสมรรถนะ'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึก การประเมินสมรรถนะนวัตกร</h6>
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

 @if ($indicatorsByDimension->isEmpty())
 <p class="text-sm text-danger">ยังไม่มีตัวชี้วัดสมรรถนะในระบบ (ต้องรัน seeder <code>CompetencyIndicatorSeeder</code> ก่อน)</p>
 @else
 <form method="POST" action="{{ route('competency-assessments.store') }}" novalidate>
 @csrf
 <input type="hidden" name="innovator_id" value="{{ $innovator->id }}">
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">นวัตกร (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $innovator->name }}" readonly disabled>
 </div>
 <div class="col-md-3">
 <label class="field-label">รอบการประเมิน</label>
 <select name="round" class="field-control" required>
 <option value="T0" @selected(old('round') == 'T0')>T0 — ก่อนพัฒนา</option>
 <option value="T1" @selected(old('round') == 'T1')>T1 — หลังการฝึก/ทดลอง</option>
 <option value="T2" @selected(old('round') == 'T2')>T2 — หลังใช้จริง</option>
 </select>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่ประเมิน</label>
 <input type="date" name="assessment_date" class="form-control" required value="{{ old('assessment_date', now()->toDateString()) }}">
 </div>
 </div>

 @if ($isSelfOnly)
 <div class="col-12">
 <p class="text-xs text-secondary mb-0">แบบนี้เป็นการ <strong>ประเมินตนเอง (Self-assessment)</strong> — ระบบจะบันทึกชื่อคุณเป็นผู้ประเมินอัตโนมัติ</p>
 </div>
 @else
 <div class="col-md-6">
 <label class="field-label">ประเภทผู้ประเมิน</label>
 <select name="assessor_type" class="field-control" required>
 <option value="observer" @selected(old('assessor_type', 'observer') == 'observer')>ผู้สังเกตการณ์ (Observer)</option>
 <option value="self" @selected(old('assessor_type') == 'self')>ประเมินตนเอง (Self)</option>
 </select>
 </div>
 @endif
 </div>

 <hr class="horizontal dark my-4">

 @foreach ($indicatorsByDimension as $dimension => $indicators)
 <h6 class="text-sm mt-3">{{ \App\Models\CompetencyIndicator::DIMENSION_LABELS[$dimension] ?? $dimension }}</h6>
 <div class="row g-3 mb-2">
 @foreach ($indicators as $indicator)
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">{{ $indicator->name }} (เต็ม {{ $indicator->max_score }})</label>
 <input type="number" step="0.01" min="0" max="{{ $indicator->max_score }}" name="scores[{{ $indicator->id }}]" class="form-control" required value="{{ old('scores.'.$indicator->id) }}">
 </div>
 </div>
 @endforeach
 </div>
 @endforeach

 <div class="col-12 mt-3">
 <div class="input-group input-group-outline">
 <label class="form-label">หมายเหตุ</label>
 <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('innovators.show', $innovator) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 @endif
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
