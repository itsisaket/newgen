<x-app-layout :title="'เพิ่มเข้าแปลงสาธิต'">
 <div class="row">
 <div class="col-12 col-lg-8">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">เพิ่มแปลงเข้าชุดเปรียบเทียบ </h6>
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

 @if ($groups->isEmpty())
 <p class="text-sm text-danger">
 ยังไม่มีชุดเปรียบเทียบที่กำลังดำเนินการอยู่ กรุณา
 <a href="{{ route('demonstration-comparison-groups.create') }}">สร้างชุดเปรียบเทียบใหม่</a>
 ก่อน
 </p>
 @else
 <form method="POST" action="{{ route('demonstration-plots.store') }}" novalidate>
 @csrf
 <input type="hidden" name="plot_id" value="{{ $plot->id }}">
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">แปลง (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $plot->plot_code }} ({{ $plot->farm->household->head_name }})" readonly disabled>
 </div>
 <div class="col-md-6">
 <label class="field-label">ชุดเปรียบเทียบ</label>
 <select name="comparison_group_id" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach ($groups as $group)
 <option value="{{ $group->id }}" @selected(old('comparison_group_id') == $group->id)>{{ $group->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6">
 <label class="field-label">กลุ่ม</label>
 <select name="group_type" class="field-control" required>
 <option value="">-- เลือก --</option>
 <option value="treatment" @selected(old('group_type') == 'treatment')>Treatment (กลุ่มทดลอง)</option>
 <option value="control" @selected(old('group_type') == 'control')>Control (กลุ่มควบคุม)</option>
 </select>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">เข้าร่วมตั้งแต่วันที่</label>
 <input type="date" name="enrolled_at" class="form-control" required value="{{ old('enrolled_at', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-12">
 <div class="input-group input-group-outline">
 <label class="form-label">หมายเหตุ (ไม่บังคับ)</label>
 <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
 </div>
 </div>
 </div>

 <div class="mt-3">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('plots.show', $plot) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 @endif
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
