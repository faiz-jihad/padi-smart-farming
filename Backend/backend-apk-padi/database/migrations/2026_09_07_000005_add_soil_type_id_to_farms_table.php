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
        Schema::table('farms', function (Blueprint $table) {
            $table->foreignId('soil_type_id')
                ->nullable()
                ->after('soil_type')
                ->constrained('soil_types')
                ->nullOnDelete();
        });

        // Safe Backfill: Match existing soil_type strings to soil_types master table
        $codeMap = [
            'loam' => ['loam', 'lempung', 'Lempung Berpasir', 'Lempung Berpasir / Loam'],
            'alluvial' => ['alluvial', 'aluvial', 'Aluvial'],
            'clay' => ['clay', 'liat', 'Liat / Clay', 'Liat', 'lempung_liat'],
            'sandy_loam' => ['sandy_loam', 'pasir', 'Pasir Berlempung', 'Pasir Berlempung / Sandy Loam'],
            'latosol' => ['latosol', 'Latosol', 'Latosol / Merah Kuning'],
            'peat' => ['peat', 'gambut', 'Gambut', 'Gambut / Peat'],
        ];

        $soilTypes = DB::table('soil_types')->get()->keyBy('code');

        foreach ($codeMap as $code => $aliases) {
            if (isset($soilTypes[$code])) {
                $targetId = $soilTypes[$code]->id;
                DB::table('farms')
                    ->whereNull('soil_type_id')
                    ->whereIn('soil_type', $aliases)
                    ->update(['soil_type_id' => $targetId]);
            }
        }

        // Fallback for any other remaining unlinked farms: match by exact code or name
        foreach ($soilTypes as $st) {
            DB::table('farms')
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
        Schema::table('farms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('soil_type_id');
        });
    }
};
