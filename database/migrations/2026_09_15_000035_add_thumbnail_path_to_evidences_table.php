<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F10 backlog item (Blueprint หัวข้อ 9: "บีบอัดรูปภาพและสร้าง thumbnail ผ่าน
// queued job") - see App\Jobs\CompressEvidenceImage. Nullable because it's
// only populated for image uploads once the queue worker processes the
// job (never synchronously on upload - see EvidenceController::store()),
// and stays null for non-image evidence (PDF receipts, etc.) or if GD is
// unavailable on this server.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            $table->dropColumn('thumbnail_path');
        });
    }
};
