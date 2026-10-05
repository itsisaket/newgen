<x-app-layout :title="'ฤดูผลิต'">
 <div class="row">
 <div class="col-12">
 <div class="card my-4">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ฤดูผลิต (Crop Season)</h6>
 @can('create', \App\Models\CropSeason::class)
 <a href="{{ route('crop-seasons.create') }}" class="btn btn-sm bg-white text-dark mb-0">+ เพิ่มฤดูผลิต</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 @if (session('status'))
 <div class="alert alert-success text-white mx-3">{{ session('status') }}</div>
 @endif
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ชื่อฤดูผลิต</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">เริ่ม</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สิ้นสุด</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($seasons as $season)
 <tr>
 <td><h6 class="mb-0 text-sm px-2 py-1">{{ $season->name }}</h6></td>
 <td><span class="text-secondary text-xs">{{ $season->start_date->format('d/m/Y') }}</span></td>
 <td><span class="text-secondary text-xs">{{ $season->end_date->format('d/m/Y') }}</span></td>
 <td>
 @if ($season->status === 'active')
 <span class="badge badge-sm bg-gradient-success">กำลังดำเนินการ</span>
 @elseif ($season->status === 'upcoming')
 <span class="badge badge-sm bg-gradient-secondary">ยังไม่เริ่ม</span>
 @else
 <span class="badge badge-sm bg-gradient-dark">ปิดแล้ว</span>
 @endif
 </td>
 <td class="align-middle text-end">
 @can('update', \App\Models\CropSeason::class)
 <a href="{{ route('crop-seasons.edit', $season) }}" class="text-secondary font-weight-bold text-xs">แก้ไข</a>
 @endcan
 </td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่มีข้อมูลฤดูผลิต</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 <div class="px-2">{{ $seasons->links() }}</div>
</x-app-layout>
