<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F10 - Evidence Management (Blueprint section 9). Polymorphic so any
// module (Farm Activity, Kiln Batch, Knowledge Transfer, ...) can attach
// files without a dedicated evidence table per module.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidences', function (Blueprint $table) {
            $table->id();
            $table->string('evidenceable_type');
            $table->unsignedBigInteger('evidenceable_id');
            $table->string('file_path');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();

            $table->index(['evidenceable_type', 'evidenceable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidences');
    }
};
