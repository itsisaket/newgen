<x-app-layout :title="'ผู้ซื้อ'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $buyer->name }}</h5>
 <p class="text-sm text-secondary mb-0">{{ $buyer->buyer_type ?? 'ไม่ระบุประเภท' }} · {{ $buyer->phone ?? $buyer->contact ?? '-' }}</p>
 </div>
 <a href="{{ route('market-validations.create', ['buyer_id' => $buyer->id]) }}" class="btn btn-outline-dark btn-sm mb-0">+ บันทึกความต้องการตลาด</a>
 </div>

 <div class="card mb-3">
 <div class="card-body">
 <div class="row">
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">มาตรฐานที่ต้องการ</p>
 <p class="text-sm mb-0">{{ $buyer->standard_required ?? '-' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-3">
 <p class="text-xs text-secondary mb-0">ช่องทางติดต่อ</p>
 <p class="text-sm mb-0">{{ $buyer->contact ?? '-' }}</p>
 </div>
 <div class="col-md-4 col-6 mb-0">
 <p class="text-xs text-secondary mb-0">หมายเหตุ</p>
 <p class="text-sm mb-0">{{ $buyer->notes ?? '-' }}</p>
 </div>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ความต้องการตลาด (Market Validation)</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ประเภทสินค้า</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ปริมาณที่ต้องการ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ราคา</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">LOI/MOU</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($buyer->marketValidations as $mv)
 <tr>
 <td><span class="text-xs px-2">{{ $mv->product_type === 'durian' ? 'ทุเรียน' : 'ผลิตภัณฑ์ชีวมวล' }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $mv->demand_quantity ?? '-' }}</span></td>
 <td><span class="text-secondary text-xs">{{ $mv->price ?? '-' }}</span></td>
 <td class="text-center"><x-status-badge :status="$mv->has_loi_mou ? 'approved' : 'draft'" /></td>
 <td class="align-middle text-end"><a href="{{ route('market-validations.show', $mv) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a></td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูลความต้องการตลาด</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ยอดขายล่าสุด (10 รายการ)</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ประเภทสินค้า</th>
 <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">มูลค่า (บาท)</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($buyer->sales as $sale)
 <tr>
 <td><span class="text-xs px-2">{{ $sale->sale_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $sale->sellerHousehold->head_name }}</span></td>
 <td><span class="text-secondary text-xs">{{ $sale->product_type === 'durian' ? 'ทุเรียน' : 'ผลิตภัณฑ์ชีวมวล' }}</span></td>
 <td class="text-end"><span class="text-sm font-weight-bold">{{ number_format($sale->total_amount, 2) }}</span></td>
 <td class="align-middle text-end"><a href="{{ route('sales.show', $sale) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a></td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มียอดขาย</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <p><a href="{{ route('buyers.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
