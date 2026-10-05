<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farmer_groups', function (Blueprint $table) {
            $table->string('contact_phone', 30)->nullable()->after('leader_name');
            $table->text('description')->nullable()->after('contact_phone');
            $table->boolean('is_active')->default(true)->after('description')->index();
            $table->unique(['tambon_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('farmer_groups', function (Blueprint $table) {
            $table->dropUnique(['tambon_id', 'name']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['contact_phone', 'description', 'is_active']);
        });
    }
};
