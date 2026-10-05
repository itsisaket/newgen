<x-app-layout :title="'ทะเบียนครัวเรือน'">
    <div class="household-registry">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">ทะเบียนครัวเรือน</h4>
                <p class="text-sm text-secondary mb-0">ค้นหาและจัดการข้อมูลครัวเรือน สวน และแปลงทุเรียนในพื้นที่รับผิดชอบ</p>
            </div>
            @can('create', App\Models\Household::class)
                <a href="{{ route('households.create') }}" class="btn bg-gradient-success mb-0">
                    <span class="material-symbols-rounded align-middle me-1" aria-hidden="true">person_add</span>เพิ่มครัวเรือน
                </a>
            @endcan
        </div>

        <div class="row g-3 mb-4 household-summary">
            @foreach ([
                ['icon' => 'groups', 'label' => 'ครัวเรือนทั้งหมด', 'value' => $summary['households'], 'tone' => 'green'],
                ['icon' => 'verified_user', 'label' => 'กำลังใช้งาน', 'value' => $summary['active'], 'tone' => 'blue'],
                ['icon' => 'forest', 'label' => 'สวนทุเรียน', 'value' => $summary['farms'], 'tone' => 'gold'],
                ['icon' => 'diversity_3', 'label' => 'กลุ่มเกษตรกร', 'value' => $summary['groups'], 'tone' => 'purple'],
            ] as $item)
                <div class="col-6 col-xl-3">
                    <div class="household-summary__card">
                        <span class="household-summary__icon household-summary__icon--{{ $item['tone'] }} material-symbols-rounded" aria-hidden="true">{{ $item['icon'] }}</span>
                        <div>
                            <div class="household-summary__value">{{ number_format($item['value']) }}</div>
                            <div class="household-summary__label">{{ $item['label'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card household-filter-card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="material-symbols-rounded text-success" aria-hidden="true">filter_alt</span>
                    <div><h6 class="mb-0">ค้นหาและกรองข้อมูล</h6><p class="text-xs text-secondary mb-0">ระบุเฉพาะเงื่อนไขที่ต้องการ แล้วกดค้นหา</p></div>
                </div>
                <form method="GET" action="{{ route('households.index') }}" class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <label class="field-label" for="household_q">ชื่อ รหัสครัวเรือน หรือเบอร์โทร</label>
                        <input id="household_q" name="q" class="field-control" value="{{ request('q') }}" placeholder="เช่น สมชาย หรือ HH-0013">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="field-label" for="farmer_group_id">กลุ่มเกษตรกร</label>
                        <select name="farmer_group_id" id="farmer_group_id" class="field-control">
                            <option value="">ทุกกลุ่ม</option>
                            @foreach ($farmerGroups as $group)
                                <option value="{{ $group->id }}" @selected(request('farmer_group_id') == $group->id)>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="field-label" for="household_status">สถานะ</label>
                        <select name="status" id="household_status" class="field-control">
                            <option value="">ทุกสถานะ</option>
                            <option value="active" @selected(request('status') === 'active')>ใช้งานอยู่</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>ระงับการใช้งาน</option>
                            <option value="withdrawn" @selected(request('status') === 'withdrawn')>ถอนตัว</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 d-flex align-items-end gap-2">
                        <button class="btn bg-gradient-dark mb-0 flex-grow-1"><span class="material-symbols-rounded align-middle me-1" aria-hidden="true">search</span>ค้นหา</button>
                        <a href="{{ route('households.index') }}" class="btn btn-outline-secondary mb-0">ล้าง</a>
                    </div>
                    <div class="col-md-4">
                        <label class="field-label" for="province_select">จังหวัด</label>
                        <select name="province_id" id="province_select" class="field-control">
                            <option value="">ทุกจังหวัด</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}" @selected($selectedProvinceId == $province->id)>{{ $province->name_th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="field-label" for="district_select">อำเภอ</label>
                        <select name="district_id" id="district_select" class="field-control" data-selected="{{ request('district_id') }}"><option value="">ทุกอำเภอ</option></select>
                    </div>
                    <div class="col-md-4">
                        <label class="field-label" for="tambon_select">ตำบล</label>
                        <select name="tambon_id" id="tambon_select" class="field-control" data-selected="{{ request('tambon_id') }}"><option value="">ทุกตำบล</option></select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card household-results-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 pb-2">
                <div><h6 class="mb-1">รายชื่อครัวเรือน</h6><p class="text-xs text-secondary mb-0">พบ {{ number_format($households->total()) }} รายการ</p></div>
                <span class="text-xs text-secondary">เรียงตามรหัสครัวเรือน</span>
            </div>

            <div class="table-responsive d-none d-md-block">
                <table class="table household-table align-items-center mb-0">
                    <thead><tr><th>ครัวเรือน</th><th>ที่ตั้ง</th><th>กลุ่มเกษตรกร</th><th class="text-center">สวน / แปลง</th><th class="text-center">สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
                    <tbody>
                        @forelse ($households as $household)
                            @php
                                $tambon = $household->farmerGroup?->tambon ?? $household->village?->tambon;
                                $village = $household->village?->display_name ?? $household->village?->name_th;
                            @endphp
                            <tr>
                                <td>
                                    <div class="household-person">
                                        <span class="household-person__avatar material-symbols-rounded" aria-hidden="true">person</span>
                                        <div>
                                            <a href="{{ route('households.show', $household) }}" class="fw-semibold text-dark">{{ $household->head_name }}</a>
                                            <div class="text-xs text-secondary mt-1">{{ $household->household_code }} @if ($household->user?->isInnovator())<span class="badge bg-gradient-gold ms-1">นวัตกร</span>@endif</div>
                                        </div>
                                    </div>
                                </td>
                                <td><div class="text-sm text-dark">{{ $village ?: 'ไม่ระบุหมู่บ้าน' }}</div><div class="text-xs text-secondary mt-1">{{ collect([$tambon?->name_th, $tambon?->district?->name_th])->filter()->join(' · ') ?: 'ยังไม่ระบุพื้นที่' }}</div></td>
                                <td><span class="text-sm">{{ $household->farmerGroup->name ?? 'ไม่ระบุกลุ่ม' }}</span></td>
                                <td class="text-center"><span class="household-count-pill"><b>{{ number_format($household->farms_count) }}</b> สวน</span><span class="household-count-pill"><b>{{ number_format($household->plots_count) }}</b> แปลง</span></td>
                                <td class="text-center"><x-status-badge :status="$household->status" /></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('households.show', $household) }}" class="btn btn-sm bg-gradient-success mb-0">ดูและจัดการ</a>
                                    @can('update', $household)<a href="{{ route('households.edit', $household) }}" class="btn btn-sm btn-outline-secondary mb-0">แก้ไข</a>@endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary py-5">ไม่พบครัวเรือนตามเงื่อนไขที่เลือก</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="household-mobile-list d-md-none">
                @forelse ($households as $household)
                    @php
                        $tambon = $household->farmerGroup?->tambon ?? $household->village?->tambon;
                        $village = $household->village?->display_name ?? $household->village?->name_th;
                    @endphp
                    <article class="household-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2"><div><h6 class="mb-1">{{ $household->head_name }}</h6><span class="text-xs text-secondary">{{ $household->household_code }}</span></div><x-status-badge :status="$household->status" /></div>
                        <div class="household-mobile-card__meta">
                            <div><span class="material-symbols-rounded">location_on</span>{{ collect([$village, $tambon?->name_th, $tambon?->district?->name_th])->filter()->join(' · ') ?: 'ยังไม่ระบุพื้นที่' }}</div>
                            <div><span class="material-symbols-rounded">diversity_3</span>{{ $household->farmerGroup->name ?? 'ไม่ระบุกลุ่มเกษตรกร' }}</div>
                            <div><span class="material-symbols-rounded">forest</span>{{ number_format($household->farms_count) }} สวน · {{ number_format($household->plots_count) }} แปลง</div>
                        </div>
                        <div class="d-flex gap-2 mt-3"><a href="{{ route('households.show', $household) }}" class="btn btn-sm bg-gradient-success mb-0 flex-grow-1">ดูและจัดการ</a>@can('update', $household)<a href="{{ route('households.edit', $household) }}" class="btn btn-sm btn-outline-secondary mb-0">แก้ไข</a>@endcan</div>
                    </article>
                @empty
                    <div class="text-center text-secondary py-5">ไม่พบครัวเรือนตามเงื่อนไขที่เลือก</div>
                @endforelse
            </div>

            @if ($households->hasPages())<div class="card-footer pt-3">{{ $households->links() }}</div>@endif
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/cascading-location.js') }}?v={{ filemtime(public_path('js/cascading-location.js')) }}" defer></script>
        <script>document.addEventListener('DOMContentLoaded',function(){drfisInitLocationCascade({province:'province_select',district:'district_select',tambon:'tambon_select',districtsUrl:'{{ route('locations.districts') }}',tambonsUrl:'{{ route('locations.tambons') }}'});});</script>
    @endpush
</x-app-layout>
