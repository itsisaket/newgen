<x-app-layout :title="'ผลการประเมินสมรรถนะ'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
 <h6 class="text-white text-capitalize ps-3 mb-0">ผลการประเมินสมรรถนะ ({{ $assessment->round }})</h6>
 </div>
 </div>
 <div class="card-body">
 <div class="row mb-3">
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">นวัตกร</p>
 <p class="text-sm mb-0"><a href="{{ route('innovators.show', $assessment->innovator) }}">{{ $assessment->innovator->name }}</a></p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">วันที่ประเมิน</p>
 <p class="text-sm mb-0">{{ $assessment->assessment_date->format('d/m/Y') }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ประเภทผู้ประเมิน</p>
 <p class="text-sm mb-0">{{ $assessment->assessor_type === 'self' ? 'ประเมินตนเอง' : 'ผู้สังเกตการณ์' }}</p>
 </div>
 <div class="col-md-3 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ผู้ประเมิน</p>
 <p class="text-sm mb-0">{{ $assessment->assessor->name ?? '-' }}</p>
 </div>
 </div>

 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">มิติ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ตัวชี้วัด</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">คะแนน</th>
 </tr>
 </thead>
 <tbody>
 @foreach ($assessment->scores as $score)
 <tr>
 <td><span class="text-xs px-2">{{ $score->indicator->dimensionLabel() }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $score->indicator->name }}</span></td>
 <td class="text-end"><span class="text-sm font-weight-bold">{{ number_format($score->score, 2) }} / {{ $score->indicator->max_score }}</span></td>
 </tr>
 @endforeach
 </tbody>
 <tfoot>
 <tr>
 <td colspan="2" class="text-end text-sm font-weight-bold">รวม</td>
 <td class="text-end text-sm font-weight-bold">{{ number_format($assessment->scores->sum('score'), 2) }}</td>
 </tr>
 </tfoot>
 </table>
 </div>

 @if ($assessment->notes)
 <p class="text-xs text-secondary mt-3 mb-0">หมายเหตุ: {{ $assessment->notes }}</p>
 @endif
 </div>
 </div>
 <p><a href="{{ route('innovators.show', $assessment->innovator) }}" class="text-secondary text-sm">&larr; กลับไปหน้านวัตกร</a></p>
 </div>
 </div>
</x-app-layout>
