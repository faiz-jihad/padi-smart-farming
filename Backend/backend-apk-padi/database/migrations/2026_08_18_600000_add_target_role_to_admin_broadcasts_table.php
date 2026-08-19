<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_broadcasts', function (Blueprint $table) {
            $table->string('target_role', 30)->default('all')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('admin_broadcasts', function (Blueprint $table) {
            $table->dropColumn('target_role');
        });
    }
};
