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
            $table->foreignId('irrigation_type_id')
                ->nullable()
                ->after('irrigation_type')
                ->constrained('irrigation_types')
                ->nullOnDelete();
        });

        // Safe Backfill: Match existing irrigation_type strings (including legacy codes & english aliases)
        $codeMap = [
            'teknis' => ['teknis', 'irrigated', 'technical', 'Irigasi Teknis'],
            'setengah_teknis' => ['setengah_teknis', 'semi_irrigated', 'semi-technical', 'Irigasi Setengah Teknis'],
            'hujan' => ['hujan', 'rainfed', 'tadah_hujan', 'Sawah Tadah Hujan', 'Tadah Hujan'],
            'swamp' => ['swamp', 'tidal', 'rawa', 'pasang_surut', 'Rawa / Pasang Surut', 'Rawa'],
            'pompa' => ['pompa', 'pump', 'Irigasi Pompa'],
            'lainnya' => ['lainnya', 'other', 'others', 'Lainnya'],
        ];

        $irrigationTypes = DB::table('irrigation_types')->get()->keyBy('code');

        foreach ($codeMap as $code => $aliases) {
            if (isset($irrigationTypes[$code])) {
                $targetId = $irrigationTypes[$code]->id;
                DB::table('farms')
                    ->whereNull('irrigation_type_id')
                    ->whereIn('irrigation_type', $aliases)
                    ->update(['irrigation_type_id' => $targetId]);
            }
        }

        // Fallback for any other remaining unlinked farms: match by exact code or name
        foreach ($irrigationTypes as $it) {
            DB::table('farms')
                ->whereNull('irrigation_type_id')
                ->where(function ($query) use ($it) {
                    $query->where('irrigation_type', $it->code)
                        ->orWhere('irrigation_type', $it->name);
                })
                ->update(['irrigation_type_id' => $it->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('irrigation_type_id');
        });
    }
};
