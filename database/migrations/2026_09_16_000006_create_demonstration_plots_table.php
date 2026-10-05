<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F05 - one row per plot enrolled into one demonstration_comparison_groups
// experiment, tagged treatment or control - see that migration's
// doc-comment for the overall design. unique(comparison_group_id,
// plot_id) stops the same plot being added twice to the same experiment
// (it MAY belong to more than one experiment across different comparison
// groups/crop seasons, which is why the uniqueness isn't on plot_id
// alone). enrolled_by follows every other field-data table's "who
// recorded this" convention (see e.g. the knowledge_transfers
// migration's doc-comment) even though the design doc's proposed schema
// didn't list it explicitly.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demonstration_plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comparison_group_id')->constrained('demonstration_comparison_groups')->cascadeOnDelete();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            $table->string('group_type');
            $table->date('enrolled_at');
            $table->date('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('enrolled_by')->constrained('users');
            $table->timestamps();

            $table->unique(['comparison_group_id', 'plot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demonstration_plots');
    }
};
