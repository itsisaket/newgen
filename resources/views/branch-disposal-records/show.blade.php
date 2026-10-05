<x-app-layout :title="'การจัดการกิ่ง'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">CFP — การจัดการกิ่ง/เศษไม้</h5>
 <p class="text-sm text-secondary mb-0">
 {{ $record->plot->plot_code }} · {{ $record->plot->farm->household->head_name }} · {{ $record->disposal_date->format('d/m/Y') }}
 </p>
 </div>
 <x-status-badge :status="$record->status" />
 <x-revision-button :record="$record" type="branch-disposal-record" />
 </div>

 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">เส้นทาง</p>
 <p class="text-sm mb-0">{{ $record->routeLabel() }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">น้ำหนักสด</p>
 <p class="text-sm mb-0">{{ number_format($record->quantity_kg, 2) }} กก.</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ความชื้น</p>
 <p class="text-sm mb-0">{{ $record->moisture_pct !== null ? number_format($record->moisture_pct, 2).' %' : 'ไม่ได้วัด' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">น้ำหนักแห้งโดยประมาณ</p>
 <p class="text-sm mb-0">{{ $record->moisture_pct !== null ? number_format($record->quantity_kg * (1 - $record->moisture_pct / 100), 2).' กก.' : '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">รอบเดินเตา</p>
 <p class="text-sm mb-0">{{ $record->kilnBatch->batch_code ?? '-' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">ผู้บันทึก</p>
 <p class="text-sm mb-0">{{ $record->recordedBy->name }}</p>
 </div>
 @if ($record->note)
 <div class="col-12 mt-3">
 <p class="text-xs text-secondary mb-0">หมายเหตุ</p>
 <p class="text-sm mb-0">{{ $record->note }}</p>
 </div>
 @endif
 @if ($record->rejection_reason)
 <div class="col-12 mt-3">
 <p class="text-xs text-danger mb-0">เหตุผลตีกลับล่าสุด</p>
 <p class="text-sm text-danger mb-0">{{ $record->rejection_reason }}</p>
 </div>
 @endif
 </div>
 </div>
 </div>

 <div class="d-flex gap-2 flex-wrap mb-3">
 @if ($record->status === 'draft')
 <form method="POST" action="{{ route('branch-disposal-records.submit', $record) }}">
 @csrf
 <button class="btn bg-gradient-info btn-sm mb-0">ส่งข้อมูล (Submit)</button>
 </form>
 @endif
 @if ($record->status === 'submitted')
 <form method="POST" action="{{ route('branch-disposal-records.verify', $record) }}">
 @csrf
 <button class="btn bg-gradient-info btn-sm mb-0">ตรวจสอบ (Verify)</button>
 </form>
 @endif
 @if ($record->status === 'verified')
 <form method="POST" action="{{ route('branch-disposal-records.approve', $record) }}">
 @csrf
 <button class="btn bg-gradient-success btn-sm mb-0">อนุมัติ (Approve)</button>
 </form>
 @endif
 @if (in_array($record->status, ['submitted', 'verified']))
 <button type="button" class="btn btn-outline-danger btn-sm mb-0" data-bs-toggle="collapse" data-bs-target="#rejectForm">ตีกลับ (Reject)</button>
 @endif
 </div>

 @if (in_array($record->status, ['submitted', 'verified']))
 <div class="collapse mb-3" id="rejectForm">
 <div class="card col-lg-6 col-12">
 <div class="card-body">
 <form method="POST" action="{{ route('branch-disposal-records.reject', $record) }}">
 @csrf
 <label class="field-label">เหตุผลที่ตีกลับ</label>
 <textarea name="rejection_reason" class="field-control mb-2" required></textarea>
 <button class="btn bg-gradient-danger btn-sm mb-0">ยืนยันตีกลับ</button>
 </form>
 </div>
 </div>
 </div>
 @endif

 <div class="card mb-3">
 <div class="card-body">
 <h6 class="text-sm mb-2">หลักฐาน</h6>
 @forelse ($record->evidences as $evidence)
 <p class="text-xs mb-1 d-flex align-items-center gap-2">
 @if ($evidence->thumbnail_path)
 <img src="{{ route('evidences.download', ['evidence' => $evidence, 'thumbnail' => 1]) }}" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">
 @endif
 <a href="{{ route('evidences.download', $evidence) }}" target="_blank">{{ basename($evidence->file_path) }}</a>
 — {{ $evidence->uploadedBy->name ?? '' }}
 </p>
 @empty
 <p class="text-secondary text-xs mb-2">ยังไม่มีหลักฐาน</p>
 @endforelse

 <form method="POST" action="{{ route('evidences.store') }}" enctype="multipart/form-data" class="mt-2 row g-2 align-items-end">
 @csrf
 <input type="hidden" name="evidenceable_type" value="{{ \App\Models\BranchDisposalRecord::class }}">
 <input type="hidden" name="evidenceable_id" value="{{ $record->id }}">
 <div class="col-sm-8 col-12">
 <input type="file" name="file" class="field-control" required accept="image/*,.pdf">
 </div>
 <div class="col-sm-4 col-12">
 <button class="btn btn-outline-secondary mb-0 w-100">อัปโหลด</button>
 </div>
 </form>
 </div>
 </div>

 <p><a href="{{ route('branch-disposal-records.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
