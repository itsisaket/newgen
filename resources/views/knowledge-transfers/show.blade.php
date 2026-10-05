<x-app-layout :title="'การถ่ายทอดความรู้'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">{{ $transfer->topic }}</h6>
 </div>
 </div>
 <div class="card-body">
 <div class="row mb-3">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">นวัตกร</p>
 <p class="text-sm mb-0"><a href="{{ route('innovators.show', $transfer->innovator) }}">{{ $transfer->innovator->name }}</a></p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">วันที่ถ่ายทอด</p>
 <p class="text-sm mb-0">{{ $transfer->transfer_date->format('d/m/Y') }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ผู้รับ</p>
 <p class="text-sm mb-0">{{ $transfer->recipient_count }} คน ({{ $transfer->recipient_type }})</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">สถานที่</p>
 <p class="text-sm mb-0">{{ $transfer->location }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">บันทึกโดย</p>
 <p class="text-sm mb-0">{{ $transfer->recordedBy->name ?? '-' }}</p>
 </div>
 </div>

 <hr class="horizontal dark my-3">

 <h6 class="text-sm mb-2">หลักฐาน</h6>
 @forelse ($transfer->evidences as $evidence)
 <p class="text-xs mb-1 d-flex align-items-center gap-2">
 @if ($evidence->thumbnail_path)
 <img src="{{ route('evidences.download', ['evidence' => $evidence, 'thumbnail' => 1]) }}" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">
 @endif
 <a href="{{ route('evidences.download', $evidence) }}" target="_blank">{{ basename($evidence->file_path) }}</a>
 </p>
 @empty
 <p class="text-secondary text-xs mb-2">ยังไม่มีหลักฐาน</p>
 @endforelse
 <form method="POST" action="{{ route('evidences.store') }}" enctype="multipart/form-data" class="mt-2 row g-2 align-items-end">
 @csrf
 <input type="hidden" name="evidenceable_type" value="{{ \App\Models\KnowledgeTransfer::class }}">
 <input type="hidden" name="evidenceable_id" value="{{ $transfer->id }}">
 <div class="col-sm-8 col-12">
 <input type="file" name="file" class="field-control" required accept="image/*,.pdf">
 </div>
 <div class="col-sm-4 col-12">
 <button class="btn btn-outline-secondary mb-0 w-100">อัปโหลด</button>
 </div>
 </form>
 </div>
 </div>
 <p><a href="{{ route('knowledge-transfers.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
