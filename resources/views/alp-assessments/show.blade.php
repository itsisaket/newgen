<x-app-layout :title="'ผลการประเมิน ALP'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">ผลการประเมิน ALP</h6>
 </div>
 </div>
 <div class="card-body">
 <div class="row">
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ครัวเรือน</p>
 <p class="text-sm mb-0"><a href="{{ route('households.show', $assessment->household) }}">{{ $assessment->household->head_name }}</a></p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">เทคโนโลยี</p>
 <p class="text-sm mb-0">{{ $assessment->technology->name }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">วันที่ประเมิน</p>
 <p class="text-sm mb-0">{{ $assessment->assessment_date->format('d/m/Y') }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ระดับ ALP</p>
 <p class="text-sm font-weight-bold mb-0">{{ $assessment->alp_level }} — {{ $assessment->levelLabel() }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ผู้ประเมิน</p>
 <p class="text-sm mb-0">{{ $assessment->assessor->name ?? '-' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ผู้บันทึก</p>
 <p class="text-sm mb-0">{{ $assessment->recordedBy->name ?? '-' }}</p>
 </div>
 @if ($assessment->notes)
 <div class="col-12">
 <p class="text-xs text-secondary mb-0">หมายเหตุ</p>
 <p class="text-sm mb-0">{{ $assessment->notes }}</p>
 </div>
 @endif
 </div>
 </div>
 </div>
 <p><a href="{{ route('households.show', $assessment->household) }}" class="text-secondary text-sm">&larr; กลับไปหน้าครัวเรือน</a></p>
 </div>
 </div>
</x-app-layout>
