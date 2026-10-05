<x-app-layout :title="'ข้อมูลพื้นฐานครัวเรือน'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ข้อมูลพื้นฐานครัวเรือน</h6>
 @can('create', \App\Models\HouseholdBaseline::class)
 <a href="{{ route('household-baselines.create') }}" class="btn btn-sm bg-gradient-success mb-0">+ เพิ่ม Baseline</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ฤดูผลิต</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">ต้นทุนรวม</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">รายได้รวม</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ผู้บันทึก</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($baselines as $baseline)
 <tr>
 <td>
 <div class="d-flex px-2 py-1">
 <div class="d-flex flex-column justify-content-center">
 <h6 class="mb-0 text-sm">{{ $baseline->household->head_name }}</h6>
 <p class="text-xs text-secondary mb-0">{{ $baseline->household->household_code }}</p>
 </div>
 </div>
 </td>
 <td class="ps-2">
 <p class="text-xs font-weight-bold mb-0">{{ $baseline->cropSeason->name }}</p>
 </td>
 <td class="text-end">
 <span class="text-secondary text-xs font-weight-bold">{{ number_format($baseline->total_cost ?? 0, 2) }}</span>
 </td>
 <td class="text-end">
 <span class="text-secondary text-xs font-weight-bold">{{ number_format($baseline->total_income ?? 0, 2) }}</span>
 </td>
 <td class="ps-2">
 <span class="text-secondary text-xs font-weight-bold">{{ $baseline->recordedBy->name }}</span>
 </td>
 <td class="align-middle text-center text-sm">
 <x-status-badge :status="$baseline->status" />
 </td>
 <td class="align-middle text-end">
 <a href="{{ route('household-baselines.show', $baseline) }}" class="text-secondary font-weight-bold text-xs" data-bs-toggle="tooltip">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="7" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>

 <div class="px-2">
 {{ $baselines->links() }}
 </div>
</x-app-layout>
