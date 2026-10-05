<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// household_code is the business primary key for a household (Blueprint 3.1).
// id_card_number must be encrypted at the application layer (Blueprint 19) -
// stored here as id_card_number_encrypted (cast with Laravel's `encrypted`
// cast on the model, never queried by raw value).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('household_code')->unique();
            $table->string('head_name');
            $table->text('id_card_number_encrypted')->nullable();
            $table->string('phone', 20)->nullable();
            $table->foreignId('farmer_group_id')->nullable()->constrained('farmer_groups')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->date('registered_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'withdrawn'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
