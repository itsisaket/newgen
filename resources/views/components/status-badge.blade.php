@php
$map = [
 'draft' => ['label' => 'ร่าง', 'class' => 'secondary'],
 'submitted' => ['label' => 'ส่งแล้ว', 'class' => 'info'],
 'verified' => ['label' => 'ตรวจสอบแล้ว', 'class' => 'primary'],
 'approved' => ['label' => 'อนุมัติแล้ว', 'class' => 'success'],
 'revision_requested' => ['label' => 'ขอแก้ไข', 'class' => 'warning'],
 // Household/Farm/Plot registration statuses (Blueprint 3.1/7.1) - these
 // are master data, not workflow records, so they reuse this
 // component's color scheme rather than the draft/submitted/... set.
 'active' => ['label' => 'ใช้งานอยู่', 'class' => 'success'],
 'inactive' => ['label' => 'ระงับการใช้งาน', 'class' => 'secondary'],
 'withdrawn' => ['label' => 'ถอนตัว', 'class' => 'danger'],
 // InnovatorEvaluation result (lite) - reuses this component's
 // color scheme rather than a one-off badge markup.
 'pass' => ['label' => 'ผ่าน', 'class' => 'success'],
 'fail' => ['label' => 'ไม่ผ่าน', 'class' => 'danger'],
 // DemonstrationComparisonGroup status (not a Draft/Submitted/
 // Verified/Approved workflow - see that migration's doc-comment).
 'completed' => ['label' => 'เสร็จสิ้น', 'class' => 'dark'],
];
$info = $map[$status] ?? ['label' => $status, 'class' => 'secondary'];
@endphp
<span class="badge badge-sm bg-gradient-{{ $info['class'] }}">{{ $info['label'] }}</span>
