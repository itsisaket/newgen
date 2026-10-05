@props(['record', 'type'])

@if ($record->status === \App\Support\WorkflowStatus::APPROVED
 && auth()->user()->hasAnyRole([\App\Models\Role::SUPER_ADMIN, \App\Models\Role::PROJECT_ADMIN, \App\Models\Role::RESEARCHER]))
 <form method="POST" action="{{ route('workflow.revisions.store', ['type' => $type, 'id' => $record->getKey()]) }}" class="d-inline">
 @csrf
 <button class="btn btn-outline-warning btn-sm mb-0">ขอสร้างฉบับแก้ไข</button>
 </form>
@endif
