<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agriculture_events', function (Blueprint $table) {
            $table->string('source', 30)->default('official')->after('created_by');
            $table->string('approval_status', 30)->default('approved')->after('source');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->foreignId('approved_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('agriculture_events', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'source',
                'approval_status',
                'rejection_reason',
                'approved_by',
                'approved_at',
            ]);
        });
    }
};
