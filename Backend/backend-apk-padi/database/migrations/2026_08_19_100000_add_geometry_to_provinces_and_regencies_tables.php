<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->json('geometry')->nullable()->after('longitude');
            $table->json('bbox')->nullable()->after('geometry');
        });

        Schema::table('regencies', function (Blueprint $table) {
            $table->json('geometry')->nullable()->after('longitude');
            $table->json('bbox')->nullable()->after('geometry');
        });
    }

    public function down(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropColumn(['geometry', 'bbox']);
        });

        Schema::table('regencies', function (Blueprint $table) {
            $table->dropColumn(['geometry', 'bbox']);
        });
    }
};
