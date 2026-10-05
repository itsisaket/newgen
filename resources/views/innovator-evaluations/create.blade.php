<x-app-layout :title="'เพิ่มการประเมินนวัตกรชุมชน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">เพิ่มการประเมินนวัตกรชุมชน </h6>
 </div>
 </div>
 <div class="card-body">
 <p class="text-sm text-secondary">
 แบบประเมินแบบย่อสำหรับเลื่อนสถานะเกษตรกร &rarr; นวัตกรชุมชน — ถ้าผลเป็น
 "ผ่าน" ระบบจะเพิ่มสิทธิ์นวัตกรชุมชนให้บัญชีเกษตรกรของครัวเรือนนี้โดยอัตโนมัติทันที
 </p>

 @if ($errors->any())
 <div class="alert alert-danger text-white">
 <ul class="mb-0 ps-3">
 @foreach ($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <form method="POST" action="{{ route('innovator-evaluations.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">ครัวเรือน</label>
 <select name="household_id" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach ($households as $household)
 <option value="{{ $household->id }}" @selected(old('household_id', $selectedHouseholdId) == $household->id)>
 {{ $household->head_name }} ({{ $household->household_code }})
 </option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6">
 <label class="field-label">วันที่ประเมิน</label>
 <input type="date" name="evaluation_date" class="field-control" required value="{{ old('evaluation_date', now()->toDateString()) }}">
 </div>
 <div class="col-md-6 col-lg-4">
 <div class="input-group input-group-outline">
 <label class="form-label">คะแนนรวม (0-100 ถ้ามี)</label>
 <input type="number" step="0.01" min="0" max="100" name="score" class="form-control" value="{{ old('score') }}">
 </div>
 </div>
 <div class="col-md-6 col-lg-4">
 <label class="field-label">ผลการประเมิน</label>
 <select name="result" class="field-control" required>
 <option value="">-- เลือก --</option>
 <option value="pass" @selected(old('result') == 'pass')>ผ่าน</option>
 <option value="fail" @selected(old('result') == 'fail')>ไม่ผ่าน</option>
 </select>
 </div>
 <div class="col-12">
 <label class="field-label">บันทึกเพิ่มเติม</label>
 <textarea name="notes" class="field-control" rows="4">{{ old('notes') }}</textarea>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึกผลการประเมิน</button>
 <a href="{{ route('innovator-evaluations.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
