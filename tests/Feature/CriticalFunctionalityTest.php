<?php

namespace Tests\Feature;

use App\Models\ActivityType;
use App\Models\AuditLog;
use App\Models\CropSeason;
use App\Models\Evidence;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\FarmerGroup;
use App\Models\Household;
use App\Models\Plot;
use App\Models\Province;
use App\Models\District;
use App\Models\Tambon;
use App\Models\Village;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CriticalFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_staff_navigation_renders_registry_and_report_links(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('ครัวเรือน')
            ->assertSee('หมู่บ้านในประเทศไทย')
            ->assertSee('กลุ่มเกษตรกร')
            ->assertSee('searchable-select.js', false)
            ->assertSee('รายงานและส่งออกข้อมูล');
    }

    public function test_staff_can_create_a_farmer_group(): void
    {
        $user = $this->superAdmin();
        $province = Province::create(['name_th' => 'จังหวัดกลุ่มทดสอบ']);
        $district = District::create(['province_id' => $province->id, 'name_th' => 'อำเภอกลุ่มทดสอบ']);
        $tambon = Tambon::create(['district_id' => $district->id, 'name_th' => 'ตำบลกลุ่มทดสอบ']);

        $token = 'farmer-group-test-token';
        $this->actingAs($user)->withSession(['_token' => $token])->post(route('farmer-groups.store'), [
            '_token' => $token,
            'tambon_id' => $tambon->id,
            'name' => 'กลุ่มทุเรียนทดสอบ',
            'leader_name' => 'ผู้ประสานงานทดสอบ',
            'is_active' => '1',
        ])->assertRedirect(route('farmer-groups.index', ['tambon_id' => $tambon->id]));

        $this->assertDatabaseHas('farmer_groups', [
            'tambon_id' => $tambon->id,
            'name' => 'กลุ่มทุเรียนทดสอบ',
            'is_active' => true,
        ]);
    }

    public function test_villages_are_loaded_by_selected_tambon(): void
    {
        $user = $this->superAdmin();
        $province = Province::create(['name_th' => 'จังหวัดทดสอบ']);
        $district = District::create(['province_id' => $province->id, 'name_th' => 'อำเภอทดสอบ']);
        $tambon = Tambon::create(['district_id' => $district->id, 'name_th' => 'ตำบลทดสอบ']);
        Village::create([
            'tambon_id' => $tambon->id,
            'village_no' => 5,
            'name_th' => 'บ้านตัวอย่าง',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->getJson(route('locations.villages', ['tambon_id' => $tambon->id]))
            ->assertOk()
            ->assertJsonPath('0.name_th', 'หมู่ 5 บ้านตัวอย่าง');

        $this->actingAs($user)
            ->get(route('villages.index'))
            ->assertOk()
            ->assertSee('ทะเบียนหมู่บ้านในประเทศไทย');
    }

    public function test_evidence_file_requires_authorization_and_is_streamed_from_private_disk(): void
    {
        Storage::fake('evidence');
        [$user, $activity] = $this->activityGraph('draft');
        Storage::disk('evidence')->put('farmactivity/1/proof.txt', 'private proof');

        $evidence = Evidence::create([
            'evidenceable_type' => FarmActivity::class,
            'evidenceable_id' => $activity->id,
            'file_path' => 'farmactivity/1/proof.txt',
            'file_type' => 'text/plain',
            'file_size' => 13,
            'uploaded_by' => $user->id,
        ]);

        $this->get(route('evidences.download', $evidence))
            ->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('evidences.download', $evidence))
            ->assertOk();

        $response = $this->actingAs($user)->get(route('evidences.download', $evidence));
        $this->assertSame('private proof', $response->streamedContent());
    }

    public function test_approved_activity_can_create_an_immutable_revision_draft(): void
    {
        [$user, $activity] = $this->activityGraph('approved');

        $token = 'revision-test-token';
        $response = $this->actingAs($user)->withSession(['_token' => $token])->post(route('workflow.revisions.store', [
            'type' => 'farm-activity',
            'id' => $activity->id,
        ]), ['_token' => $token]);

        $response->assertRedirect();

        $revision = FarmActivity::where('original_record_id', $activity->id)->firstOrFail();

        $response->assertRedirect(route('farm-activities.show', $revision));
        $this->assertSame('revision_requested', $activity->fresh()->status);
        $this->assertSame('draft', $revision->status);
        $this->assertSame($user->id, $revision->recorded_by);
        $this->assertNotSame($activity->client_uuid, $revision->client_uuid);
    }

    public function test_research_csv_export_is_available_and_anonymized(): void
    {
        [$user] = $this->activityGraph('approved');

        $response = $this->actingAs($user)->get(route('reports.exports.csv'));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('HH-TEST', $content);
        $this->assertStringNotContainsString('หัวหน้าทดสอบ', $content);

        $this->actingAs($user)->get(route('reports.exports.xlsx'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)->get(route('reports.exports.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_api_sync_is_authenticated_scoped_and_idempotent(): void
    {
        [$user, $existing, $plot, $season, $type] = $this->activityGraph('draft');
        Sanctum::actingAs($user);

        $uuid = (string) Str::uuid();
        $payload = ['records' => [[
            'client_uuid' => $uuid,
            'plot_id' => $plot->id,
            'crop_season_id' => $season->id,
            'activity_type_id' => $type->id,
            'activity_date' => '2026-09-27',
            'quantity' => 2,
            'unit_cost' => 10,
            'labor_cost' => 5,
        ]]];

        $this->postJson('/api/v1/sync/farm-activities', $payload)
            ->assertStatus(207)
            ->assertJsonPath('results.0.status', 'created');

        $this->postJson('/api/v1/sync/farm-activities', $payload)
            ->assertStatus(207)
            ->assertJsonPath('results.0.status', 'duplicate');

        $this->assertDatabaseCount('farm_activities', 2);
        $this->getJson('/api/v1/sync/pull?since=2020-01-01')
            ->assertOk()
            ->assertJsonCount(2, 'farm_activities');
    }

    public function test_only_super_admin_can_open_audit_trail(): void
    {
        $admin = $this->superAdmin();
        AuditLog::create([
            'user_id' => $admin->id,
            'auditable_type' => Household::class,
            'auditable_id' => 1,
            'action' => 'test_action',
            'new_values' => ['status' => 'active'],
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get(route('audit-logs.index'))
            ->assertOk()
            ->assertSee('test_action');

        $farmer = User::factory()->create();
        $farmer->assignRole(Role::FARMER);
        $this->actingAs($farmer)
            ->get(route('audit-logs.index'))
            ->assertForbidden();
    }

    public function test_invalid_workflow_transition_returns_validation_feedback_instead_of_500(): void
    {
        [$user, $activity] = $this->activityGraph('draft');
        $token = 'invalid-transition-token';

        $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->from(route('farm-activities.show', $activity))
            ->post(route('workflow.revisions.store', ['type' => 'farm-activity', 'id' => $activity->id]), ['_token' => $token])
            ->assertRedirect(route('farm-activities.show', $activity))
            ->assertSessionHasErrors('workflow');
    }

    public function test_household_registry_is_searchable_and_shows_management_summary(): void
    {
        $user = $this->superAdmin();
        $matching = Household::create([
            'household_code' => 'HH-SEARCH-01',
            'head_name' => 'เกษตรกรค้นหาเจอ',
            'phone' => '0812345678',
            'status' => 'active',
        ]);
        Household::create([
            'household_code' => 'HH-OTHER-01',
            'head_name' => 'ครัวเรือนอื่น',
            'status' => 'inactive',
        ]);
        $farm = Farm::create([
            'household_id' => $matching->id,
            'farm_code' => 'FARM-SEARCH-01',
            'farm_name' => 'สวนสำหรับค้นหา',
        ]);
        Plot::create([
            'farm_id' => $farm->id,
            'plot_code' => 'PLOT-SEARCH-01',
            'area_rai' => 2,
            'tree_count' => 10,
        ]);

        $this->actingAs($user)
            ->get(route('households.index', ['q' => 'ค้นหาเจอ']))
            ->assertOk()
            ->assertSee('ค้นหาและกรองข้อมูล')
            ->assertSee('เกษตรกรค้นหาเจอ')
            ->assertDontSee('ครัวเรือนอื่น')
            ->assertSee('ดูและจัดการ')
            ->assertSee('พบ 1 รายการ');
    }

    private function superAdmin(): User
    {
        foreach (Role::ALL as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
        $role = Role::where('name', Role::SUPER_ADMIN)->firstOrFail();
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /** @return array{User, FarmActivity, Plot, CropSeason, ActivityType} */
    private function activityGraph(string $status): array
    {
        $user = $this->superAdmin();
        $household = Household::create([
            'household_code' => 'HH-TEST',
            'head_name' => 'หัวหน้าทดสอบ',
        ]);
        $farm = Farm::create([
            'household_id' => $household->id,
            'farm_code' => 'FARM-TEST',
            'farm_name' => 'สวนทดสอบ',
        ]);
        $plot = Plot::create([
            'farm_id' => $farm->id,
            'plot_code' => 'PLOT-TEST',
            'area_rai' => 4,
            'tree_count' => 20,
        ]);
        $season = CropSeason::create([
            'name' => 'ฤดูทดสอบ',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
        ]);
        $type = ActivityType::create([
            'category' => 'labor',
            'name' => 'กิจกรรมทดสอบ',
        ]);
        $activity = FarmActivity::create([
            'plot_id' => $plot->id,
            'crop_season_id' => $season->id,
            'activity_type_id' => $type->id,
            'activity_date' => '2026-09-26',
            'total_cost' => 100,
            'status' => $status,
            'recorded_by' => $user->id,
        ]);

        return [$user, $activity, $plot, $season, $type];
    }
}
