<x-app-layout :title="'ประเภทเทคโนโลยี'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $technology->name }}</h5>
 <p class="text-sm text-secondary mb-0">{{ $technology->type ?? '-' }}</p>
 </div>
 </div>

 @if ($technology->description)
 <div class="card mb-3"><div class="card-body"><p class="text-sm mb-0">{{ $technology->description }}</p></div></div>
 @endif

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">เครื่อง/ชุดอุปกรณ์</h6>
 @can('create', \App\Models\TechnologyAsset::class)
 <a href="{{ route('technology-assets.create', ['technology_id' => $technology->id]) }}" class="btn btn-sm bg-white text-dark mb-0">+ ลงทะเบียนเครื่อง</a>
 @endcan
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">รหัสเครื่อง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">สภาพ</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ผู้ถือครองปัจจุบัน</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($technology->assets as $asset)
 <tr>
 <td><h6 class="mb-0 text-sm px-2 py-1">{{ $asset->asset_code }}</h6></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $asset->condition_status }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $asset->currentAssignment()?->household?->head_name ?? '- ยังไม่จัดสรร -' }}</span></td>
 <td class="align-middle text-end">
 <a href="{{ route('technology-assets.show', $asset) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีเครื่องในประเภทนี้</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <p><a href="{{ route('technologies.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
