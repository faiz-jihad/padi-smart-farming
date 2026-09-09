<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'face_descriptor')) {
                $table->json('face_descriptor')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'face_registered_at')) {
                $table->timestamp('face_registered_at')->nullable()->after('face_descriptor');
            }

            if (! Schema::hasColumn('users', 'pin_hash')) {
                $table->string('pin_hash')->nullable()->after('face_registered_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach (['pin_hash', 'face_registered_at', 'face_descriptor'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
