<?php

namespace App\Support;

/**
 * Generates sequential business codes (HH-0001, FARM-0001, ...) so the
 * create forms for Household/Farm no longer ask the user to type one by
 * hand - head_name/farm_name are the primary display name now, and the
 * code becomes an internal reference id assigned automatically.
 *
 * Looks at the highest existing numeric suffix for the given prefix
 * (not row count), so a deleted or out-of-order code never causes a
 * collision or gets silently reused.
 */
class CodeGenerator
{
    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    public static function next(string $modelClass, string $column, string $prefix, int $padLength = 4): string
    {
        $max = $modelClass::where($column, 'like', $prefix.'-%')
            ->pluck($column)
            ->map(fn ($code) => (int) substr((string) $code, strlen($prefix) + 1))
            ->max();

        $next = ($max ?? 0) + 1;

        return sprintf('%s-%0'.$padLength.'d', $prefix, $next);
    }
}
