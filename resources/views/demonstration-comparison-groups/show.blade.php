<x-app-layout :title="'ชุดเปรียบเทียบแปลงสาธิต'">
 <div class="row">
 <div class="col-12">
 <div class="d-flex justify-content-between align-items-start mb-3">
 <div>
 <h5 class="mb-1">{{ $group->name }}</h5>
 <p class="text-sm text-secondary mb-0">
 ฤดูกาล: {{ $group->cropSeason->name }}
 @if ($group->technology)
 · เทคโนโลยี: {{ $group->technology->name }}
 @endif
 · ผู้สร้าง: {{ $group->createdBy->name }}
 </p>
 </div>
 <div class="d-flex align-items-center gap-2">
 <x-status-badge :status="$group->status" />
 @can('update', $group)
 @if ($group->status === 'active')
 <form method="POST" action="{{ route('demonstration-comparison-groups.complete', $group) }}">
 @csrf
 <button class="btn btn-outline-dark btn-sm mb-0">ปิดชุดเปรียบเทียบ (เสร็จสิ้น)</button>
 </form>
 @endif
 @endcan
 </div>
 </div>

 @if ($group->objective)
 <div class="card mb-3">
 <div class="card-body">
 <p class="text-xs text-secondary mb-0">วัตถุประสงค์</p>
 <p class="text-sm mb-0">{{ $group->objective }}</p>
 </div>
 </div>
 @endif

 <div class="card mb-3">
 <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
 <div class="bg-gradient-gold shadow-gold border-radius-lg pt-4 pb-3 px-3">
 <h6 class="text-white text-capitalize mb-0">แปลงที่เข้าร่วม</h6>
 </div>
 </div>
 <div class="card-body px-0 pb-2">
 <p class="text-xs text-secondary px-3 mb-2">
 เพิ่มแปลงเข้าร่วมได้จากหน้ารายละเอียดของแปลงนั้น ๆ (ปุ่ม "+ เพิ่มเข้าแปลงสาธิต")
 </p>
 <div class="table-responsive p-0">
 <table class="table align-items-center mb-0">
 <thead>
 <tr>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">แปลง</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">ครัวเรือน</th>
 <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">กลุ่ม</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">เข้าร่วมตั้งแต่</th>
 <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">สิ้นสุด</th>
 <th class="text-secondary opacity-7"></th>
 </tr>
 </thead>
 <tbody>
 @forelse ($group->demonstrationPlots as $enrollment)
 <tr>
 <td><a href="{{ route('plots.show', $enrollment->plot) }}" class="text-xs font-weight-bold px-2">{{ $enrollment->plot->plot_code }}</a></td>
 <td class="ps-2"><span class="text-secondary text-xs">{{ $enrollment->plot->farm->household->head_name }}</span></td>
 <td class="text-center">
 @if ($enrollment->group_type === 'treatment')
 <span class="badge badge-sm bg-gradient-info">Treatment</span>
 @else
 <span class="badge badge-sm bg-gradient-secondary">Control</span>
 @endif
 </td>
 <td><span class="text-secondary text-xs font-weight-bold">{{ $enrollment->enrolled_at->format('d/m/Y') }}</span></td>
 <td>
 @if ($enrollment->ended_at)
 <span class="text-secondary text-xs">{{ $enrollment->ended_at->format('d/m/Y') }}</span>
 @else
 <span class="badge badge-sm bg-gradient-success">กำลังร่วม</span>
 @endif
 </td>
 <td class="align-middle text-end">
 @can('create', [App\Models\DemonstrationPlot::class, $enrollment->plot])
 @if (! $enrollment->ended_at)
 <form method="POST" action="{{ route('demonstration-plots.end', $enrollment) }}" class="d-inline">
 @csrf
 <button class="btn btn-link text-secondary text-xs mb-0 p-0">สิ้นสุด</button>
 </form>
 @endif
 @endcan
 </td>
 </tr>
 @empty
 <tr><td colspan="6" class="text-center text-secondary text-sm py-4">ยังไม่มีแปลงเข้าร่วม</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <p><a href="{{ route('demonstration-comparison-groups.index') }}" class="text-secondary text-sm">&larr; กลับไปหน้ารายการ</a></p>
 </div>
 </div>
</x-app-layout>
