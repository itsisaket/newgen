<x-app-layout :title="'การประเมินสมรรถนะนวัตกร'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">การประเมินสมรรถนะนวัตกร (6 มิติ × T0/T1/T2)</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">บันทึกใหม่ได้จากหน้ารายละเอียดของนวัตกรเท่านั้น</p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">นวัตกร</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">รอบ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ผู้ประเมิน</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($assessments as $assessment)
 <tr>
 <td><span class="text-sm px-2">{{ $assessment->innovator->name }}</span></td>
 <td class="ps-2"><span class="badge badge-sm bg-gradient-secondary">{{ $assessment->round }}</span></td>
 <td><span class="text-secondary text-xs">{{ $assessment->assessment_date->format('d/m/Y') }}</span></td>
 <td><span class="text-secondary text-xs">{{ $assessment->assessor_type === 'self' ? 'ประเมินตนเอง' : 'ผู้สังเกตการณ์' }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('competency-assessments.show', $assessment) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $assessments->links() }}</div>
</x-app-layout>
