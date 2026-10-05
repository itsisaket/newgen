<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Supports installing the full official Thailand province/district/tambon
// dataset (database/seeders/LocationSeeder.php) - `provinces` already had
// name_en from Sprint 1, but `districts`/`tambons` didn't, and `tambons`
// had no zip_code column at all. Both nullable so this is safe to run
// before the reseed (the official dataset always fills them, but nothing
// downstream depends on them being non-null).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('districts', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name_th');
        });

        Schema::table('tambons', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name_th');
            $table->unsignedInteger('zip_code')->nullable()->after('name_en');
        });
    }

    public function down(): void
    {
        Schema::table('districts', function (Blueprint $table) {
            $table->dropColumn('name_en');
        });

        Schema::table('tambons', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'zip_code']);
        });
    }
};
