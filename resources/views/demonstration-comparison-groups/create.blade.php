<x-app-layout :title="'สร้างชุดเปรียบเทียบแปลงสาธิต'">
 <div class="row">
 <div class="col-12 col-lg-8">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">สร้างชุดเปรียบเทียบแปลงสาธิต </h6>
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

 <form method="POST" action="{{ route('demonstration-comparison-groups.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-12">
 <div class="input-group input-group-outline">
 <label class="form-label">ชื่อชุดเปรียบเทียบ (เช่น เปรียบเทียบเตาชีวมวลรุ่น A ปี 2569)</label>
 <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
 </div>
 </div>
 <div class="col-md-6">
 <label class="field-label">ฤดูกาลผลิต</label>
 <select name="crop_season_id" class="field-control" required>
 <option value="">-- เลือก --</option>
 @foreach ($cropSeasons as $season)
 <option value="{{ $season->id }}" @selected(old('crop_season_id') == $season->id)>{{ $season->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-md-6">
 <label class="field-label">เทคโนโลยี/นวัตกรรมที่สาธิต (ไม่บังคับ)</label>
 <select name="technology_id" class="field-control">
 <option value="">-- ไม่ระบุ --</option>
 @foreach ($technologies as $technology)
 <option value="{{ $technology->id }}" @selected(old('technology_id') == $technology->id)>{{ $technology->name }}</option>
 @endforeach
 </select>
 </div>
 <div class="col-12">
 <div class="input-group input-group-outline">
 <label class="form-label">วัตถุประสงค์ (ไม่บังคับ)</label>
 <input type="text" name="objective" class="form-control" value="{{ old('objective') }}">
 </div>
 </div>
 </div>

 <div class="mt-3">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('demonstration-comparison-groups.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
