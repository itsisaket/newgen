<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->string('official_code', 8)->nullable()->unique()->after('tambon_id');
            $table->unsignedSmallInteger('village_no')->nullable()->after('official_code');
            $table->decimal('latitude', 10, 7)->nullable()->after('name_th');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('source')->nullable()->after('longitude');
            $table->boolean('is_active')->default(true)->after('source')->index();
            $table->index(['tambon_id', 'name_th']);
        });
    }

    public function down(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->dropIndex(['tambon_id', 'name_th']);
            $table->dropIndex(['is_active']);
            $table->dropUnique(['official_code']);
            $table->dropColumn(['official_code', 'village_no', 'latitude', 'longitude', 'source', 'is_active']);
        });
    }
};
