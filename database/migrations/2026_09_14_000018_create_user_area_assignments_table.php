<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Blueprint section 4.1 - the table every area-scoped RBAC query joins
// through. A user may have many rows here (e.g. a District Officer
// covering several tambons). scope_id is nullable only when
// scope_type = 'all' (Super Admin / Project Admin).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_area_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('scope_type', ['province', 'district', 'tambon', 'farmer_group', 'all']);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_area_assignments');
    }
};
