<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farmer_profiles', function (Blueprint $table): void {
            $table->string('regency', 100)->nullable()->after('district');
        });
    }

    public function down(): void
    {
        Schema::table('farmer_profiles', function (Blueprint $table): void {
            $table->dropColumn('regency');
        });
    }
};