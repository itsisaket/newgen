<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Links a household to its one Farmer-role login account (Blueprint
// section 4 role table + this sprint's "1 ครัวเรือน คือ 1 เกษตรกร
// (login Farmer)" requirement - see App\Support\FarmerAccountProvisioner).
// Nullable + unique: a unique index in MySQL still allows any number of
// NULL rows, only non-null values must be distinct, so this tolerates a
// household that hasn't been provisioned an account yet while still
// guaranteeing one login is never shared by two households.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('household_code')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
