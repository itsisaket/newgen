<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

/**
 * DRFIS user account.
 *
 * Every request must be checked against BOTH:
 *  - Role/Permission (via Spatie's HasRoles trait: roles(), permissions(), can())
 *  - Geographic/Project Scope (via areaAssignments(), see Blueprint section 4.1)
 * Prefer AreaScopeService / a global scope over ad-hoc checks in controllers.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Quick-reference primary role (Blueprint Appendix C.1 users.role_id).
     * Fine-grained authorization should still go through Spatie's
     * hasRole()/can() (model_has_roles), not this column alone.
     */
    public function primaryRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function areaAssignments()
    {
        return $this->hasMany(UserAreaAssignment::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * The household this Farmer/Innovator login belongs to, when this
     * account was auto-provisioned for one (see
     * App\Support\FarmerAccountProvisioner). Null for role-example/staff
     * accounts (admin, researcher, field officer, etc.) that aren't tied
     * to a specific household.
     */
    public function household()
    {
        return $this->hasOne(Household::class);
    }

    /**
     * A promoted Farmer keeps the Farmer role AND gains Innovator on top
     * (assignRole(), never syncRoles() - see InnovatorEvaluationController)
     * so this is a hasRole() check, not a replacement of their base role.
     */
    public function isInnovator(): bool
    {
        return $this->hasRole(Role::INNOVATOR);
    }
}
