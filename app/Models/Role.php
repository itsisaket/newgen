<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Thin extension of Spatie's Role model so App\Models\User::primaryRole()
 * and any DRFIS-specific role helpers have a stable place to live.
 */
class Role extends SpatieRole
{
    // Blueprint section 4 roles: Super Admin, Project Admin, Researcher,
    // Field Officer, District Officer, Innovator, Farmer, Evaluator, Viewer.
    public const SUPER_ADMIN = 'Super Admin';
    public const PROJECT_ADMIN = 'Project Admin';
    public const RESEARCHER = 'Researcher';
    public const FIELD_OFFICER = 'Field Officer';
    public const DISTRICT_OFFICER = 'District Officer';
    public const INNOVATOR = 'Innovator';
    public const FARMER = 'Farmer';
    public const EVALUATOR = 'Evaluator';
    public const VIEWER = 'Viewer';

    public const ALL = [
        self::SUPER_ADMIN,
        self::PROJECT_ADMIN,
        self::RESEARCHER,
        self::FIELD_OFFICER,
        self::DISTRICT_OFFICER,
        self::INNOVATOR,
        self::FARMER,
        self::EVALUATOR,
        self::VIEWER,
    ];

    /**
     * Role groupings used by the Policy classes (app/Policies/*) and
     * AreaScopeService to decide who sees what (15 ก.ย. authorization
     * round). These are deliberately coarse - they are NOT the full
     * per-action Role/Permission Matrix that Blueprint Appendix E item 3
     * still asks the project team to sign off on. They only close the
     * concrete gap found during the 15 ก.ย. review: every logged-in user
     * could see/edit every household's data regardless of role.
     *
     * - STAFF: sees/edits data within their AreaScopeService scope
     *   (Field Officer/District Officer are genuinely area-limited;
     *   Super Admin/Project Admin/Researcher typically hold a SCOPE_ALL
     *   assignment - see DemoUserSeeder).
     * - OWN_HOUSEHOLD: a Farmer/Innovator login only ever sees/edits the
     *   one household its account is linked to (App\Support\
     *   FarmerAccountProvisioner) - never area-scoped, always exactly
     *   their own record.
     * - READ_ONLY: Evaluator/Viewer per Blueprint section 4 are scoped to
     *   "Dashboard, Evidence, Reports" only, not the ทะเบียนข้อมูลหลัก
     *   registry screens (Household/Farm/Plot/Baseline/Activity/Innovator
     *   Evaluation) - so for now they get no access to those screens at
     *   all rather than a half-specified partial one. They already have
     *   the public aggregate Dashboard (no login required).
     */
    public const STAFF = [
        self::SUPER_ADMIN,
        self::PROJECT_ADMIN,
        self::RESEARCHER,
        self::FIELD_OFFICER,
        self::DISTRICT_OFFICER,
    ];

    public const OWN_HOUSEHOLD = [
        self::FARMER,
        self::INNOVATOR,
    ];

    public const READ_ONLY = [
        self::EVALUATOR,
        self::VIEWER,
    ];
}
