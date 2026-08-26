<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

return new class extends Migration
{
    /**
     * Helper to safely add an index without throwing Duplicate Key errors.
     */
    private function safeAddIndex(string $table, array|string $columns, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (QueryException $e) {
            // Error code 1061 is MySQL duplicate key name - safely ignore
            if (!str_contains($e->getMessage(), '1061') && !str_contains($e->getMessage(), 'Duplicate key')) {
                throw $e;
            }
        }
    }

    /**
     * Helper to safely drop an index without throwing errors.
     */
    private function safeDropIndex(string $table, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        } catch (QueryException $e) {
            // Safely ignore if index doesn't exist
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Index optimization for notifications table
        if (Schema::hasTable('notifications')) {
            $this->safeAddIndex('notifications', ['user_id', 'read_at'], 'idx_notifications_user_read');
            $this->safeAddIndex('notifications', 'type', 'idx_notifications_type');
            $this->safeAddIndex('notifications', 'created_at', 'idx_notifications_created_at');
        }

        // 2. Index optimization for market_listings table
        if (Schema::hasTable('market_listings')) {
            $this->safeAddIndex('market_listings', ['status', 'published_at'], 'idx_market_listings_status_published');
            $this->safeAddIndex('market_listings', 'farmer_id', 'idx_market_listings_farmer');
        }

        // 3. Index optimization for community_reports table
        if (Schema::hasTable('community_reports')) {
            $this->safeAddIndex('community_reports', ['status', 'reported_at'], 'idx_community_reports_status_reported');
            $this->safeAddIndex('community_reports', 'farmer_id', 'idx_community_reports_farmer');
        }

        // 4. Index optimization for disease_scans table (farmer_id)
        if (Schema::hasTable('disease_scans')) {
            $this->safeAddIndex('disease_scans', ['farmer_id', 'created_at'], 'idx_disease_scans_farmer_created');
        }

        // 5. Index optimization for device_tokens table
        if (Schema::hasTable('device_tokens')) {
            $this->safeAddIndex('device_tokens', ['user_id', 'platform'], 'idx_device_tokens_user_platform');
        }

        // 6. Index optimization for crop_seasons table
        if (Schema::hasTable('crop_seasons')) {
            $this->safeAddIndex('crop_seasons', ['farm_id', 'status'], 'idx_crop_seasons_farm_status');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notifications')) {
            $this->safeDropIndex('notifications', 'idx_notifications_user_read');
            $this->safeDropIndex('notifications', 'idx_notifications_type');
            $this->safeDropIndex('notifications', 'idx_notifications_created_at');
        }

        if (Schema::hasTable('market_listings')) {
            $this->safeDropIndex('market_listings', 'idx_market_listings_status_published');
            $this->safeDropIndex('market_listings', 'idx_market_listings_farmer');
        }

        if (Schema::hasTable('community_reports')) {
            $this->safeDropIndex('community_reports', 'idx_community_reports_status_reported');
            $this->safeDropIndex('community_reports', 'idx_community_reports_farmer');
        }

        if (Schema::hasTable('disease_scans')) {
            $this->safeDropIndex('disease_scans', 'idx_disease_scans_farmer_created');
        }

        if (Schema::hasTable('device_tokens')) {
            $this->safeDropIndex('device_tokens', 'idx_device_tokens_user_platform');
        }

        if (Schema::hasTable('crop_seasons')) {
            $this->safeDropIndex('crop_seasons', 'idx_crop_seasons_farm_status');
        }
    }
};
