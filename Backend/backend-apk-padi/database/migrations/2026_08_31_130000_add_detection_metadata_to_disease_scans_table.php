<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disease_scans', function (Blueprint $table) {
            $table->json('detection_metadata')->nullable()->after('confidence');
        });
    }

    public function down(): void
    {
        Schema::table('disease_scans', function (Blueprint $table) {
            $table->dropColumn('detection_metadata');
        });
    }
};
