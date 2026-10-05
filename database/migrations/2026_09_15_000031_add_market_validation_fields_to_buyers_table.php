<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F08 full (Sprint 4 round) - extends the F14/F08-lite `buyers` table
 * (name, phone, buyer_type, notes) up to Blueprint Appendix C.5's real
 * column list (id, name, type, contact, standard_required) rather than
 * replacing it outright - `phone`/`buyer_type` already have real data and
 * FK references from harvest_records/inventory_transactions/sales, so
 * dropping/renaming them would be a needless breaking change. `contact`
 * is added as a broader field than `phone` alone (Line ID/email/other
 * channel a buyer gave), and `standard_required` records any product
 * standard the buyer requires (GAP/Organic/etc.) per Blueprint 10 ("ผู้ซื้อ
 * ราคา ความต้องการ มาตรฐาน LOI/MOU").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('contact')->nullable()->after('phone');
            $table->string('standard_required')->nullable()->after('buyer_type');
        });
    }

    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn(['contact', 'standard_required']);
        });
    }
};
