<x-app-layout :title="'ประเภทเทคโนโลยี'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ประเภทเทคโนโลยี</h6>
 @can('create', \App\Models\Technology::class)
 <a href="{{ route('technologies.create') }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มประเภทเทคโนโลยี</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ชื่อ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ประเภท</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">จำนวนเครื่อง</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($technologies as $technology)
 <tr>
 <td><h6 class="mb-0 text-sm px-2 py-1">{{ $technology->name }}</h6></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $technology->type ?? '-' }}</span></td>
 <td class="text-end"><span class="text-secondary text-xs font-weight-bold">{{ $technology->assets_count }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('technologies.show', $technology) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูล</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $technologies->links() }}</div>
</x-app-layout>
