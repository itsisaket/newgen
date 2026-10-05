<x-app-layout :title="'การประเมินนวัตกรชุมชน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">การประเมินนวัตกรชุมชน </h6>
 @can('create', \App\Models\InnovatorEvaluation::class)
 <a href="{{ route('innovator-evaluations.create') }}" class="btn btn-sm bg-gradient-success mb-0">+ เพิ่มการประเมิน</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">วันที่ประเมิน</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">คะแนน</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ผล</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ผู้ประเมิน</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($evaluations as $evaluation)
 <tr>
 <td>
 <div class="d-flex px-2 py-1">
 <div class="d-flex flex-column justify-content-center">
 <h6 class="mb-0 text-sm">{{ $evaluation->household->head_name }}</h6>
 <p class="text-xs text-secondary mb-0">{{ $evaluation->household->household_code }}</p>
 </div>
 </div>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs">{{ $evaluation->evaluation_date->format('d/m/Y') }}</span>
 </td>
 <td class="align-middle text-center">
 <span class="text-secondary text-xs font-weight-bold">{{ $evaluation->score ?? '-' }}</span>
 </td>
 <td class="align-middle text-center text-sm">
 <x-status-badge :status="$evaluation->result" />
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs">{{ $evaluation->evaluator->name ?? '-' }}</span>
 </td>
 <td class="align-middle text-end">
 <a href="{{ route('innovator-evaluations.show', $evaluation) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>

 <div class="px-2">
 {{ $evaluations->links() }}
 </div>
</x-app-layout>
