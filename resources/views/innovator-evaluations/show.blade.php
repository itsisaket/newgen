<x-app-layout :title="'รายละเอียดการประเมิน'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $evaluation->household->head_name }}</h5>
 <p class="text-sm text-secondary mb-0">
 รหัส {{ $evaluation->household->household_code }}
 · ประเมินวันที่ {{ $evaluation->evaluation_date->format('d/m/Y') }}
 </p>
 </div>
 <x-status-badge :status="$evaluation->result" />
 </div>

 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">คะแนนรวม</p>
 <p class="text-sm font-weight-bold mb-0">{{ $evaluation->score ?? '-' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ผู้ประเมิน</p>
 <p class="text-sm mb-0">{{ $evaluation->evaluator->name ?? '-' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">สถานะบัญชีเกษตรกรปัจจุบัน</p>
 <p class="text-sm mb-0">
 @if ($evaluation->household->user?->isInnovator())
 นวัตกรชุมชน
 @elseif ($evaluation->household->user)
 เกษตรกร
 @else
 ยังไม่มีบัญชี
 @endif
 </p>
 </div>
 @if ($evaluation->notes)
 <div class="col-12 mt-2">
 <p class="text-xs text-secondary mb-0">บันทึกเพิ่มเติม</p>
 <p class="text-sm mb-0">{{ $evaluation->notes }}</p>
 </div>
 @endif
 </div>
 </div>
 </div>

 <p>
 <a href="{{ route('households.show', $evaluation->household) }}" class="text-secondary text-sm">&larr; ไปหน้าครัวเรือน</a>
 &nbsp;·&nbsp;
 <a href="{{ route('innovator-evaluations.index') }}" class="text-secondary text-sm">กลับไปหน้ารายการ</a>
 </p>
 </div>
 </div>
</x-app-layout>
