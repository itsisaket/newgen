<x-app-layout :title="'เครื่อง'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $asset->asset_code }}</h5>
 <p class="text-sm text-secondary mb-0">{{ $asset->technology->name }} · สภาพ: {{ $asset->condition_status }}</p>
 </div>
 @if (! $asset->currentAssignment())
 @can('create', \App\Models\TechnologyAssignment::class)
 <a href="{{ route('technology-assignments.create', ['technology_asset_id' => $asset->id]) }}" class="btn btn-sm bg-gradient-success mb-0">+ จัดสรรให้ครัวเรือน</a>
 @endcan
 @endif
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
 <h6 class="text-white text-capitalize mb-0">ประวัติการจัดสรร</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ครัวเรือน</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">วันที่จัดสรร</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">วันที่คืน</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($asset->assignments as $assignment)
 <tr>
 <td><span class="text-xs font-weight-bold px-2">{{ $assignment->household->head_name }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $assignment->assigned_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $assignment->returned_date?->format('d/m/Y') ?? '-' }}</span></td>
 <td class="align-middle text-center text-sm"><x-status-badge :status="$assignment->status === 'active' ? 'active' : 'inactive'" /></td>
 <td class="align-middle text-end">
 @if ($assignment->status === 'active')
 <form method="POST" action="{{ route('technology-assignments.return', $assignment) }}" class="d-inline">
 @csrf
 <button class="btn btn-outline-secondary btn-sm mb-0">รับคืนเครื่อง</button>
 </form>
 @endif
 </td>
 </tr>
 @empty
 <tr><td colspan="5" class="text-center text-secondary text-sm py-4">ยังไม่เคยจัดสรร</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 px-3">
 <h6 class="text-white text-capitalize mb-0">การเดินเตาล่าสุด </h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">วันที่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สถานะ</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($asset->kilnBatches as $batch)
 <tr>
 <td><span class="text-secondary text-xs font-weight-bold px-2">{{ $batch->batch_date->format('d/m/Y') }}</span></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $batch->household->head_name }}</span></td>
 <td class="align-middle text-center text-sm"><x-status-badge :status="$batch->status" /></td>
 <td class="align-middle text-end">
 <a href="{{ route('kiln-batches.show', $batch) }}" class="text-secondary font-weight-bold text-xs">แสดงรายละเอียด</a>
 </td>
 </tr>
 @empty
 <tr><td colspan="4" class="text-center text-secondary text-sm py-4">ยังไม่มีการเดินเตาด้วยเครื่องนี้</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <p><a href="{{ route('technologies.show', $asset->technology) }}" class="text-secondary text-sm">&larr; กลับไปหน้าประเภทเทคโนโลยี</a></p>
 </div>
 </div>
</x-app-layout>
