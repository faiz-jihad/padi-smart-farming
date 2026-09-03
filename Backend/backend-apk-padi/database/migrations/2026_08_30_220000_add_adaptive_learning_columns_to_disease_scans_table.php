<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disease_scans', function (Blueprint $table) {
            $table->string('user_feedback', 30)->nullable()->after('model_version');
            $table->string('verified_class', 100)->nullable()->after('user_feedback');
            $table->boolean('is_learned')->default(false)->after('verified_class');
            $table->text('feedback_notes')->nullable()->after('is_learned');
        });
    }

    public function down(): void
    {
        Schema::table('disease_scans', function (Blueprint $table) {
            $table->dropColumn([
                'user_feedback',
                'verified_class',
                'is_learned',
                'feedback_notes',
            ]);
        });
    }
};
