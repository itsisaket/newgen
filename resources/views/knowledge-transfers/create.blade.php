<x-app-layout :title="'บันทึกการถ่ายทอดความรู้'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">บันทึกการถ่ายทอดองค์ความรู้</h6>
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

 <form method="POST" action="{{ route('knowledge-transfers.store') }}" novalidate>
 @csrf
 <input type="hidden" name="innovator_id" value="{{ $innovator->id }}">
 <div class="row g-3">
 <div class="col-md-6">
 <label class="field-label">นวัตกร (ล็อกจากหน้าที่มาถึง)</label>
 <input type="text" class="field-control" value="{{ $innovator->name }}" readonly disabled>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-outline">
 <label class="form-label">วันที่ถ่ายทอด</label>
 <input type="date" name="transfer_date" class="form-control" required value="{{ old('transfer_date', now()->toDateString()) }}">
 </div>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-outline">
 <label class="form-label">จำนวนผู้รับ (คน)</label>
 <input type="number" min="1" name="recipient_count" class="form-control" required value="{{ old('recipient_count') }}">
 </div>
 </div>
 <div class="col-md-6">
 <div class="input-group input-group-outline">
 <label class="form-label">หัวข้อที่ถ่ายทอด</label>
 <input type="text" name="topic" class="form-control" required value="{{ old('topic') }}">
 </div>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-outline">
 <label class="form-label">ประเภทผู้รับ (เช่น เกษตรกรในพื้นที่)</label>
 <input type="text" name="recipient_type" class="form-control" required value="{{ old('recipient_type') }}">
 </div>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-outline">
 <label class="form-label">สถานที่</label>
 <input type="text" name="location" class="form-control" required value="{{ old('location') }}">
 </div>
 </div>
 </div>

 <div class="mt-4">
 <button class="btn bg-gradient-success mb-0">บันทึก</button>
 <a href="{{ route('innovators.show', $innovator) }}" class="btn btn-outline-secondary mb-0">ยกเลิก</a>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
