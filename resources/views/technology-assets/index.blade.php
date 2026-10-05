<x-app-layout :title="'เครื่องเทคโนโลยีทั้งหมด'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">เครื่อง/ชุดอุปกรณ์ทั้งหมด</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">ลงทะเบียนเครื่องใหม่ได้จากหน้าประเภทเทคโนโลยีนั้น ๆ</p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">รหัสเครื่อง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ประเภทเทคโนโลยี</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">สภาพ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ผู้ถือครองปัจจุบัน</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($assets as $asset)
 <tr>
 <td><h6 class="mb-0 text-sm px-2 py-1">{{ $asset->asset_code }}</h6></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $asset->technology->name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $asset->condition_status }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $asset->assignments->first(fn ($a) => $a->status === 'active')?->household?->head_name ?? '- ยังไม่จัดสรร -' }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('technology-assets.show', $asset) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
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
 <div class="px-2">{{ $assets->links() }}</div>
</x-app-layout>
