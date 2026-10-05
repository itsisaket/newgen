<x-app-layout :title="'ผลิตภัณฑ์ชีวมวล'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ประเภทผลิตภัณฑ์ชีวมวล</h6>
 @can('create', \App\Models\Product::class)
 <a href="{{ route('products.create') }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มประเภทผลิตภัณฑ์</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ชื่อ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">หน่วย</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($products as $product)
 <tr>
 <td><h6 class="mb-0 text-sm px-2 py-1">{{ $product->name }}</h6></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $product->unit }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('products.show', $product) }}" class="text-secondary font-weight-bold text-xs">ดูสต็อก</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="3" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $products->links() }}</div>
</x-app-layout>
