<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds structured จังหวัด/อำเภอ/ตำบล (province/district/tambon) location to
// farms, alongside the existing free-text `address` column (kept as a
// supplementary detail field - house number/road/soi - not replaced by
// this). Household already gets its location transitively through
// farmer_group/village -> tambon -> district -> province, so no equivalent
// columns are needed there; this is only for Farm, which previously had no
// structured location at all. Nullable + nullOnDelete since a farm can
// exist before its location is fully classified, and deleting a
// province/district/tambon (should that ever happen) should not cascade
// into deleting farm records.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('address')
                ->constrained('provinces')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('province_id')
                ->constrained('districts')->nullOnDelete();
            $table->foreignId('tambon_id')->nullable()->after('district_id')
                ->constrained('tambons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('province_id');
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('tambon_id');
        });
    }
};
