<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// A plot's location was always derived from its farm in practice (the GPS
// map picker on the plot form even defaulted its center to the parent
// farm's own coordinates), and a farm's plots are never scattered across
// different places - so per-plot gps_lat/gps_lng was redundant with
// farms.gps_lat/gps_lng and never carried information the farm didn't
// already have. Farm keeps its own coordinates (used for the Leaflet map
// on the Farm Management dashboard per Blueprint 14) - this migration only
// touches plots.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->dropColumn(['gps_lat', 'gps_lng']);
        });
    }

    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
        });
    }
};
