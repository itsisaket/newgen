<?php

namespace App\Support;

use App\Contracts\SmsSender;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Creates/refreshes the one Farmer-role login account that belongs to a
 * household (Blueprint section 4 role table + this sprint's "1 ครัวเรือน
 * คือ 1 เกษตรกร (login Farmer)" requirement). households.user_id is a
 * nullable, unique FK - one login per household, never shared.
 *
 * The system has no email-collection step for households, so login uses
 * a synthetic @drfis.local address derived from household_code
 * (guaranteed unique, since household_code itself is unique) and a random
 * password. The plaintext password is only ever returned once, for the
 * caller to flash into the session for a single display - it is never
 * stored or logged anywhere except through the SMS stub below (which
 * exists specifically to simulate what a farmer would receive).
 *
 * Backlog item (Blueprint 4's "phone + OTP เป็นทางเลือกสำหรับ Farmer/
 * Innovator"): every credential this class generates is also "sent" via
 * App\Contracts\SmsSender to household->phone when one is on file - today
 * that's the LogSmsSender stub (see AppServiceProvider::register()), so
 * this doesn't yet replace the officer-reads-it-off-the-screen flow, but
 * the seam is in place for a real gateway to plug into later without
 * touching HouseholdController.
 */
class FarmerAccountProvisioner
{
    /**
     * Create the Farmer account for a household that doesn't have one yet.
     * Idempotent: if household->user_id is already set, does nothing and
     * returns null instead of creating a second account.
     *
     * @return array{email: string, password: string}|null
     */
    public static function provision(Household $household): ?array
    {
        if ($household->user_id) {
            return null;
        }

        $password = Str::password(10);
        $role = Role::where('name', Role::FARMER)->first();

        $user = User::create([
            'name' => $household->head_name,
            'email' => static::emailFor($household),
            'phone' => $household->phone,
            'password' => $password, // hashed automatically by User's `password` => 'hashed' cast
            'role_id' => $role?->id,
            // Always active, independent of the household's own status -
            // user confirmed (15 ก.ย.) the Farmer login should keep working
            // even after the household is marked inactive/withdrawn (e.g.
            // a นวัตกรชุมชน who "graduated" the program should not be
            // locked out of a login built on the same account).
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        if ($role) {
            $user->assignRole($role);
        }

        $household->update(['user_id' => $user->id]);

        static::notifyByPhone($household, $user->email, $password);

        return ['email' => $user->email, 'password' => $password];
    }

    /**
     * Reset the password of a household's existing Farmer account (e.g.
     * when field staff has lost the one-time password shown at
     * registration, or is handing the account to someone else). Does
     * nothing if the household has no linked account yet.
     *
     * @return array{email: string, password: string}|null
     */
    public static function resetPassword(Household $household): ?array
    {
        $user = $household->user;

        if (! $user) {
            return null;
        }

        $password = Str::password(10);
        $user->forceFill(['password' => $password])->save();

        static::notifyByPhone($household, $user->email, $password);

        return ['email' => $user->email, 'password' => $password];
    }

    /**
     * Best-effort only - a household with no phone on file simply gets no
     * SMS (the flashed on-screen credentials are still the reliable path
     * either way), and the SmsSender contract itself promises never to
     * throw for a delivery failure.
     */
    private static function notifyByPhone(Household $household, string $email, string $password): void
    {
        if (! $household->phone) {
            return;
        }

        $message = "DRFIS: บัญชีเกษตรกรของท่านคือ {$email} รหัสผ่านชั่วคราว: {$password} กรุณาเปลี่ยนรหัสผ่านหลังเข้าสู่ระบบครั้งแรก";

        app(SmsSender::class)->send($household->phone, $message);
    }

    private static function emailFor(Household $household): string
    {
        return strtolower($household->household_code).'@drfis.local';
    }
}
