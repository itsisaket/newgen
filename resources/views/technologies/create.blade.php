<x-app-layout :title="'เพิ่มประเภทเทคโนโลยี'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">เพิ่มประเภทเทคโนโลยี </h6>
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
 <form method="POST" action="{{ route('technologies.store') }}" novalidate>
 @csrf
 <div class="row g-3">
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ชื่อเทคโนโลยี (เช่น เตาผลิตถ่านชีวภาพ)</label>
 <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">ประเภท (เช่น เตาชีวมวล, ถังกลั่น)</label>
 <input type="text" name="type" class="form-control" value="{{ old('type') }}">
 </div>
 </div>
 <div class="col-12">
 <label class="field-label">รายละเอียด</label>
 <textarea name="description" class="field-control">{{ old('description') }}</textarea>
 </div>
 </div>
 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('technologies.index') }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
