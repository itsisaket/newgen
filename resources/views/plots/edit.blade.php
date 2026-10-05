<x-app-layout :title="'แก้ไขแปลง'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">แก้ไขแปลง — {{ $plot->plot_code }}</h6>
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

 <form method="POST" action="{{ route('plots.update', $plot) }}" novalidate>
 @csrf
 @method('PUT')
 <div class="row g-3">
 <div class="col-md-6">
 {{-- 15 ก.ย. round 2: ย้ายแปลงไปสวนอื่นไม่ได้อีกต่อไปแล้ว -
 UpdatePlotRequest ไม่รับ farm_id เลย (ดู PlotController::update())
 ช่องนี้จึงเป็นข้อมูลอ้างอิงอย่างเดียว ไม่ใช่ฟอร์ม input --}}
 <label class="field-label">สวน (ย้ายไม่ได้)</label>
 <input type="text" class="field-control" value="{{ $plot->farm->farm_name ?? $plot->farm->farm_code }} ({{ $plot->farm->household->head_name }})" readonly disabled>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">รหัสแปลง (Plot Code)</label>
 <input type="text" name="plot_code" class="form-control" value="{{ old('plot_code', $plot->plot_code) }}" required>
 </div>
 </div>
 <div class="col-md-4">
 <label class="field-label">พันธุ์ทุเรียน</label>
 <select name="durian_variety_id" class="field-control">
 <option value="">-- ไม่ระบุ --</option>
 @foreach ($varieties as $variety)
 <option value="{{ $variety->id }}" @selected(old('durian_variety_id', $plot->durian_variety_id) == $variety->id)>{{ $variety->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">พื้นที่ (ไร่)</label>
 <input type="number" step="0.01" min="0" name="area_rai" class="form-control" value="{{ old('area_rai', $plot->area_rai) }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">จำนวนต้น</label>
 <input type="number" min="0" name="tree_count" class="form-control" value="{{ old('tree_count', $plot->tree_count) }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ปีที่ปลูก (พ.ศ./ค.ศ.)</label>
 <input type="number" name="planting_year" class="form-control" value="{{ old('planting_year', $plot->planting_year) }}">
 </div>
 </div>
 <div class="col-md-4">
 <div class="input-group input-group-outline">
 <label class="form-label">ระบบน้ำ</label>
 <input type="text" name="irrigation_type" class="form-control" value="{{ old('irrigation_type', $plot->irrigation_type) }}">
 </div>
 </div>
 <div class="col-md-4">
 <label class="field-label">สถานะ</label>
 <select name="status" class="field-control" required>
 <option value="active" @selected(old('status', $plot->status) === 'active')>ใช้งานอยู่</option>
 <option value="inactive" @selected(old('status', $plot->status) === 'inactive')>ระงับการใช้งาน</option>
 </select>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึกการแก้ไข</button>
 <a href="{{ route('plots.show', $plot) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
