<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F14/F08-lite round: harvest_records had `status` (default 'draft') from
// its original Sprint 1 migration but no screen and no recorded_by/
// rejection_reason/original_record_id - so it could never actually go
// through WorkflowService (Blueprint 16.1: every F01-F15 module shares the
// same Draft -> Submitted -> Verified -> Approved state machine). Adding
// the same 3 columns farm_activities already has, in the same shape, so
// HarvestRecordController can use WorkflowService exactly like
// FarmActivityController does instead of inventing a separate simplified
// status flow. The table is guaranteed empty on every existing install
// (no screen ever wrote to it), so recorded_by can be added NOT NULL
// safely - no backfill needed. Also adds buyer_id's real FK now that
// `buyers` exists (the original migration left it FK-less on purpose,
// "buyers table lands with F08 Market Validation").
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvest_records', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
            $table->foreignId('recorded_by')->after('rejection_reason')->constrained('users');
            $table->foreignId('original_record_id')->nullable()->after('recorded_by')->constrained('harvest_records')->nullOnDelete();
            $table->foreign('buyer_id')->references('id')->on('buyers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('harvest_records', function (Blueprint $table) {
            $table->dropForeign(['buyer_id']);
            $table->dropForeign(['original_record_id']);
            $table->dropForeign(['recorded_by']);
            $table->dropColumn(['rejection_reason', 'recorded_by', 'original_record_id']);
        });
    }
};
