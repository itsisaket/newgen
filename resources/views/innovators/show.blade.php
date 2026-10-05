<x-app-layout :title="'นวัตกร'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $innovator->name }}</h5>
 <p class="text-sm text-secondary mb-0">
 {{ $innovator->household ? 'ครัวเรือน '.$innovator->household->household_code : 'แกนนำภายนอก (ไม่มีครัวเรือน)' }}
 · ลงทะเบียนเมื่อ {{ $innovator->registered_at->format('d/m/Y') }}
 </p>
 </div>
 @if ($innovator->household)
 <a href="{{ route('households.show', $innovator->household) }}" class="btn btn-outline-dark btn-sm mb-0">ดูหน้าครัวเรือน</a>
 @endif
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">การประเมินการยอมรับเทคโนโลยี (ALP) (ระดับการยอมรับเทคโนโลยี)</h6>
 @if ($innovator->household)
 <a href="{{ route('alp-assessments.create', ['household_id' => $innovator->household_id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มการประเมิน ALP</a>
 @endif
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 @if (! $innovator->household)
 <p class="text-sm text-secondary px-3 mb-0">การประเมินการยอมรับเทคโนโลยี (ALP) ผูกกับครัวเรือน — แกนนำที่ไม่มีครัวเรือนยังไม่มีประวัติส่วนนี้</p>
 @else
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">เทคโนโลยี</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ระดับ ALP</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($alpAssessments as $alp)
 <tr>
 <td><span class="text-xs px-2">{{ $alp->assessment_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $alp->technology->name }}</span></td>
 <td class="text-center"><span class="badge badge-sm bg-gradient-info">{{ $alp->alp_level }} — {{ \App\Models\AlpAssessment::LEVEL_LABELS[$alp->alp_level] ?? '' }}</span></td>
 <td class="align-middle text-end"><a href="{{ route('alp-assessments.show', $alp) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a></td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีประวัติการประเมิน ALP</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 @endif
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">การประเมินสมรรถนะนวัตกร (6 มิติ × T0/T1/T2)</h6>
 <a href="{{ route('competency-assessments.create', ['innovator_id' => $innovator->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มการประเมินสมรรถนะ</a>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">รอบ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ผู้ประเมิน</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">คะแนนรวม</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($innovator->competencyAssessments as $assessment)
 <tr>
 <td><span class="badge badge-sm bg-gradient-secondary px-2">{{ $assessment->round }}</span></td>
 <td class="ps-2"><span class="text-xs">{{ $assessment->assessment_date->format('d/m/Y') }}</span></td>
 <td><span class="text-secondary text-xs">{{ $assessment->assessor_type === 'self' ? 'ประเมินตนเอง' : 'ผู้สังเกตการณ์' }}</span></td>
 <td class="text-center"><span class="text-secondary text-xs font-weight-bold">{{ number_format($assessment->scores->sum('score'), 1) }}</span></td>
 <td class="align-middle text-end"><a href="{{ route('competency-assessments.show', $assessment) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a></td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มีการประเมินสมรรถนะ</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">การถ่ายทอดองค์ความรู้</h6>
 <a href="{{ route('knowledge-transfers.create', ['innovator_id' => $innovator->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มการถ่ายทอดความรู้</a>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">หัวข้อ</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ผู้รับ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($innovator->knowledgeTransfers as $transfer)
 <tr>
 <td><span class="text-xs px-2">{{ $transfer->transfer_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $transfer->topic }}</span></td>
 <td class="text-center"><span class="text-secondary text-xs">{{ $transfer->recipient_count }} ({{ $transfer->recipient_type }})</span></td>
 <td class="align-middle text-end"><a href="{{ route('knowledge-transfers.show', $transfer) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a></td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีประวัติการถ่ายทอดความรู้</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <p><a href="{{ route('innovators.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
