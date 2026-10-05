<x-app-layout :title="'ยอดขาย'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ยอดขายจริง (Sales)</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">บันทึกใหม่ได้จากหน้ารายละเอียดของครัวเรือนเท่านั้น</p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ผู้ซื้อ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ประเภทสินค้า</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">มูลค่า (บาท)</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($sales as $sale)
 <tr>
 <td><span class="text-xs px-2">{{ $sale->sale_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $sale->sellerHousehold->head_name }}</span></td>
 <td><span class="text-secondary text-xs">{{ $sale->buyer->name ?? '-' }}</span></td>
 <td><span class="text-secondary text-xs">{{ $sale->product_type === 'durian' ? 'ทุเรียน' : ($sale->product->name ?? 'ผลิตภัณฑ์ชีวมวล') }}</span></td>
 <td class="text-end"><span class="text-sm font-weight-bold">{{ number_format($sale->total_amount, 2) }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('sales.show', $sale) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
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
 <div class="px-2">{{ $sales->links() }}</div>
</x-app-layout>
