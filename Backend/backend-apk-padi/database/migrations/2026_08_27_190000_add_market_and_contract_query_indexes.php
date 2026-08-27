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
        // 1. Index optimization for purchase_contracts table
        if (Schema::hasTable('purchase_contracts')) {
            $this->safeAddIndex('purchase_contracts', ['farmer_id', 'status'], 'idx_purchase_contracts_farmer_status');
            $this->safeAddIndex('purchase_contracts', ['partner_id', 'status'], 'idx_purchase_contracts_partner_status');
            $this->safeAddIndex('purchase_contracts', 'contracted_at', 'idx_purchase_contracts_contracted_at');
            $this->safeAddIndex('purchase_contracts', 'listing_id', 'idx_purchase_contracts_listing_id');
        }

        // 2. Index optimization for market_offers table
        if (Schema::hasTable('market_offers')) {
            $this->safeAddIndex('market_offers', ['listing_id', 'status'], 'idx_market_offers_listing_status');
            $this->safeAddIndex('market_offers', ['partner_id', 'status'], 'idx_market_offers_partner_status');
            $this->safeAddIndex('market_offers', 'created_at', 'idx_market_offers_created_at');
        }

        // 3. Index optimization for market_listings table
        if (Schema::hasTable('market_listings')) {
            $this->safeAddIndex('market_listings', ['status', 'commodity'], 'idx_market_listings_status_commodity');
            $this->safeAddIndex('market_listings', 'quantity', 'idx_market_listings_quantity');
        }

        // 4. Index optimization for farms table
        if (Schema::hasTable('farms')) {
            $this->safeAddIndex('farms', 'user_id', 'idx_farms_user_id');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_contracts')) {
            $this->safeDropIndex('purchase_contracts', 'idx_purchase_contracts_farmer_status');
            $this->safeDropIndex('purchase_contracts', 'idx_purchase_contracts_partner_status');
            $this->safeDropIndex('purchase_contracts', 'idx_purchase_contracts_contracted_at');
            $this->safeDropIndex('purchase_contracts', 'idx_purchase_contracts_listing_id');
        }

        if (Schema::hasTable('market_offers')) {
            $this->safeDropIndex('market_offers', 'idx_market_offers_listing_status');
            $this->safeDropIndex('market_offers', 'idx_market_offers_partner_status');
            $this->safeDropIndex('market_offers', 'idx_market_offers_created_at');
        }

        if (Schema::hasTable('market_listings')) {
            $this->safeDropIndex('market_listings', 'idx_market_listings_status_commodity');
            $this->safeDropIndex('market_listings', 'idx_market_listings_quantity');
        }

        if (Schema::hasTable('farms')) {
            $this->safeDropIndex('farms', 'idx_farms_user_id');
        }
    }
};
