<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('soil_detections', function (Blueprint $table) {
            $table->foreignId('soil_type_id')
                ->nullable()
                ->after('soil_temp_celsius')
                ->constrained('soil_types')
                ->nullOnDelete();
        });

        // Safe Backfill: Match existing soil_type string to soil_types master table
        $soilTypes = DB::table('soil_types')->get();
        foreach ($soilTypes as $st) {
            DB::table('soil_detections')
                ->whereNull('soil_type_id')
                ->where(function ($query) use ($st) {
                    $query->where('soil_type', $st->code)
                        ->orWhere('soil_type', $st->name);
                })
                ->update(['soil_type_id' => $st->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soil_detections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('soil_type_id');
        });
    }
};
