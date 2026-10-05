<x-app-layout :title="'ความต้องการตลาด'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ความต้องการตลาด (Market Validation)</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">บันทึกใหม่ได้จากหน้ารายละเอียดของผู้ซื้อเท่านั้น</p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ผู้ซื้อ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ประเภทสินค้า</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ช่วงที่มีผล</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">LOI/MOU</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($validations as $mv)
 <tr>
 <td><span class="text-sm px-2">{{ $mv->buyer->name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $mv->product_type === 'durian' ? 'ทุเรียน' : 'ผลิตภัณฑ์ชีวมวล' }}</span></td>
 <td><span class="text-secondary text-xs">{{ $mv->valid_from?->format('d/m/Y') }} - {{ $mv->valid_to?->format('d/m/Y') ?? '-' }}</span></td>
 <td class="text-center"><x-status-badge :status="$mv->has_loi_mou ? 'approved' : 'draft'" /></td>
 <td class="align-middle text-end">
 <a href="{{ route('market-validations.show', $mv) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
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
 <div class="px-2">{{ $validations->links() }}</div>
</x-app-layout>
