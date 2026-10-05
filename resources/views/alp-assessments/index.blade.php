<x-app-layout :title="'การประเมินการยอมรับเทคโนโลยี (ALP)'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">การประเมินการยอมรับเทคโนโลยี (ALP) (ระดับการยอมรับเทคโนโลยี)</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">บันทึกใหม่ได้จากหน้ารายละเอียดของครัวเรือน/นวัตกรเท่านั้น</p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">เทคโนโลยี</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ระดับ ALP</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($assessments as $assessment)
 <tr>
 <td><span class="text-xs px-2">{{ $assessment->assessment_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $assessment->household->head_name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $assessment->technology->name }}</span></td>
 <td class="text-center"><span class="badge badge-sm bg-gradient-info">{{ $assessment->alp_level }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('alp-assessments.show', $assessment) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
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
