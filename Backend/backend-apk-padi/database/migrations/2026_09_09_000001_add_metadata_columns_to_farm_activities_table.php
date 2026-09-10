<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('farm_activities', function (Blueprint $table) {
            if (! Schema::hasColumn('farm_activities', 'source')) {
                $table->string('source', 32)->default('MANUAL')->after('cost');
            }

            if (! Schema::hasColumn('farm_activities', 'status')) {
                $table->string('status', 32)->default('COMPLETED')->after('source');
            }

            if (! Schema::hasColumn('farm_activities', 'sync_status')) {
                $table->string('sync_status', 32)->default('pending')->after('status');
            }

            if (! Schema::hasColumn('farm_activities', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_activities', function (Blueprint $table) {
            if (Schema::hasColumn('farm_activities', 'sync_status')) {
                $table->dropColumn('sync_status');
            }

            if (Schema::hasColumn('farm_activities', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('farm_activities', 'source')) {
                $table->dropColumn('source');
            }

            if (Schema::hasColumn('farm_activities', 'created_at')) {
                $table->dropTimestamps();
            }
        });
    }
};
