<x-app-layout :title="'การเดินเตา'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">การเดินเตา (Kiln Production Log)</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">บันทึกใหม่ได้จากหน้ารายละเอียดของครัวเรือนนั้น ๆ เท่านั้น</p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">รหัส Batch</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">เครื่อง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($batches as $batch)
 <tr>
 <td><span class="text-xs font-weight-bold px-2">{{ $batch->batch_code }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $batch->household->head_name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $batch->technologyAsset->asset_code }}</span></td>
 <td><span class="text-secondary text-xs font-weight-bold">{{ $batch->batch_date->format('d/m/Y') }}</span></td>
 <td class="align-middle text-center text-sm"><x-status-badge :status="$batch->status" /></td>
 <td class="align-middle text-end">
 <a href="{{ route('kiln-batches.show', $batch) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
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
 <div class="px-2">{{ $batches->links() }}</div>
</x-app-layout>
