<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\Innovator;
use App\Models\InnovatorEvaluation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo F09-lite evaluation history so the dashboard's "นวัตกรชุมชน" count
 * and the households/innovator-evaluations screens have real, non-zero
 * numbers to show out of the box - mirrors (loosely, not to the exact
 * ratio) the Blueprint's sample figures (Appendix B: ~65 นวัตกร out of
 * ~101 households). Uses updateOrCreate keyed on household_id +
 * evaluation_date so `db:seed` stays safe to re-run, same convention as
 * DemoFarmSeeder.
 *
 * Must run after DemoFarmSeeder (needs the demo households), after
 * FarmerAccountSeeder (needs each household's Farmer login already
 * linked, so passing an evaluation has an account to promote), and after
 * DemoUserSeeder (needs evaluator@drfis.local as evaluated_by).
 */
class InnovatorEvaluationSeeder extends Seeder
{
    public function run(): void
    {
        $evaluator = User::where('email', 'evaluator@drfis.local')->first();

        $results = [
            'HH-0001' => 'pass', 'HH-0002' => 'fail',
            'HH-0003' => 'pass', 'HH-0004' => 'fail',
            'HH-0005' => 'pass', 'HH-0007' => 'pass',
            'HH-0009' => 'pass', 'HH-0011' => 'pass',
        ];

        foreach ($results as $code => $result) {
            $household = Household::where('household_code', $code)->first();

            if (! $household) {
                continue;
            }

            $evaluation = InnovatorEvaluation::updateOrCreate(
                ['household_id' => $household->id, 'evaluation_date' => '2026-08-15'],
                [
                    'evaluated_by' => $evaluator?->id,
                    'score' => $result === 'pass' ? 82.5 : 48.0,
                    'result' => $result,
                    'notes' => $result === 'pass'
                        ? 'ผ่านเกณฑ์ประเมินนวัตกรชุมชนรอบสาธิต (ข้อมูลตัวอย่าง)'
                        : 'ยังไม่ผ่านเกณฑ์ในรอบนี้ (ข้อมูลตัวอย่าง)',
                ]
            );

            // Same promotion rule as InnovatorEvaluationController::store()
            // - keep both in sync so seeded demo data behaves exactly like
            // recording it through the UI would (Sprint 4: this now also
            // includes the real `innovators` row, not just the role grant).
            if ($result === 'pass') {
                Innovator::updateOrCreate(
                    ['household_id' => $household->id],
                    [
                        'innovator_evaluation_id' => $evaluation->id,
                        'name' => $household->head_name,
                        'phone' => $household->phone,
                        'registered_at' => $evaluation->evaluation_date,
                    ]
                );

                if ($household->user) {
                    $household->user->assignRole(Role::INNOVATOR);

                    $innovatorRole = Role::where('name', Role::INNOVATOR)->first();
                    if ($innovatorRole) {
                        $household->user->forceFill(['role_id' => $innovatorRole->id])->save();
                    }
                }
            }
        }
    }
}
